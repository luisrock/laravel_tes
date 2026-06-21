# Extensão Chrome T&S — Plano de Implementação no SITE (specs passo a passo)

> **Escopo:** mudanças no repositório **Laravel** (`teses`) que dão suporte/alavancam a extensão.
> Deriva da seção 13 ("Low hanging fruit") de `ARQUIVOS_MD/EXTENSAO_CHROME_ESTRATEGIA.md`.
> O trabalho na **extensão** (`/Users/maurolopes/chromeExtensions/tes_chrome`) é guiado pelo doc
> `AGENTS.md` daquele repositório — **mantido em sincronia por este plano** (ver coluna "Sync ext.").
>
> **Criado:** 2026-06-19.
> **Status geral:** S1–S9 concluídos (S1–S8 em 2026-06-19; S9 em 2026-06-20). Pendência futura: print/GIF real do popup na landing; eventual ampliação do teor público para FONAJE (adiado).
>
> **Lado extensão (2026-06-21):** consumindo todo o contrato S6/S7/S9, a extensão chegou à **v2.0.0**
> (etapas E1–E5 em `tes_chrome/PLANO_EXTENSAO.md`): side panel com teor, detecção de referência
> (bolinha + menu), UTM e changelog automático. **Ainda não publicada** (Web Store tem a v1.0.0) —
> próximo marco é (re)publicar a v2.0.0. Nada pendente do servidor para esse lançamento.

---

## Como usar este plano

- Cada passo tem ID `S#`, um **objetivo**, **arquivos**, **spec**, **testes** e **gate de validação**.
- **Gate de validação (frontend):** quando o passo muda algo visível no site, **pare e peça aprovação
  do Mauro** (screenshot/preview) antes de seguir. Marcado com **🔶 VALIDAÇÃO**.
- **Sync ext.:** se o passo muda o contrato com a extensão, **atualizar o `AGENTS.md` da extensão**
  na mesma leva (a coluna indica o que sincronizar).
- Regras do projeto valem sempre: `php artisan make:*`, Form Requests, Eloquent (sem `DB::` exceto
  tabelas legadas dos tribunais), `route()`, **teste obrigatório por mudança**, e
  `vendor/bin/pint --dirty --format agent` antes de finalizar. Rodar testes com
  `php artisan test --compact --filter=<algo>`.

---

## Visão geral / ordem

| Passo | LH | Título | Esforço | Frontend? | Sync ext.? |
|---|---|---|---|---|---|
| **S1** | LH-1 | UTM nos links existentes p/ Web Store | S | leve | não |
| **S2** | LH-2 | CTA "Extensão" no header | S | **sim 🔶** | não |
| **S3** | LH-4 | Throttle em `/api/unified-search` | S | não | **sim** (rate limit) |
| **S4** | LH-3 | `unified-search` aceitar `q` além de `keyword` | S | não | **sim** (contrato) |
| **S5** | LH-7 | Instrumentação de uso (`X-Extension-Version` + contador) | M | não | **sim** (header novo) |
| **S6** | LH-5 | Endpoint público de leitura de teor (súmula/tese) | M | não | **sim** (endpoint novo) |
| **S7** | LH-6 | Ampliar tribunais no teor (além de STF/STJ) | M | não | **sim** (cobertura) |
| **S8** | LH-13 | Página `/extensao` (landing enxuta) | S/M | **sim 🔶** | **sim** (UTM destino) |
| **S9** | — | Ampliar teor público: `tema`/`tese`/`situacao` nas teses, `situacao` nas súmulas e Súmula Vinculante | M | não | **sim** (contrato) |

> **Semana A:** S1 → S2 → S3 → S4. **Semana B:** S5 → S6 → S7 (+ S8 em paralelo).
> S6 depende da **decisão LH-0** (teor público sem login). Confirmar com o Mauro antes de S6.

---

## Fatos do código já verificados (base das specs)

- `routes/api.php`: `POST /api/unified-search` → `ApiController@unifiedSearch` (público, **sem throttle**).
- `ApiController@unifiedSearch` valida **somente** `keyword` (`min:3`); `ApiController@index` aceita `q` **ou** `keyword`.
- `ApiController@getSumula`/`getTese` exigem `bearer.token` e **só aceitam STF e STJ** (hardcoded `in_array($t, ['STF','STJ'])`).
- `BearerTokenMiddleware` compara contra **um único** `config('services.api.token')` (`.env` `API_TOKEN`) — não há token por usuário.
- `SearchPageController@index` aceita `q`/`keyword` + `tribunal` (pré-seleção de aba) → a extensão **já** gera deep links `?q=...&tribunal=...`.
- Tribunais: fonte única em `config/tes_constants.php` (`lista_tribunais`), exposto por `SearchTribunalRegistry`. **FONAJE tem múltiplas tabelas** (`fonaje_civ_sumulas`, `fonaje_cri_sumulas`, `fonaje_faz_sumulas`).
- Links p/ Web Store hoje: `resources/views/partials/footer.blade.php` e `resources/views/front/atualizacoes.blade.php`. **Header não tem.**
- Rota `/atualizacoes` é changelog de conteúdo e está **desativada para visitantes** (uso interno) — não reaproveitar como landing.
- Testes de API: `tests/Feature/ApiTest.php` (SQLite pode dar 500 em FULLTEXT — padrão `toBeIn([200, 404, 500])`).

---

## S1 — LH-1: UTM nos links existentes para a Web Store

**Objetivo:** rastrear, no Matomo, quem vai do site para a Chrome Web Store.

**Arquivos:**
- `resources/views/partials/footer.blade.php`
- `resources/views/front/atualizacoes.blade.php`

**Spec:**
- Centralizar a URL da extensão para evitar repetição (DRY). Criar `config/teses.php` → chave
  `extension` com:
  ```php
  'extension' => [
      'webstore_url' => 'https://chrome.google.com/webstore/detail/teses-e-s%C3%BAmulas/biigfejcdpcpibfmffgmmndpjhnlcjfb',
  ],
  ```
- Criar um helper Blade simples para anexar UTM. Opção mínima sem helper novo: montar a URL no Blade
  com query string:
  `{{ config('teses.extension.webstore_url') }}?utm_source=site&utm_medium=footer&utm_campaign=extensao`
  e `...&utm_medium=atualizacoes` na outra view.
- Não alterar texto/estilo visível dos links (só o `href`).

**Testes (`php artisan make:test --pest ExtensionLinksTest`):**
- Rota `searchpage` (ou qualquer página com footer) responde 200 e o HTML contém
  `utm_source=site` no link da Web Store.
- Usar `assertRouteResponds()` / `$this->get('/')->assertSee('utm_source=site', false)`.

**Gate:** sem validação visual (não muda layout).

**Sync ext.:** não.

---

## S2 — LH-2: CTA "Extensão" no header  🔶 VALIDAÇÃO

**Objetivo:** reintroduzir descoberta da extensão no topo (desktop + mobile).

**Arquivos:** `resources/views/partials/header.blade.php`

**Spec:**
- Adicionar um link discreto no `<nav>` desktop (perto de "Newsletters") e no menu mobile
  (`#site-nav-menu`), seguindo as classes Tailwind já usadas pelos vizinhos (consistência).
- Texto sugerido: **"Extensão"** (ou com ícone `fa-chrome`). O `href` deve apontar para **`/extensao`**
  (página do S8) **quando S8 existir**; até lá, apontar para a Web Store com UTM
  (`utm_source=site&utm_medium=header`).
- Não quebrar o toggle mobile existente (JS no fim do arquivo).

**Testes:**
- `$this->get('/')->assertSee('Extensão')` e contém o `href` esperado (UTM `utm_medium=header` ou rota `/extensao`).

**Gate:** **🔶 VALIDAÇÃO** — mudança visível no header. Gerar preview/responder com como ficou
(desktop e mobile) e **aguardar OK do Mauro** antes de finalizar.
> Lembrete: mudança em Blade exige `npm run build` (ou `npm run dev`) para refletir no ambiente do Mauro.

**Sync ext.:** não (mas se o `href` apontar p/ `/extensao`, garantir que S8 esteja pronto ou usar Web Store provisoriamente).

---

## S3 — LH-4: Throttle em `/api/unified-search`

**Objetivo:** proteger a API pública de abuso/custo antes do freemium.

**Arquivos:** `routes/api.php`

**Spec:**
- Envolver a rota em `throttle`. Valor inicial conservador e generoso para uso humano:
  `Route::middleware('throttle:120,1')->post('/unified-search', ...)` (120 req/min por IP).
- Garantir resposta `429` com JSON (Laravel já retorna 429; validar headers `Retry-After`).
- **Não** aplicar a outras rotas neste passo.

**Testes (no `ApiTest.php`):**
- Em ambiente de teste o limiter pode ser desabilitado; cobrir com teste que faz N+1 chamadas e
  espera `429` na última **ou** que aceite `[200, 429, 500]` se o ambiente não isolar o limiter.
  Preferir um teste focado: setar limite baixo via `RateLimiter::for` mock não é trivial — então
  validar que a rota **continua respondendo** (`toBeIn([200, 422, 500])`) e adicionar um teste
  específico de 429 só se o ambiente permitir. Documentar a decisão no teste.

**Gate:** sem validação visual.

**Sync ext.:** **sim** — documentar no `AGENTS.md` da extensão que existe rate limit (120/min) e que a
extensão deve tratar `429` (mostrar mensagem "muitas buscas, tente em instantes").

---

## S4 — LH-3: `unified-search` aceitar `q` além de `keyword`

**Objetivo:** consistência com `ApiController@index` e robustez a clientes que enviem `q`.

**Arquivos:** `app/Http/Controllers/ApiController.php` (método `unifiedSearch`).

**Spec:**
- Replicar o padrão de `index()`: validar `q` **ou** `keyword`:
  ```php
  $request->validate([
      'q' => 'required_without:keyword|min:3',
      'keyword' => 'required_without:q|min:3',
  ], [ /* mensagens equivalentes às já usadas */ ]);
  $keyword = $request->input('q') ?? $request->input('keyword');
  ```
- Manter a resposta `meta.keyword` com o termo efetivo.
- **Avaliar Form Request** (`UnifiedSearchRequest`) para alinhar à convenção do projeto (controllers
  magros). Se criar, mover as mensagens para lá.

**Testes (no `ApiTest.php`):**
- Mantém: `keyword` ausente + `q` ausente → 422.
- Novo: enviar só `q` (≥3 chars) → estrutura válida (`toBeIn([200, 500])`, e se 200, `meta.keyword === q`).
- Novo: `q` com 2 chars → 422.

**Gate:** sem validação visual.

**Sync ext.:** **sim** — registrar no `AGENTS.md` que `unified-search` aceita `q` ou `keyword`
(a extensão envia `keyword`; nada a mudar, mas documentar para evitar confusão).

---

## S5 — LH-7: Instrumentação de uso da extensão

**Objetivo:** medir uso de forma durável, sem depender de logs nginx; sem PII (LGPD).

**Arquivos:**
- Migration + model: `php artisan make:model ExtensionUsageDaily -m`
- `app/Http/Controllers/ApiController.php` (`unifiedSearch`) ou um middleware dedicado.

**Spec:**
- Tabela `extension_usage_daily`: colunas `date` (date), `extension_version` (string, nullable),
  `hits` (unsigned int, default 0). Índice único `(date, extension_version)`.
- Em `unifiedSearch` (ou middleware `LogExtensionUsage` aplicado só à rota): ler header
  `X-Extension-Version` (sanitizar: regex `^[0-9.]{1,12}$`, senão `null`) e fazer **upsert
  incremental** do contador do dia (`updateOrCreate` + `increment`, ou `upsert` com `hits = hits + 1`).
- **Não logar keyword crua.** Nada de IP/PII na tabela.
- Opcional: expor um widget/age­gado simples no Filament `SiteStats` depois (fora deste passo).

**Testes:**
- `POST /api/unified-search` com header `X-Extension-Version: 1.0.0` cria/incrementa linha do dia.
- Duas chamadas no mesmo dia/versão ⇒ `hits = 2`.
- Header ausente ⇒ linha com `extension_version = null` (ou agregada conforme decisão).
- Header malicioso (`<script>`) ⇒ tratado como `null` (sanitização).

**Gate:** sem validação visual.

**Sync ext.:** **sim** — o `AGENTS.md` deve instruir a extensão a enviar `X-Extension-Version`
(lido de `chrome.runtime.getManifest().version`) em **todas** as chamadas à API.

---

## S6 — LH-5: Endpoint público de leitura de teor  (depende de decisão LH-0)

**Objetivo:** permitir que a extensão mostre o **teor** de súmula/tese **sem** embutir token e **sem** login.

**Pré-requisito:** **confirmação do Mauro (LH-0)** de que o teor básico pode ser público.

**Arquivos:**
- `routes/api.php` (grupo novo `/api/public/...` com throttle)
- `app/Http/Controllers/ApiController.php` (métodos `getPublicSumula`, `getPublicTese`) **ou**
  um controller dedicado `PublicContentApiController` (preferível para isolar do fluxo autenticado).

**Spec:**
- Rotas: `GET /api/public/sumula/{tribunal}/{numero}` e `GET /api/public/tese/{tribunal}/{numero}`,
  com `throttle` (ex.: `throttle:120,1`), **sem** `bearer.token`.
- Resposta enxuta e estável (contrato versionável):
  ```json
  { "success": true, "data": { "tribunal": "STJ", "tipo": "sumula", "numero": 7,
    "texto": "…", "url": "https://tesesesumulas.com.br/sumula/stj/7" } }
  ```
- `url` canônica: usar `route()`/`url()` quando houver página individual (stf/stj/tst/tnu);
  senão, link para a busca (`/?q=...`).
- Reaproveitar a lógica de leitura já existente em `getSumula`/`getTese` (extrair método privado
  comum para DRY). Manter `getSumula`/`getTese` autenticados para escrita/admin.
- **Sem PII**, sem campos internos desnecessários (selecionar colunas explicitamente, não `select('*')`).

**Testes (novo `tests/Feature/PublicContentApiTest.php`):**
- `GET /api/public/sumula/STF/1` responde sem token (`toBeIn([200, 404, 500])`).
- Tribunal inválido → 400/404 com `success=false`.
- `numero` não numérico → 400.
- Resposta 200 contém chaves `tribunal`, `tipo`, `numero`, `texto`, `url`.

**Gate:** sem validação visual (é API). Mas **🔶 confirmar LH-0 com o Mauro antes de começar.**

**Sync ext.:** **sim** — documentar o novo contrato no `AGENTS.md` (rota, shape, throttle 429,
ausência de token) para habilitar F2 (side panel/teor inline).

---

## S7 — LH-6: Ampliar tribunais no teor

**Objetivo:** o teor não ficar restrito a STF/STJ (a busca já cobre 7 órgãos).

**Arquivos:** `app/Http/Controllers/ApiController.php` (e/ou `PublicContentApiController` do S6),
usando `SearchTribunalRegistry`.

**Spec:**
- Substituir o hardcode `in_array($t, ['STF','STJ'])` por validação via **registry**
  (`SearchTribunalRegistry::keys()`), aceitando os tribunais que possuem tabela correspondente.
- Mapear tabela a partir do registry (`tables.sumulas` / `tables.teses`), **não** por concatenação
  fixa `strtolower($t).'_sumulas'`.
- **Atenção FONAJE:** possui múltiplas tabelas (`fonaje_civ_sumulas`, `fonaje_cri_sumulas`,
  `fonaje_faz_sumulas`). Definir regra (ex.: parâmetro de sub-base, ou buscar nas três por `numero`).
  **Validar a abordagem com o Mauro** antes de implementar FONAJE.
- Teses só existem para stf/stj/tst/tnu — retornar 404 claro para órgãos sem teses.

**Testes:**
- Para cada tribunal com tabela: rota responde `toBeIn([200, 404, 500])`.
- Tribunal sem teses (ex.: CARF em `/tese/...`) → 404 com mensagem clara.
- Tribunal inexistente → 400/404.

**Gate:** sem validação visual; **🔶 validar abordagem FONAJE** (decisão de produto/dados).

**Sync ext.:** **sim** — atualizar `AGENTS.md` listando exatamente quais tribunais têm teor de
súmula e de tese disponíveis no endpoint público.

---

## S8 — LH-13: Página `/extensao` (landing enxuta)  🔶 VALIDAÇÃO

**Objetivo:** página simples que educa antes de mandar para a Web Store (melhora conversão e reduz
instalação dormente).

**Arquivos:**
- Rota em `routes/web.php` (`Route::get('/extensao', ...)->name('extensao')`).
- View `resources/views/front/extensao.blade.php` (estende o layout/base já usado pelo front).
- (Opcional) controller fino ou closure que só retorna a view (sem DB).

**Spec:**
- Conteúdo: título, subtítulo, 3–4 bullets (busca rápida; 7 tribunais; gratuito; link direto ao site),
  1 imagem/GIF do popup (placeholder se ainda não houver asset), e CTA final
  **"Instalar no Chrome"** → Web Store com `utm_source=site&utm_medium=extensao_page`.
- Reusar componentes/estilos Tailwind do front (cor de marca `#912F56`). Não criar design novo do zero.
- Atualizar o `href` do CTA do header (S2) para apontar a esta página.
- SEO básico: `@section('page-title', ...)` e meta description.

**Testes:**
- `$this->get('/extensao')->assertOk()->assertSee('Instalar no Chrome')`.
- Link da Web Store contém `utm_medium=extensao_page`.
- Rota nomeada `extensao` resolve (`route('extensao')`).

**Gate:** **🔶 VALIDAÇÃO** — página nova e visível. Apresentar preview e **aguardar OK do Mauro**
(texto, imagem, layout). Requer `npm run build`/`dev`.

**Sync ext.:** **sim** — registrar no `AGENTS.md` que a landing oficial é `/extensao` (destino de
campanhas/links) e o padrão de UTM.

---

## Checklist mestre (marque ao concluir)

- [x] S1 — UTM nos links existentes (LH-1) — **concluído 2026-06-19**. Config `teses.extension.webstore_url` + helper `extension_webstore_url()` (`bootstrap/tes_functions.php`); footer e `/atualizacoes` atualizados; `tests/Feature/ExtensionLinksTest.php` (3 testes verdes).
- [x] S2 — CTA "Extensão" no header (LH-2) 🔶 — **concluído 2026-06-19** (validado pelo Mauro). Link "Extensão" (ícone `fa-puzzle-piece`) no nav desktop e menu mobile de `partials/header.blade.php`, apontando à Web Store com `utm_medium=header` (migrará para `/extensao` no S8). Teste no `ExtensionLinksTest.php`.
- [x] S3 — Throttle em `/api/unified-search` (LH-4) → sync ext. — **concluído 2026-06-19**. **Correção da premissa:** a API **já** tinha throttle de **60 req/min por IP** (limiter `api` no `RouteServiceProvider`, aplicado pelo grupo `api`). Não foi empilhado throttle redundante; apenas documentado e adicionado teste de `429` (`ApiTest.php`). `AGENTS.md` da extensão atualizado (baseline + log S3).
- [x] S4 — `unified-search` aceita `q`/`keyword` (LH-3) → sync ext. — **concluído 2026-06-19**. `ApiController@unifiedSearch` valida `q` **ou** `keyword` (espelha `index()`); `meta.keyword` reflete o termo efetivo. 2 testes novos no `ApiTest.php`. `AGENTS.md` atualizado.
- [x] S5 — Instrumentação `X-Extension-Version` (LH-7) → sync ext. — **concluído 2026-06-19**. Tabela `extension_usage_dailies` (date, extension_version, hits; unique date+version) + model `ExtensionUsageDaily` com `record()`/`sanitizeVersion()` (sem PII/keyword). `unifiedSearch` chama `record($request->header('X-Extension-Version'))`. 7 testes em `ExtensionUsageTest.php`. Migration aplicada em dev. `AGENTS.md` atualizado (extensão deve enviar o header).
- [x] Decisão LH-0 com o Mauro (teor público vs. login) — **DECIDIDO 2026-06-19: endpoint público de leitura, sem login, com rate limit.** Recomendação adicional do Mauro alinhada ao híbrido (teor básico público; extras como IA/salvar ficam para depois, atrás de login). **S6 liberado.**
- [x] S6 — Endpoint público de teor (LH-5) → sync ext. — **concluído 2026-06-19**. Novos `GET /api/public/sumula/{trib}/{n}` e `GET /api/public/tese/{trib}/{n}` (sem `bearer.token`), resposta enxuta `{ success, data: { tribunal, tipo, numero, texto, url } }` com `url` canônica via `route()`. Lógica de leitura centralizada no service `App\Services\TribunalContentReader` (resolve tabela via `SearchTribunalRegistry`, coluna de teor explícita, sem `select('*')` nem PII). Controller fino `PublicContentApiController`. **Escopo S6 = STF/STJ** (mesmo do endpoint autenticado; ampliação no S7). Rate limit herdado do grupo `api` (60/min por IP), consistente com S3 (sem throttle redundante empilhado). 7 testes em `PublicContentApiTest.php`; happy path validado em MySQL dev via tinker. `AGENTS.md` da extensão atualizado (novo contrato).
- [x] S7 — Ampliar tribunais no teor (LH-6) → sync ext. — **concluído 2026-06-19**. `TribunalContentReader` ampliado: **súmulas** em STF/STJ/TST/TNU/CARF/CEJ e **teses** em STF/STJ/TST/TNU (coluna de teor por tribunal: súmula→`texto`; tese→`tese_texto` em STF/STJ, `texto` no TST, `tese` no TNU). Tabela resolvida via `SearchTribunalRegistry` (`tables()`), sem concatenação fixa. **FONAJE adiado** por decisão do Mauro (3 sub-bases civ/cri/faz com numerações próprias ⇒ `/FONAJE/{n}` ambíguo) → retorna 404; **TCU fora** (API externa). Teses de CARF/CEJ/FONAJE → 404 claro. URL canônica via `route()` para STF/STJ/TST/TNU; fallback de busca para CARF/CEJ (sem página individual). Testes ampliados com datasets em `PublicContentApiTest.php` (20 testes); happy path de todos os tribunais validado em MySQL dev. `AGENTS.md` atualizado.
- [x] S8 — Página `/extensao` (LH-13) 🔶 → sync ext. — **concluído 2026-06-19** (validado pelo Mauro). Rota `GET /extensao` (closure, sem DB, `name('extensao')`) + view `front/extensao.blade.php` no layout `front.base` (hero, mock visual do popup como placeholder até print/GIF real, 4 bullets, CTA "Instalar no Chrome" com `utm_medium=extensao_page`). CTA "Extensão" do header (S2, desktop+mobile) repontado de Web Store para `/extensao`. Ajustes do Mauro aplicados: hero "dos tribunais mais importantes" e card "Tudo num só lugar". `npm run build` gerado. Testes: ajustado o do header + 3 novos da landing em `ExtensionLinksTest.php`. `AGENTS.md` atualizado (landing oficial). **Pendente futuro:** trocar o mock por print/GIF real do popup.
- [x] S9 — Ampliar API pública de teor para a extensão → sync ext. — **concluído 2026-06-20**. Três inclusões em `App\Services\TribunalContentReader` + `PublicContentApiController`, sem quebrar o contrato S6/S7 (mantidos `success`, `tribunal`, `tipo`, `numero`, `url`, `texto`): **(1)** `GET /api/public/tese/{trib}/{n}` ganha `tema` (questão; STF de `tema_texto` com prefixo "N - " removido; STJ/TST/TNU de `tema`), `tese` (== `texto`; `tese_texto` em STF/STJ, `texto` no TST, `tese` no TNU) e `situacao` (coluna `situacao`; TST não tem → `""`). Tese não fixada ⇒ `texto`/`tese` `""` com `tema`/`situacao` preenchidos (validado no Tema 1443/STF). **(2)** `GET /api/public/sumula/{trib}/{n}` ganha `situacao` (`"Cancelada"`/`""`: STJ/TNU via `isCancelada`, STF via `obs`); STF passa a filtrar `is_vinculante=0` (corrige ambiguidade da numeração compartilhada com a SV). **(3)** Novo `GET /api/public/sumula-vinculante/{trib}/{n}` (só STF; `is_vinculante=1`), `tipo: "sumula-vinculante"`, `url` canônica `/sumula/stf/sv{n}`. Rate limit/erros (400/404/429) e leitura de `X-Extension-Version` inalterados. Testes: `PublicContentApiTest.php` ampliado (31 testes) + 4 JSON reais validados em MySQL dev via HTTP. `ApiTest.php` sem regressão. `AGENTS.md` da extensão atualizado (estado atual + log S9).

## Procedimento ao concluir cada passo

1. Rodar `vendor/bin/pint --dirty --format agent`.
2. Rodar testes do escopo: `php artisan test --compact --filter=<algo>`.
3. Se houver mudança de frontend (🔶): **parar e pedir validação do Mauro** (lembrar do `npm run build`).
4. Se a coluna **Sync ext.** indicar: atualizar `/Users/maurolopes/chromeExtensions/tes_chrome/AGENTS.md`
   na seção "Contrato com o site (sincronizado)" e datar a entrada.
5. Marcar o passo no checklist.
