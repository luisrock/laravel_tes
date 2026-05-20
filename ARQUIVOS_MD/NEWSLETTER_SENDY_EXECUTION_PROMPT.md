# Prompt — Executor da integração Newsletter Sendy

Cole o conteúdo abaixo na nova conversa do Cursor. Não precisa de ajustes; tudo o que o agente executor precisa está nos dois arquivos referenciados (briefing do projeto + plano em fases).

---

# Instruções para a IA executora

Sou o agente responsável por executar a integração da newsletter T&S com o Sendy. **Antes de qualquer coisa, leia, nesta ordem**:

1. `PROJECT_BRIEF.md` (raiz do projeto) — briefing compacto do site, stack, modelos, rotas, convenções.
2. `ARQUIVOS_MD/NEWSLETTER_SENDY_PLAN.md` — plano detalhado em 8 fases, com critérios de aceitação, testes e gates de validação humana.
3. `AGENTS.md` / `CLAUDE.md` (já carregados automaticamente como `always_applied_workspace_rules`).

Depois confirme em uma única frase: "Li o briefing e o plano. Estou pronto para começar a Fase N." (substituindo N pela fase pendente mais antiga conforme o STATUS TRACKER do plano).

## Regras de ouro (não negociáveis)

1. **Execução compartimentalizada**: trabalho avança UMA fase por vez. Cada fase tem um gate de validação humana ao fim. **NÃO inicie a próxima fase sem o user dizer explicitamente "Pode avançar para a Fase N+1"**.
2. **Site sempre estável**: o user pode commitar/empurrar para prod a qualquer momento. Toda integração com Sendy passa por `SiteSetting::getAsBool('newsletter_integration_enabled')` (default `'0'`). Quando `false`, UI nova esconde-se e fallbacks antigos (ex.: link externo em `/newsletters`) seguem ativos.
3. **Falhas no Sendy não podem quebrar o site**: todo chamado externo em `try/catch` + log. Service sempre retorna DTO de sucesso/erro; jamais throw para o caller.
4. **Testes obrigatórios por fase**: a fase só está concluída quando os testes específicos passam (`php artisan test --compact --filter=...`) E a suite completa não regrediu (`php artisan test --compact`).
5. **Validação no browser obrigatória** quando a fase tem componente visual: reporte os passos exatos (URLs, credenciais de teste se necessárias, o que olhar) para o user testar em `https://teses.test`. **Aguarde a confirmação** antes de marcar a fase como `validated`.
6. **Atualize o plano no fim de cada fase**:
   - Marcar status (`validated` + data) na tabela STATUS TRACKER no topo do plano.
   - Marcar a checkbox de gate na seção da fase.
   - Preencher a subseção "Notas de execução" com decisões tomadas, comandos rodados, número de testes verdes, e qualquer surpresa que pode ser útil em um restart de contexto.
7. **Pint antes de cada handoff/commit**: `vendor/bin/pint --dirty --format agent`.
8. **Use Laravel Boost MCP**: `search-docs` antes de mexer em Laravel/Filament/Livewire/Pest; `database-schema` antes de queries; `tinker` para debug; `list-artisan-commands` para parâmetros de comandos.
9. **PHP 8.3** — confirmar com `php -v` na primeira fase.
10. **Comunicação em português** com o user.

## Convenções específicas do projeto (não esqueça)

- Estrutura Laravel 10 legada (middleware em `app/Http/Kernel.php`, schedule em `app/Console/Kernel.php`). NÃO mexer em `bootstrap/app.php`.
- Tailwind v3 com prefixo `tw-`. Cor primária bordô `#912F56`.
- Form Requests para validação. Eloquent + return types tipados.
- `php artisan make:*` com `--no-interaction` para scaffolding.
- Honeypot Spatie (`@honeypot`) em forms públicos.
- Padrão de Filament settings page já existente em `app/Filament/Pages/MeteredWallSettings.php` — espelhar.
- Testes Pest. Helpers em `tests/Pest.php`. SQLite in-memory; para testar connection `sendy` use o helper `fakeSendyConnection()` que será criado na Fase 1.

## Workflow esperado por fase

```
1. Ler a seção da fase no plano.
2. Confirmar com o user que vamos começar essa fase ("Vou iniciar a Fase N. Faz sentido?").
3. Implementar conforme spec (sem desviar — se algo não estiver claro, perguntar).
4. Rodar testes específicos + suite completa.
5. Rodar Pint.
6. Listar para o user os passos exatos de validação no browser (se aplicável).
7. Aguardar resposta do user.
8. Após validação, atualizar plano (STATUS TRACKER + checkbox + Notas de execução).
9. Perguntar: "Posso avançar para a Fase N+1?"
```

## Em caso de bloqueio

- Se um teste falhar e você não souber resolver em 2 tentativas: pare, reporte ao user com diagnóstico e opções.
- Se a API/DB do Sendy não responder: pare, reporte, NÃO improvise mocks que mascarem o problema.
- Se um requisito do plano colidir com a realidade do código existente: pare, reporte, sugira ajuste, espere decisão.

## Smoke test inicial (Fase 0/1)

Antes de escrever qualquer linha de código de domínio, confirme via tinker (ou Boost `tinker` MCP):
- `config('services.sendy.api_base_url')` retorna URL não vazia.
- `config('services.sendy.list_id')` retorna string não vazia (hash).
- `config('services.sendy.list_internal_id')` retorna `2` (ou o que estiver no .env).
- `DB::connection('sendy')->getPdo()` conecta sem erro.
- `DB::connection('sendy')->table('subscribers')->limit(1)->get()` retorna pelo menos 1 linha.

Reportar o resultado desses 5 checks ao user antes de avançar.

---

Comece agora pela leitura dos arquivos e pela confirmação. Não escreva código até ter o "ok" para a Fase 0.
