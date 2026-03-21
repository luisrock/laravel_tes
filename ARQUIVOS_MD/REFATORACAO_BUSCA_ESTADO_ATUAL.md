# REFATORACAO BUSCA — ESTADO ATUAL

Referencia unica de handoff para o motor de busca. Atualizado em 2026-03-06 apos conclusao das Fases 1, 2 e 3.

## Resumo executivo

A refatoracao reorganizou a arquitetura da busca sem alterar nenhum contrato HTTP, shape de resposta ou comportamento visivel ao usuario. O arquivo `bootstrap/tes_functions.php` continua existindo como fachada de compatibilidade. Os services responsaveis pelo parser, execucao e cache foram extraidos para classes testadas. Classes desnecessariamente criadas foram revertidas e a logica foi reinserida nos controllers onde pertencia.

## Arquitetura atual

### Services ativos em app/Services/

| Classe | Responsabilidade |
|---|---|
| `SearchQueryParser` | Parser: normaliza conectores, injeta AND implicito, monta string boolean mode para MySQL e para APIs externas |
| `SearchDatabaseService` | Executa busca local: cache via SearchCacheManager, MATCH AGAINST, shape de resultado |
| `SearchCacheManager` | Encapsula chave de cache, TTL (3600s), cache tags e invalidacao granular por tribunal |
| `SearchTribunalRegistry` | Centraliza leitura de `config('tes_constants.lista_tribunais')` |
| `SearchTribunalConfig` | DTO com configuracao de um tribunal (tabelas, colunas FULLTEXT, teseName, usesDatabase) |
| `SearchTribunalResult` | DTO de resultado por tribunal (secoes sumula/tese, totalCount, conversao para array) |
| `SearchResultSection` | DTO de uma secao de resultado (total, hits) |

### Pontos de entrada

| Arquivo | Funcao |
|---|---|
| `app/Http/Controllers/SearchPageController.php` | Busca web unificada (exclui TCU, dispara job SEO) |
| `app/Http/Controllers/ApiController.php` | API publica por tribunal, busca unificada, temas aleatorios, CRUD de sumulas/teses |
| `app/Http/Controllers/TemaPageController.php` | Pagina publica de tema por slug |
| `app/Jobs/SearchToDbPesquisas.php` | Persiste termo na tabela pesquisas (filtros inline) |
| `app/Console/Commands/SyncTemasResults.php` | Recalcula campo results na tabela pesquisas |

### bootstrap/tes_functions.php

Permanece como fachada de compatibilidade. Expoe:
- `adjustOneQuoteOnly`, `adjustOperators`, `keyword_to_array`, `insertOperator`, `buildFinalSearchString`, `buildFinalSearchStringForApi` — delegam para `SearchQueryParser`
- `tes_search_db`, `tes_search_db_execute` — delegam para `SearchDatabaseService`
- `call_adjust_query_function`, `call_request_api` e demais helpers legados — ainda sem classe dedicada

## Comportamento do parser

Implementado em `SearchQueryParser`. Congelado por testes de caracterizacao.

**Conectores aceitos:**
- `OU`, `ou` -> OR
- `E`, `e`, `MESMO`, `Mesmo`, `mesmo` -> AND
- `NAO`, `nao`, `Nao`, `NÃO`, `não`, `Não` -> NOT

**AND implicito:** dois termos consecutivos sem conector geram AND entre eles.

**Aspas malformadas:** uma aspa solta e removida antes da tokenizacao (adjustOneQuoteOnly).

**Sinalizacao boolean mode:**
- termos obrigatorios (AND) recebem +
- termos apos NOT recebem -
- termos apos OR ficam sem sinal
- termos curtos (< 3 chars) podem ficar sem sinal
- frase sozinha nao recebe +; frase com outros termos recebe +

**Exemplos confirmados por teste MySQL real:**
- `dano moral` -> `+dano +moral`
- `dano OU moral` -> `dano moral`
- `dano não moral` -> `+dano -moral`
- `"dano moral" responsabilidade` -> `+"dano moral" +responsabilidade`
- `"dano moral"` (sozinho) -> `"dano moral"`

## Cache de busca

Gerenciado por `SearchCacheManager`. Chave: `search_{tribunal}_{md5(keyword)}`.

- TTL: 3600 segundos
- Com Redis/Memcached: usa cache tags, permite `cache:clear-searches --tribunal=STF`
- Com file/array: fallback para Cache::remember sem tags
- Invalidacao manual: `php artisan cache:clear-searches [--tribunal=X]`

## Resultado da busca

Shape retornado por `SearchDatabaseService::search()` (array compativel com views e API):

```
[
  'sumula' => ['total' => int, 'hits' => array],
  '{tese_name}' => ['total' => int, 'hits' => array],
  'total_count' => int,
]
```

`SearchTribunalResult` e `SearchResultSection` sao usados internamente nos services para tipagem. Conversao para array (->toArray()) preserva o shape acima. Os controllers e views continuam recebendo arrays.

## Persistencia SEO (SearchToDbPesquisas)

Job disparado por SearchPageController quando a busca tem resultados.

Filtros inline no job (sem classe separada):
- descarta termos numericos puros
- descarta termos contendo sumula ou sumula com acento (mb_strtolower, sem trailing-space)
- descarta termos com menos de 3 caracteres

Quando passa os filtros: percorre tribunais com usesDatabase()=true, soma total_count, insere em pesquisas via insertOrIgnore.

## Contratos a preservar

**Busca web:** aceita q e keyword; tribunal e opcional (pre-selecao de aba); TCU excluido da busca unificada.

**API por tribunal:** exige tribunal; aceita q e keyword; resposta: {total_sum, total_rep, hits_sum, hits_rep}.

**API unificada:** exige keyword; resposta: bloco por tribunal com {sumulas, teses, total} + bloco meta com {keyword, total_global}.

**Parser:** comportamento congelado por testes. Nao alterar sem atualizar SearchParserCharacterizationTest.

## Testes automatizados

**Suite principal (SQLite, phpunit.xml):** 302 testes
- `SearchParserCharacterizationTest` — comportamento do parser
- `SearchTest` — contrato HTTP da busca web
- `ApiTest` — contratos da API publica
- `SearchDatabaseServiceTest` — SearchDatabaseService + cache
- `SearchCacheManagerTest` — invalidacao de cache
- `ClearSearchCacheTest` — comando artisan
- `SearchResultObjectsTest` — DTOs SearchTribunalResult e SearchResultSection
- `SearchToDbPesquisasTest` — job de persistencia SEO
- `SyncTemasResultsTest` — comando de recalculo
- `SearchTribunalConfigTest`, `SearchTribunalRegistryTest` — configuracao

**Suite MySQL (phpunit.mysql.xml, banco forge_tes_test):** 16 testes
- `SearchMysqlIntegrationTest` — FULLTEXT BOOLEAN MODE real, parser->MySQL, executeResult end-to-end

**Rodar MySQL:** `php artisan test --compact -c phpunit.mysql.xml`

## O que ainda esta em bootstrap/tes_functions.php sem classe dedicada

- `call_request_api` — chamada a APIs externas para tribunais com db=false
- `call_adjust_query_function` — ajustes de shape por tribunal apos busca
- Helpers de formatacao de resultado especificos por tribunal

Esses helpers continuam funcionando. A migracao deles para classes nao foi feita porque o beneficio nao justificou o custo. Qualquer refatoracao futura deve comecar por aqui.

## Proximos passos possiveis

- Melhorias de UX de busca (sugestoes, zero-results, autocomplete)
- Extracao dos helpers de API externa e de ajuste de shape para classes testadas
- Avaliacao de motor alternativo (Meilisearch, Typesense) — so faz sentido apos definir objetivo de produto concreto
