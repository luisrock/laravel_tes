# AI SDK — Fase 2: Prompts gerenciáveis, Streaming e Conversa persistente

> Continuação de `AI_SDK_INTEGRATION_PLAN.md` (Fase 1, concluída). Mesmos princípios: **baby steps**,
> **teste automatizado verde antes de avançar**, **validação de frontend sob demanda**, **nada quebra em
> produção**, **isolamento** das features de IA legadas (Decifrando a Tese), **cautela máxima**, handoff
> de contexto quando crescer. Atualizar este checklist a cada iteração.

## Estado atual

- **Não iniciada.** Pré-requisito: Fase 1 commitada.
- Base já existente (Fase 1): `App\Ai\Agents\StatsAnalyst`, `App\Ai\Tools\QuerySiteMetrics`,
  `App\Services\Ai\OpenRouterManagementService`, `App\Filament\Pages\AiSettings`,
  `App\Livewire\StatsAiChat`, provider `openrouter` em `config/ai.php`, setting `ai_chat_model`.

## Motivação (pedidos do usuário)

1. **Editar o prompt (instruções) do assistente** na área de Configurações de IA; e, como virão outros
   prompts, montar um **CRUD de prompts**.
2. **Streaming** da resposta (token a token) — mais elegante e deve **eliminar os timeouts** da resposta síncrona.
3. **Conversa multi-turno com contexto** estilo ChatGPT. *(Já funciona de forma efêmera na Fase 1; aqui
   trata-se de persistir/retomar conversas.)*

---

## Decisões a confirmar com o usuário (antes de codar)

- **Escopo dos prompts no CRUD**: só o "system prompt" do StatsAnalyst, ou prompts reutilizáveis por
  várias áreas (chave + título + conteúdo + descrição)? Sugestão: modelo `AiPrompt` genérico com `key`
  única (ex.: `stats_analyst_system`), editável no Filament; o agente lê pela `key` com fallback ao
  prompt default em código.
- **Streaming no Filament/Livewire**: o AI SDK suporta `stream()` (SSE) e broadcasting. Em Livewire isso
  exige wire:stream ou broadcast via Echo/Reverb. Confirmar se já há Echo/Reverb no projeto (hoje não há).
  Alternativa mais simples sem infra extra: `wire:stream` do Livewire 3 chamando o iterador de eventos do SDK.
- **Persistência de conversa**: usar o trait `RemembersConversations` do SDK (publica migrations
  `agent_conversations`/`agent_conversation_messages`) — retomar por `conversationId`; ou tabela própria.
  Definir se haverá UI de "conversas anteriores".

---

## Esboço de passos (detalhar na execução)

### Bloco A — CRUD de prompts
- [ ] Migration + modelo `AiPrompt` (`key` única, `title`, `content`, `description`, timestamps) — **tabela nova,
      isolada de `ai_models`/`TeseAnalysis*`**. Factory + seeder com o prompt default do StatsAnalyst.
- [ ] `StatsAnalyst::instructions()` passa a ler `AiPrompt::forKey('stats_analyst_system')` com fallback ao texto atual.
- [ ] Filament `Resource` (ou página) para CRUD de prompts; ou seção editável na `AiSettings` para o prompt do assistente.
- [ ] Testes: persistência, fallback quando ausente, agente usando o prompt editado.

### Bloco B — Streaming
- [ ] Confirmar abordagem (wire:stream vs broadcast). Se broadcast: avaliar instalar Reverb (decisão do usuário, muda deps).
- [ ] Adaptar `StatsAiChat` para consumir `StatsAnalyst::stream()` e renderizar incrementalmente.
- [ ] Ajustar timeouts (o streaming reduz o risco de fatal por execução longa).
- [ ] Testes: usar fakes do SDK para stream; assert de eventos/append incremental.

### Bloco C — Conversa persistente
- [ ] Decidir `RemembersConversations` (publicar migrations do SDK que removemos na Fase 1) vs tabela própria.
- [ ] `StatsAiChat`: iniciar/continuar conversa por usuário; opcional UI de histórico de conversas.
- [ ] Testes: nova conversa, continuação, isolamento por usuário.

---

## Riscos / notas
- **Streaming** pode exigir infra (Reverb/Echo) — isso altera dependências; **pedir aprovação** antes.
- **Migrations** voltam a entrar em cena (Blocos A e C) → deploy Vito roda `migrate --force`; validar em dev primeiro.
- Manter tudo **admin-only** e isolado até a futura unificação do gestor de IA.

## Handoff
_Sem handoff pendente._
