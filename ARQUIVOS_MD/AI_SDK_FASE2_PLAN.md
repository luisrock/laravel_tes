# AI SDK — Fase 2: Prompts gerenciáveis, Streaming e Conversa persistente

> Continuação de `AI_SDK_INTEGRATION_PLAN.md` (Fase 1 — **concluída e em produção**, commits `8966fe8`
> + `e7df5bd`). **Mesmos princípios, obrigatórios:** baby steps; **teste automatizado verde antes de
> avançar**; validação de frontend sob demanda (pausar e aguardar OK); **nada quebra em produção**
> (feature admin-only); **isolamento** das features de IA legadas ("Decifrando a Tese": `ai_models`,
> `TeseAnalysis*` — não tocar); testes para tudo; cautela máxima (aditivo, não destrutivo); handoff de
> contexto quando crescer. Atualizar este checklist a cada iteração (marcar `[x]`, indicar próximo passo).

## Estado atual

- **Bloco A CONCLUÍDO, validado e COMMITADO** (`e11d08c editor de prompts`).
- **Bloco B (streaming) CONCLUÍDO e validado** (frontend OK em 2026-05-29; decisão B = `$this->stream()` Livewire 3).
- **Bloco C (conversa persistente) CONCLUÍDO** (código + testes verdes) — **aguardando VALIDAÇÃO FRONTEND**.
  Decisões C: `RemembersConversations` do SDK; UI completa (lista/seletor); sem geração de título por LLM.
- **NADA dos Blocos B/C foi commitado** — tudo na working tree, aguardando OK explícito (push = deploy Vito).
- Fase 1 já em `master`. Em prod falta: `OPENROUTER_API_KEY` + `OPENROUTER_API_KEY_MANAGEMENT` no `.env` e
  escolher o modelo em `/admin/painel/configuracoes-ia`.
- **Pendências de prod ao commitar Blocos B/C**: a migration de conversas roda sozinha (`migrate --force`);
  o **seeder de prompts continua manual**: `php artisan db:seed --class=AiPromptsSeeder --force` (sem ele, o
  chat usa o fallback do system prompt). Streaming/persistência não exigem passos extras de deploy.

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

### Bloco B — Streaming (Livewire 3 `$this->stream()`) — **decisão B confirmada: `$this->stream()` sem Reverb/Echo**
- [x] Em `StatsAiChat::submitPrompt()`: itera `(new StatsAnalyst($history))->stream($prompt)`, empurra cada
      `TextDelta->delta` via `$this->stream(to: 'ai-answer', content: $delta, replace: false)`; ao terminar
      grava user+assistant em `$messages` (como antes). Evento `Streaming\Events\Error` vira exceção tratada.
- [x] Mantido `set_time_limit`/`timeout()` da Fase 1. Exceção/erro com a mesma UX de erro (catch `Throwable`).
- [x] View: contêiner `wire:stream="ai-answer"` (dentro de `wire:loading`) no lugar do indicador antigo; spinner
      mantido; texto final renderiza markdown em `$messages` (re-render limpa o alvo de stream ao concluir).
- [x] **Testes**: `StatsAiChatTest` (9 verdes) — happy path já exercita o streaming via `fake()` (deltas saem
      para `ai-answer`, comprovado no log) e o resultado final entra em `$messages`; novo teste cobre falha do
      modelo durante o streaming (sem gravar mensagens, `error` preenchido, `input` preservado). Pint pass; 44/44 em `tests/Feature/Ai`.
- [ ] **VALIDAÇÃO FRONTEND** — ver tokens aparecendo. Aguardar OK. **← PRÓXIMO PASSO (aguardando usuário)**

### Bloco C — Conversa persistente — **decisões C: RemembersConversations do SDK; UI completa (lista/seletor); sem geração de título por LLM**
- [x] Migration `database/migrations/2026_05_29_160000_create_agent_conversations_table.php` (copiada do SDK,
      `extends AiMigration`, tabelas `agent_conversations` + `agent_conversation_messages` via config). **Rodada no dev.**
      **Avisar deploy** (Vito roda `migrate --force` no push). Seção `conversations` em `config/ai.php`
      (`generate_title=false` → título = início do prompt, sem chamada LLM extra; nomes de tabela).
- [x] `StatsAnalyst`: trait `RemembersConversations` (removido o `messages()` efêmero e o construtor de history;
      `messages()` agora vem do `ConversationStore` quando há participante). `Conversational` satisfeito pelo trait.
- [x] `StatsAiChat`: `forUser()`/`continue()` no envio; `conversationId` persistido; ao montar retoma a última
      conversa do admin (`latestConversationId`); botão "Nova conversa"; lista/seletor (pills) das conversas
      anteriores com `loadConversation()` (checa posse). Persistência funciona no fluxo de streaming (middleware
      `RememberConversation` via `->then()`).
- [x] **Testes**: `ConversationPersistenceTest` (3); `StatsAnalystAgentTest` atualizado (participante/`continue`/store);
      `StatsAiChatTest` (13: persiste/exibe, multi-turno na mesma conversa, retoma ao montar, nova conversa,
      retomar conversa específica, isolamento por usuário, erro no streaming sem gravar). `AiSdkConfigTest`
      atualizado (migration agora existe). **Suíte completa: 723 passou / 0 falhou / 75 skipped.** Pint pass.
- [ ] **VALIDAÇÃO FRONTEND** — conversa persiste após recarregar; trocar entre conversas; "Nova conversa". **← PRÓXIMO (aguardando usuário)**
  - Ajustes pós-feedback (2026-05-29): lista de conversas virou **dropdown** "título — dd/mm/yyyy, HH:MM:SS"
    (mostra até 50 recentes; **todas** ficam persistidas); mensagens redesenhadas em **coluna única alinhada
    à esquerda, largura cheia** (rótulos "Você"/"Assistente", divisórias sutis) — a pedido do usuário.
  - **Esclarecido:** persistência guarda TODAS as conversas/mensagens; limite é só de exibição (50 na lista)
    e de contexto enviado ao modelo (100 msgs/conversa, padrão `maxConversationMessages()` do trait).

### Fecho
- [ ] `vendor/bin/pint --dirty --format agent`; `php artisan test --compact` (suíte completa verde).
- [ ] Atualizar checklist; confirmar com usuário antes de commit/push em `master` (deploy automático).

## Pendências registradas
- [x] **Prompt do botão "Avaliar estatísticas" agora é editável** (2026-05-29). `StatsAnalyst::EVALUATE_PROMPT_KEY`
      (`stats_analyst_evaluate`) + `defaultEvaluatePrompt()` (placeholder `{periodo}`) + `evaluatePromptFor($label)`
      com fallback. `evaluateOnScreen()` usa o builder; `AiPromptsSeeder` semeia a key (idempotente). Aparece no
      editor `AiPromptResource`. Testes em `AiPromptTest`. **Em prod**: rodar `db:seed --class=AiPromptsSeeder --force`
      para criar a key nova (sem ela, usa o fallback).
- [x] **Card do assistente colapsável** (2026-05-29). `<x-filament::section collapsible persist-collapsed
      collapse-id="stats-ai-chat">` — toggle no header; só o header fica visível ao encolher; estado persistido
      em localStorage (nativo do Filament/Alpine `$persist`). Sem código custom. Teste de render em `StatsAiChatTest`.

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
