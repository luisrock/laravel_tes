# AI SDK — Fase 2: Prompts gerenciáveis, Streaming e Conversa persistente

> Continuação de `AI_SDK_INTEGRATION_PLAN.md` (Fase 1 — **concluída e em produção**, commits `8966fe8`
> + `e7df5bd`). **Mesmos princípios, obrigatórios:** baby steps; **teste automatizado verde antes de
> avançar**; validação de frontend sob demanda (pausar e aguardar OK); **nada quebra em produção**
> (feature admin-only); **isolamento** das features de IA legadas ("Decifrando a Tese": `ai_models`,
> `TeseAnalysis*` — não tocar); testes para tudo; cautela máxima (aditivo, não destrutivo); handoff de
> contexto quando crescer. Atualizar este checklist a cada iteração (marcar `[x]`, indicar próximo passo).

## Estado atual

- **Bloco A CONCLUÍDO e validado** (frontend OK em 2026-05-29) — **ainda NÃO commitado** (aguarda OK do usuário).
- **Próximo: Bloco B (streaming).** Confirmar a "decisão B" antes de codar.
- Fase 1 já em `master` (deploy automático Vito). Em prod falta apenas: `OPENROUTER_API_KEY` +
  `OPENROUTER_API_KEY_MANAGEMENT` no `.env` e escolher o modelo em `/admin/painel/configuracoes-ia`.
- **Pendências de prod do Bloco A** (quando commitar/pushar): a migration roda sozinha (`migrate --force`),
  mas o **seeder é manual**: `php artisan db:seed --class=AiPromptsSeeder --force` (sem ele, o chat usa o fallback).

## Base existente (Fase 1) — o que já há

| Arquivo | Papel |
|---|---|
| `config/ai.php` | Provider `openrouter` nativo (driver `openrouter`, lê `OPENROUTER_API_KEY`). |
| `config/services.php` → `openrouter` | `key`, `management_key`, `base_url`, `request_timeout` (120). |
| `app/Services/Ai/OpenRouterManagementService.php` | `remainingCredits()`, `availableModels()` (cache 6h), `clearModelsCache()`. |
| `app/Filament/Pages/AiSettings.php` | Página "Configurações de IA" (`/admin/painel/configuracoes-ia`): crédito + Select de modelo → `SiteSetting('ai_chat_model')`. |
| `app/Ai/Tools/QuerySiteMetrics.php` | Tool: métricas por período (1/3/7/30/60), reaproveita `SiteMetrics` + `SendyService`. |
| `app/Ai/Agents/StatsAnalyst.php` | Agent `Agent,Conversational,HasTools`; `provider()='openrouter'`, `model()` de `SiteSetting`, `timeout()=120`, `maxSteps()=6`, `instructions()` (system prompt em código), `messages()` (histórico efêmero), `isConfigured()`. |
| `app/Livewire/StatsAiChat.php` + view | Chat síncrono + botão "Avaliar estatísticas"; embutido como último item de `SiteStats`. |
| Testes | `tests/Feature/Ai/*` (30) + `tests/Feature/AiSdkConfigTest.php`. |

**Fakes do SDK para testes:** `StatsAnalyst::fake([...])`, `StatsAnalyst::assertPrompted(fn ($p) => ...->prompt)`,
`assertNotPrompted()`. `Message` (`Laravel\Ai\Messages\Message`) e `MessageRole` (`User`, `Assistant`, `ToolResult`).

## Motivação (pedidos do usuário)

1. **Editar o system prompt** do assistente na área de Configurações de IA; e, como virão outros prompts,
   montar um **CRUD de prompts**.
2. **Streaming** da resposta (token a token) — mais elegante; melhora a percepção de lentidão.
3. **Conversa multi-turno com contexto** estilo ChatGPT — *já funciona efêmero na Fase 1*; aqui é **persistir/retomar**.

---

## Decisões a confirmar com o usuário ANTES de codar cada bloco

- **Bloco A (prompts):** CRUD genérico (`AiPrompt` com `key` única + `title` + `content` + `description`)
  vs. só um campo editável do system prompt na `AiSettings`. Recomendação: `AiPrompt` genérico desde já
  (escalável), começando com a `key` `stats_analyst_system`.
- **Bloco B (streaming):** abordagem recomendada **`$this->stream()` do Livewire 3** (sem Reverb/Echo,
  sem novas deps) consumindo `StatsAnalyst::stream()` do SDK. Nota honesta: streaming melhora a UX e a
  percepção, mas a request PHP continua aberta até terminar — o `set_time_limit`/`timeout` da Fase 1
  seguem necessários; não é bala de prata para timeouts de modelos lentos.
- **Bloco C (conversa persistente):** `RemembersConversations` do SDK (re-publicar **apenas** a migration
  `create_agent_conversations_table` que removemos na Fase 1) vs. tabela própria. Definir se haverá UI de
  "conversas anteriores". Recomendação: `RemembersConversations` + `forUser()`/`continue()`.

---

## Checklist de passos

### Bloco A — CRUD de prompts (sem mexer no legado) — **decisão: AiPrompt genérico + AiPromptResource dedicado**
- [x] Migration + modelo `App\Models\AiPrompt` (`key` única, `title`, `content` text, `description` nullable,
      timestamps). **Tabela nova `ai_prompts`** — não confundir com `ai_models`. Helper `AiPrompt::contentForKey()`.
- [x] Factory + Seeder (`AiPromptsSeeder`) com a `key` `stats_analyst_system` = `StatsAnalyst::defaultInstructions()`.
      Seeder idempotente (`firstOrCreate`). **Rodar manual em prod**: `php artisan db:seed --class=AiPromptsSeeder --force`.
- [x] `StatsAnalyst::instructions()` lê `AiPrompt::contentForKey(self::SYSTEM_PROMPT_KEY)` com **fallback** ao
      `defaultInstructions()` (texto extraído para método estático reutilizado pelo seeder). Nunca quebra se faltar registro.
- [x] CRUD no Filament: `AiPromptResource` (grupo `Configurações`, padrão flat como `PlanFeatureResource`).
      Textarea multilinha; `key` `disabledOn('edit')` + `unique(ignoreRecord)`.
- [x] **Testes**: `AiPromptTest` (persistência, fallback ausente/vazio, seeder idempotente) +
      `AiPromptResourceTest` (acesso admin-only, create/edit, key desabilitada). 20 verdes; suíte completa verde.
- [x] **VALIDAÇÃO FRONTEND** — editar o prompt e ver o efeito no chat. **OK do usuário em 2026-05-29.**

### Bloco B — Streaming (Livewire 3 `$this->stream()`)
- [ ] Em `StatsAiChat`: novo fluxo de envio que chama `StatsAnalyst::make(history)->stream($prompt)`,
      itera os eventos e usa `$this->stream(to: 'ai-answer', content: $delta, replace: false)` para
      empurrar deltas a um alvo `wire:stream="ai-answer"` na view. Ao terminar, persistir a resposta final
      em `$messages` (como hoje) e limpar o buffer de streaming.
- [ ] Manter `set_time_limit`/`timeout`. Tratar exceção (mesma UX de erro da Fase 1).
- [ ] View: adicionar o contêiner `wire:stream="ai-answer"` no lugar do indicador atual; manter markdown no
      texto final (o streaming pode mostrar texto cru durante o fluxo e renderizar markdown ao concluir).
- [ ] **Testes**: com `StatsAnalyst::fake([...])` validar que ao concluir a resposta final entra em `$messages`
      e o estado de erro é tratado. (Streaming em si é difícil de asserir unitariamente — focar no resultado final.)
- [ ] **VALIDAÇÃO FRONTEND** — ver tokens aparecendo. Aguardar OK.

### Bloco C — Conversa persistente
- [ ] Re-publicar **só** a migration de conversas do SDK: `php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider" --tag=...`
      (ou copiar manualmente `create_agent_conversations_table`); rodar `migrate`. **Avisar deploy** (Vito roda `migrate --force`).
- [ ] `StatsAnalyst`: usar trait `RemembersConversations` (remover o `messages()` efêmero ou conciliar).
- [ ] `StatsAiChat`: `forUser(auth()->user())` ao iniciar; guardar `conversationId`; botão "Nova conversa";
      opcional: listar/retomar conversas anteriores do admin (`continue($id, as: $user)`).
- [ ] **Testes**: nova conversa cria registro; continuação carrega histórico; isolamento por usuário.
- [ ] **VALIDAÇÃO FRONTEND** — conversa persiste após recarregar. Aguardar OK.

### Fecho
- [ ] `vendor/bin/pint --dirty --format agent`; `php artisan test --compact` (suíte completa verde).
- [ ] Atualizar checklist; confirmar com usuário antes de commit/push em `master` (deploy automático).

## Riscos / notas
- **Migrations voltam** (Blocos A e C) → validar em dev; em prod o Vito roda `migrate --force` no push.
  Seeder de prompts é **manual** em prod (não está no script de deploy).
- **Streaming** não elimina timeouts de modelos lentos; combinar com escolha de modelos rápidos.
- Manter tudo **admin-only** e isolado até a futura unificação do gestor de IA (quando o ai-sdk absorverá
  `TeseAnalysis*`). Projetar `AiPrompt` com nomes neutros pensando nessa unificação.
- Convenção do projeto: `env()` só em `config/*`; Form Requests p/ validação; Pint antes de finalizar.

---

## PROMPT DE HANDOFF (colar num novo chat / outro modelo)

```
Contexto: projeto Laravel 12 "Teses e Súmulas" (PHP 8.3, Filament 4, Livewire 3, Pest 3, Laravel Boost
MCP). Site EM PRODUÇÃO (tesesesumulas.com.br). Antes de tudo, leia PROJECT_BRIEF.md e AGENTS.md/CLAUDE.md.
Responda em português.

Estou continuando a integração do Laravel AI SDK (laravel/ai). A FASE 1 já está pronta, testada e em
master: um chat de IA + botão "Avaliar estatísticas" na página /admin/painel/estatisticas, e uma página
Filament /admin/painel/configuracoes-ia para escolher o modelo (OpenRouter) e ver o crédito residual.
Detalhes completos e o que já existe estão em ARQUIVOS_MD/AI_SDK_INTEGRATION_PLAN.md (Fase 1) e o plano
desta fase em ARQUIVOS_MD/AI_SDK_FASE2_PLAN.md — LEIA OS DOIS antes de começar.

Tarefa: executar a FASE 2 (ARQUIVOS_MD/AI_SDK_FASE2_PLAN.md), em blocos A→B→C, seguindo À RISCA:
- Baby steps: cada passo entrega código + teste automatizado, e só avança após o teste passar.
- Nada pode quebrar a produção; feature é admin-only; isole das features de IA legadas (NÃO toque em
  ai_models nem TeseAnalysis*).
- Peça validação de frontend ao usuário quando o passo exigir conferência visual e AGUARDE o OK.
- Confirme as "decisões a confirmar" do plano com o usuário antes de codar cada bloco.
- Pint (vendor/bin/pint --dirty --format agent) e Pest (php artisan test --compact) antes de finalizar.
- Só faça commit/push (em master) com OK explícito do usuário; o push dispara deploy automático (Vito).
  Avise sobre migrations novas (Blocos A e C) e que o seeder de prompts é manual em prod.

Chaves de ambiente já existentes: OPENROUTER_API_KEY (requisições ao modelo) e
OPENROUTER_API_KEY_MANAGEMENT (crédito/catálogo). Mapeadas em config/services.php e config/ai.php.

Comece confirmando que leu os dois planos e me faça as perguntas das "decisões a confirmar" do Bloco A.
```

## Handoff de execução (estado interno — preencher ao pausar)

**Data:** 2026-05-29. **Onde paramos:** Bloco A 100% concluído, testado e **validado no frontend** pelo
usuário. **NADA foi commitado** — tudo na working tree, aguardando OK explícito para commit/push em `master`.

### Bloco A — arquivos entregues (working tree, não commitados)
- `database/migrations/2026_05_29_150037_create_ai_prompts_table.php` — tabela `ai_prompts`
  (`key` única, `title`, `content` text, `description` nullable, timestamps). **Já rodada no dev.**
- `app/Models/AiPrompt.php` — modelo + `AiPrompt::contentForKey(string $key): ?string`.
- `database/factories/AiPromptFactory.php` — factory.
- `database/seeders/AiPromptsSeeder.php` — `firstOrCreate` idempotente da key `stats_analyst_system`
  com `StatsAnalyst::defaultInstructions()`. **Já rodado no dev.** Manual em prod.
- `app/Ai/Agents/StatsAnalyst.php` — nova const `SYSTEM_PROMPT_KEY`; `instructions()` lê o `AiPrompt`
  com fallback; texto default extraído para `public static function defaultInstructions(): string`.
- `app/Filament/Resources/AiPromptResource.php` (+ `AiPromptResource/Pages/{List,Create,Edit}AiPrompt.php`)
  — CRUD flat (padrão `PlanFeatureResource`), grupo "Configurações", slug `ai-prompts`, `navigationSort=56`,
  `key` `disabledOn('edit')` + `unique(ignoreRecord)`, `content` Textarea 16 linhas.
- Testes: `tests/Feature/Ai/AiPromptTest.php` + `tests/Feature/Ai/AiPromptResourceTest.php` (20 verdes).
- Plano atualizado (este arquivo) — também na working tree (já estava `M` antes do Bloco A).

### Verificação no fecho do Bloco A
- `vendor/bin/pint --dirty --format agent` → pass.
- `php artisan test --compact` → **713 passou / 0 falhou / 75 skipped** (skips usuais MySQL/fulltext).
- Dev: `migrate` + `db:seed --class=AiPromptsSeeder` rodados; registro confirmado (849 chars).

### PRÓXIMO PASSO IMEDIATO (Bloco B — streaming)
1. **Antes de codar, perguntar/confirmar a "decisão B"** (ver seção "Decisões a confirmar"): abordagem
   recomendada `$this->stream()` do Livewire 3 consumindo `StatsAnalyst::stream()` do SDK — **sem** Reverb/Echo,
   sem novas deps. Lembrar a "nota honesta": streaming melhora UX mas **não elimina timeouts** de modelos lentos;
   manter `set_time_limit`/`timeout` da Fase 1.
2. Implementar em baby steps no `app/Livewire/StatsAiChat.php` (+ view), com teste verde antes de avançar
   (focar no resultado final em `$messages` e no tratamento de erro — streaming em si é difícil de asserir).
3. Validar frontend (ver tokens aparecendo) e aguardar OK.

### Itens a NÃO esquecer ao commitar (quando o usuário autorizar)
- Mensagem de commit em PT, escopo `feat(ia)`; **avisar** que o push dispara deploy Vito e que o
  **seeder de prompts é manual em prod**. Confirmar OK explícito antes de `git push`.
