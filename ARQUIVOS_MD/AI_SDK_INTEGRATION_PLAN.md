# Integração Laravel AI SDK — Plano & Checklist vivo

> **Documento vivo.** Marcar `[x]` os passos concluídos e indicar o próximo. Atualizar a cada iteração.
> Cada passo entrega **código + teste automatizado verde** antes de avançar. Validação de frontend é
> solicitada ao usuário quando indicado e aguarda OK. Site em **produção** — cautela máxima, nada pode quebrar.

## Estado atual

- **Passo atual:** Fase 1 **concluída** — aguardando confirmação do usuário para commit/push.
  Próximas features (CRUD de prompts, streaming, conversa persistente) em `AI_SDK_FASE2_PLAN.md`.
- **Última atualização:** 2026-05-29.
- **Branch:** master.
- **Concluído:** Passo 0; Passo 1 (`AiSdkConfigTest`, 4); Passo 2 (`OpenRouterManagementService` —
  rótulo `$X/M in · $Y/M out · 200K`; 6); Passo 3 **VALIDADO no frontend** (`AiSettings`,
  `configuracoes-ia`; 5); Passo 4 (`QuerySiteMetrics`; 4); Passo 5 (`app/Ai/Agents/StatsAnalyst.php` —
  `Agent,Conversational,HasTools`; provider `openrouter`, model de `SiteSetting`, history efêmero,
  tool `QuerySiteMetrics`, `isConfigured()`; `tests/Feature/Ai/StatsAnalystAgentTest.php`, 6). Pint pass.
- **Fakes do SDK:** `StatsAnalyst::fake([...])` + `assertPrompted()` (úteis no Passo 6).

---

## Contexto

**Fase 1** da integração do **Laravel AI SDK** (`laravel/ai`). Objetivo: o **admin** conversa com um modelo
de IA sobre as estatísticas em `/admin/painel/estatisticas` (Filament `SiteStats`) e, com **um clique**,
pede uma **avaliação das estatísticas exibidas na tela**. Provedor: **OpenRouter** (modelo configurável).
Painel **Configurações de IA** no Filament para escolher modelo e ver **crédito residual** da conta.

### Decisões confirmadas
- Resposta do chat: **síncrona com loading** (sem streaming).
- Histórico: **efêmero na sessão Livewire** (sem tabelas `agent_conversations`).
- Acesso aos dados: **tool de consulta** de métricas sob demanda.
- **+ Botão de avaliação one-click** das estatísticas do período atual.
- Layout: **painel de chat abaixo dos widgets** na página de estatísticas.

### Credenciais (.env — já existentes)
- `OPENROUTER_API_KEY` → requisições ao modelo (chat/avaliação).
- `OPENROUTER_API_KEY_MANAGEMENT` → gerenciamento (crédito residual, lista de modelos).
- `env()` só em `config/*`.

### Princípios
1. **Baby steps**; só avança após teste do passo anterior passar.
2. **Nada quebra em produção**; feature **admin-only**.
3. **Isolamento total** das features de IA existentes ("Decifrando a Tese": `ai_models`, `TeseAnalysis*`) —
   não tocar. Namespaces/settings próprios. *Futuro: o ai-sdk será o único gestor de IA e absorverá tudo.*
4. **Testes automatizados para tudo**.
5. **Validação de frontend sob demanda** (pausar e aguardar OK).
6. **Handoff de contexto** ao fim de cada iteração se o contexto crescer (ver secção final).
7. **Cautela**: aditivo, não destrutivo.

---

## Checklist

### Passo 0 — Documento vivo
- [x] Criar `ARQUIVOS_MD/AI_SDK_INTEGRATION_PLAN.md`.

### Passo 1 — Instalar SDK + configurar OpenRouter
- [x] `composer require laravel/ai` (^0.7.2); `vendor:publish` de `config/ai.php`. Migration do SDK removida.
- [x] Driver `openrouter` **nativo** confirmado (`Lab::OpenRouter`, `OpenRouterProvider`).
- [x] `config/services.php`: bloco `openrouter` (`key`, `management_key`, `base_url`).
- [x] `config/ai.php`: provider `openrouter` (já vinha nativo, lê `OPENROUTER_API_KEY`).
- [x] **Teste**: `tests/Feature/AiSdkConfigTest.php` (4 verde).

### Passo 2 — OpenRouterManagementService (crédito + catálogo)
- [x] `app/Services/Ai/OpenRouterManagementService.php` (injeta `Http\Client\Factory`).
      `remainingCredits()` (`/api/v1/credits`), `availableModels()` (`/api/v1/models`, cache 6h, filtra texto), `clearModelsCache()`.
- [x] **Teste**: `tests/Feature/Ai/OpenRouterManagementServiceTest.php` (6 verde).

### Passo 3 — Página Filament AiSettings (admin-only)
- [x] `app/Filament/Pages/AiSettings.php` (grupo `Configurações`, slug `configuracoes-ia`).
      Crédito em destaque; `Select` `ai_chat_model` searchable; persiste em `SiteSetting`; botão refresh.
- [x] **Teste**: `tests/Feature/Ai/AiSettingsPageTest.php` (5 verde).
- [ ] **VALIDAÇÃO FRONTEND** — aguardar OK (precisa de `OPENROUTER_API_KEY_MANAGEMENT` no `.env` dev).

### Passo 4 — Tool QuerySiteMetrics
- [x] `app/Ai/Tools/QuerySiteMetrics.php` (schema `period`; usa `SiteMetrics`+`SendyService`).
- [x] **Teste**: `tests/Feature/Ai/QuerySiteMetricsToolTest.php` (4 verde).

### Passo 5 — Agent StatsAnalyst
- [x] `app/Ai/Agents/StatsAnalyst.php` (`Agent,Conversational,HasTools`; provider `openrouter`,
      model de `SiteSetting`; tool `QuerySiteMetrics`; `isConfigured()`).
- [x] **Teste**: `tests/Feature/Ai/StatsAnalystAgentTest.php` (6 verde).

### Passo 6 — Chat + botão de avaliação na SiteStats
- [x] `app/Livewire/StatsAiChat.php` + `resources/views/livewire/stats-ai-chat.blade.php`; `send()` síncrono,
      `evaluateOnScreen()` (seletor de período próprio, inicia do filtro da página), guard, `clearConversation()`.
- [x] Embutido via `Filament\Schemas\Components\Livewire::make()` abaixo do grid em `SiteStats` (widgets intactos).
- [x] **Teste**: `tests/Feature/Ai/StatsAiChatTest.php` (8 verde, inclui render da página).
- [x] **VALIDAÇÃO FRONTEND OK** — chat e avaliação funcionando.
- [x] **Ajustes pós-validação**: CSS via componentes Filament; `wire:loading.flex` (loading só em curso);
      textarea `fi-fo-textarea` rows=4; timeout HTTP 120s + `maxSteps=6` + `set_time_limit` (resolveu fatal
      de 30s); reordenação (controles no topo, respostas abaixo, mais recente em cima); card movido para o
      fim da página (gráficos agora em `content()`, não em footer widgets).

### Passo 7 — Fecho
- [x] `vendor/bin/pint --dirty --format agent` (pass).
- [x] `php artisan test --compact` — **suíte completa: 700 passou, 75 skipped (MySQL/Sendy), 0 falhas**.
- [ ] Confirmar com usuário antes de commit/push (deploy automático Vito em prod). **PENDENTE.**

### Lembrete de produção (ao fazer deploy)
- Definir no `.env` de prod: `OPENROUTER_API_KEY`, `OPENROUTER_API_KEY_MANAGEMENT`
  (opcionais: `OPENROUTER_BASE_URL`, `OPENROUTER_REQUEST_TIMEOUT`).
- Sem migrations nesta fase → deploy Vito não exige passos extras. Escolher o modelo em
  `/admin/painel/configuracoes-ia` após o deploy.

---

## Isolamento — garantias
- Namespaces: `App\Services\Ai`, `App\Ai\Agents`, `App\Ai\Tools`, `App\Livewire\StatsAiChat`, `App\Filament\Pages\AiSettings`.
- Setting dedicado `ai_chat_model` (não usa `ai_models`/`TeseAnalysis*`).
- **Sem migrations** nesta fase. Tudo gated por admin.

---

## Handoff (preencher se trocar de chat/modelo)

_Sem handoff pendente. Ao crescer o contexto, registrar aqui: estado, passo atual, próximos passos,
arquivos tocados, comandos de teste a rodar._
