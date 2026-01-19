# Plano de Implementação: Sistema de Assinaturas

**Documento:** ASSINATURA_PLAN.md  
**Versão:** 1.4  
**Status:** ✅ Aprovado para Implementação  
**Criado em:** 18 de Janeiro de 2026  
**Última Revisão:** 18 de Janeiro de 2026  
**Projeto:** Teses e Súmulas (https://tesesesumulas.com.br/)  
**Versão Laravel:** 8.x  
**PHP:** 8.3  

---

## 1. Visão Geral

### 1.1 Objetivo
Implementar sistema de assinaturas recorrentes usando **Laravel Cashier (Stripe)** para monetização do site Teses e Súmulas, permitindo que usuários assinem planos pagos para acessar conteúdo exclusivo e navegar sem anúncios.

### 1.2 Tecnologias Principais
| Componente | Tecnologia | Versão |
|------------|------------|--------|
| Framework | Laravel | 8.x |
| Billing | Laravel Cashier (Stripe) | ^13.x (compatível Laravel 8) |
| Gateway | Stripe Brasil | BRL |
| Admin Panel (novo) | Filament | 2.x (compatível Laravel 8) |
| Admin Panel (existente) | Custom | Manter em `/admin` |
| Roles/Permissions | Spatie Laravel Permission | 5.10 (já instalado) |
| Emails | Amazon SES | Já configurado |
| Newsletter | Sendy | newsletter.maurolopes.com.br |

### 1.3 Princípios de Desenvolvimento
- **DRY (Don't Repeat Yourself):** Reutilizar código, traits e helpers
- **Funções nativas Laravel:** Usar facades, collections, events, notifications
- **Nada hardcoded:** Valores, nomes de planos e configurações em banco ou Stripe
- **Sem redundância:** Não duplicar o que o Stripe Dashboard já oferece
- **Preparado para expansão:** Arquitetura que suporte assinaturas coletivas futuras
- **Identificadores centralizados:** Usar constantes/enums para nomes de subscriptions e feature keys (melhoria progressiva)

---

## 2. Arquitetura do Sistema

### 2.1 Estrutura de Planos

**Importante:** Cada tier é um PRODUTO separado no Stripe. Isso permite:
- Mapeamento correto de features por `stripe_product_id`
- Upgrade/downgrade entre produtos via Billing Portal
- Clareza semântica na organização

```
┌─────────────────────────────────────────────────────────┐
│                    STRIPE DASHBOARD                      │
│  (Gerencia: Produtos, Preços, Cupons, Webhooks)         │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Produto: "PRO" (prod_xxx_pro)                          │
│  ├── Price: Mensal (ex: R$ 29,90/mês)                   │
│  └── Price: Anual (ex: R$ 299,00/ano)                   │
│                                                          │
│  Produto: "PREMIUM" (prod_xxx_premium)                  │
│  ├── Price: Mensal (ex: R$ 49,90/mês)                   │
│  └── Price: Anual (ex: R$ 499,00/ano)                   │
│                                                          │
│  Cupons/Promotion Codes: No Stripe Dashboard            │
│  (usuário digita código diretamente no Stripe Checkout) │
│                                                          │
└─────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────┐
│                    LARAVEL + CASHIER                     │
│  (Gerencia: Usuários, Checkout, Portal, Features)       │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  - Sincroniza planos via Stripe API                     │
│  - Controla acesso por subscription status              │
│  - Envia emails transacionais (não redundantes)         │
│  - Admin Filament: visualização e ações sobre users     │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### 2.2 Fluxo do Usuário

```
┌─────────┐     ┌─────────────┐     ┌──────────────┐     ┌─────────────┐
│ Visitante│────▶│ Página de   │────▶│ Stripe       │────▶│ Página de   │
│          │     │ Planos      │     │ Checkout     │     │ Sucesso     │
└─────────┘     └─────────────┘     └──────────────┘     └──────┬──────┘
                                           │                    │
                                           ▼                    ▼
                                    ┌──────────────┐     ┌─────────────┐
                                    │ Webhook      │────▶│ Assinante   │
                                    │ Atualiza DB  │     │ Ativo       │
                                    └──────────────┘     └─────────────┘
```

**Tratamento da Página de Sucesso:**
- Stripe redireciona com `?session_id=cs_xxx` na URL
- Exibir "Pagamento em processamento..." inicialmente
- Usar polling AJAX consultando endpoint que verifica:
  1. Se a Checkout Session (via `session_id`) foi completada com sucesso (buscar na tabela `stripe_webhook_events` ou consultar Stripe API)
  2. Se o webhook `checkout.session.completed` já criou a subscription vinculada a essa session
- **IMPORTANTE:** NÃO usar `$user->subscribed()` como critério - pode retornar `true` para assinatura anterior em grace period
- Só confirmar quando a session específica (pelo `session_id`) tiver sido processada
- Evitar race condition entre redirect e webhook

### 2.3 Fluxo de Cancelamento

```
┌─────────────┐     ┌─────────────┐     ┌─────────────────────────┐
│ Assinante   │────▶│ Stripe      │────▶│ Subscription cancelada  │
│ clica       │     │ Billing     │     │ ao fim do período pago  │
│ "Cancelar"  │     │ Portal      │     │ (grace period)          │
└─────────────┘     └─────────────┘     └─────────────────────────┘
                                                    │
                                                    ▼
                                        ┌─────────────────────────┐
                                        │ Acesso mantido até      │
                                        │ ends_at (último dia)    │
                                        └─────────────────────────┘
```

---

## 3. Componentes do Sistema

### 3.1 Banco de Dados

#### 3.1.1 Alterações na tabela `users` (via migration Cashier)
```
+ stripe_id (varchar, nullable, index)
+ pm_type (varchar, nullable) -- tipo do payment method
+ pm_last_four (varchar, nullable) -- últimos 4 dígitos do cartão
+ trial_ends_at (timestamp, nullable) -- para trial futuro
```

#### 3.1.2 Nova tabela `subscriptions` (Cashier padrão + customização)
```
- id
- user_id (foreign key)
- name (varchar) -- nome interno da subscription, ex: "default"
- stripe_id (varchar, unique)
- stripe_status (varchar)
- stripe_price (varchar, nullable)
- quantity (integer, nullable)
- trial_ends_at (timestamp, nullable)
- ends_at (timestamp, nullable) -- quando cancelada
- current_period_end (timestamp, nullable) -- CUSTOM: data da próxima renovação
- created_at
- updated_at
```

**Nota:** A coluna `current_period_end` não faz parte do Cashier padrão. Deve ser adicionada via migration customizada.

**Atualização de `current_period_end`:**
- `customer.subscription.created` → Define valor inicial
- `customer.subscription.updated` → Atualiza quando período muda (ex: upgrade/downgrade)
- `invoice.payment_succeeded` → Atualiza após renovação bem-sucedida

**Job de lembrete de renovação:**
- **Regra MVP:** Notificar apenas assinaturas com `stripe_status = active`
- Filtrar apenas assinaturas com `stripe_status IN ('active')` - não notificar `past_due` (problema de pagamento)
- Ignorar assinaturas com `ends_at` preenchido (em grace period - já cancelaram)
- Verificar se `current_period_end` está a 7 dias do dia atual
- **Futuro:** Se implementar trial, incluir `trialing` na lista de status a notificar (lembrar que trial vai acabar)

#### 3.1.3 Nova tabela `subscription_items` (Cashier padrão)
```
- id
- subscription_id (foreign key)
- stripe_id (varchar, unique)
- stripe_product (varchar)
- stripe_price (varchar)
- quantity (integer, nullable)
- created_at
- updated_at
```

#### 3.1.4 Nova tabela `plan_features` (custom - autorização interna)
```
- id
- stripe_product_id (varchar) -- ID do produto no Stripe
- feature_key (varchar) -- ex: "no_ads", "exclusive_content", "ai_tools"
- feature_value (text, nullable) -- valor se aplicável
- created_at
- updated_at

-- CONSTRAINTS
UNIQUE(stripe_product_id, feature_key) -- evita duplicações acidentais
```

**Propósito:** Esta tabela NÃO é redundância com o Stripe. O Stripe não gerencia features de acesso (ex: no_ads, exclusive_content). Esta tabela é 100% gerenciada manualmente pelo admin via Filament para controlar quais features cada produto oferece.

**Nota sobre `feature_value`:**
- **MVP:** Para features booleanas (no_ads, exclusive_content), o valor é ignorado. A existência da linha indica acesso.
- **Futuro:** Para features com quota/limite (ex: `max_downloads = 100`, `ai_queries_per_month = 50`), usar `feature_value` com cast numérico na lógica de verificação.

**Nota sobre índice:** A constraint UNIQUE(stripe_product_id, feature_key) também funciona como índice para queries `WHERE stripe_product_id = ? AND feature_key = ?`.

**Nota:** Os nomes e preços dos planos são obtidos diretamente do Stripe via API, não duplicados no banco local.

#### 3.1.5 Nova tabela `refund_requests` (solicitações de estorno)
```
- id
- user_id (foreign key)
- cashier_subscription_id (bigint, nullable, FK -> subscriptions.id) -- referência local
- stripe_subscription_id (varchar) -- ID no Stripe (sub_xxx) para auditoria
- stripe_invoice_id (varchar, nullable) -- ID da última invoice (in_xxx) para estorno
- stripe_payment_intent_id (varchar, nullable) -- ID do payment intent (pi_xxx) para estorno
- reason (text)
- status (enum: pending, approved, rejected, processed)
- admin_notes (text, nullable)
- created_at
- updated_at
```

**Nota:** Usamos dois campos de subscription: `cashier_subscription_id` para integridade referencial local e `stripe_subscription_id` para referência direta ao Stripe.

**Nota sobre estorno:** Os campos `stripe_invoice_id` e `stripe_payment_intent_id` devem ser preenchidos automaticamente ao criar a solicitação, buscando a última invoice paga da assinatura. Esses IDs são necessários para processar o refund via Stripe API.

#### 3.1.6 Nova tabela `stripe_webhook_events` (idempotência de webhooks)
```
- id
- stripe_event_id (varchar, unique) -- ex: evt_xxx
- event_type (varchar) -- ex: checkout.session.completed
- stripe_object_id (varchar, nullable, index) -- ID do objeto principal (cs_xxx, sub_xxx, in_xxx)
- user_id (bigint, nullable, index) -- usuário associado (quando aplicável)
- received_at (timestamp) -- quando recebemos
- processed_at (timestamp, nullable) -- quando processamos com sucesso
- failed_at (timestamp, nullable) -- última falha (se houver)
- attempts (integer, default 0) -- tentativas de processamento
- last_error (text, nullable) -- último erro (para debug)
- created_at
- updated_at
```

**Propósito:** Garantir idempotência no processamento de webhooks. O Stripe pode reenviar eventos em caso de falha ou timeout. Esta tabela evita processamento duplicado e permite reprocessamento de eventos que falharam.

**Campos adicionais para polling:**
- `stripe_object_id`: Extraído do payload do evento (ex: `session.id` para checkout.session.completed)
- `user_id`: Extraído via `client_reference_id` ou pela subscription criada
- Permite que o endpoint de polling consulte: `WHERE stripe_object_id = ? AND event_type = 'checkout.session.completed' AND processed_at IS NOT NULL`

### 3.2 Models

#### 3.2.1 User Model (alterações)
```php
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, Billable;
    
    // Métodos adicionais para verificar features
    public function hasFeature(string $featureKey): bool
    public function isSubscriber(): bool
    public function getSubscriptionPlan(): ?string
    public function canAccessExclusiveContent(): bool
    public function shouldSeeAds(): bool
}
```

#### 3.2.2 PlanFeature Model (novo)
```php
class PlanFeature extends Model
{
    protected $fillable = ['stripe_product_id', 'feature_key', 'feature_value'];
    
    // Escopo para buscar features de um produto
    public function scopeForProduct($query, string $productId)
}
```

#### 3.2.3 RefundRequest Model (novo)
```php
class RefundRequest extends Model
{
    protected $fillable = [
        'user_id', 
        'cashier_subscription_id',
        'stripe_subscription_id',
        'stripe_invoice_id',
        'stripe_payment_intent_id',
        'reason', 
        'status', 
        'admin_notes'
    ];
    
    public function user(): BelongsTo
    public function subscription(): BelongsTo // FK local
}
```

### 3.3 Controllers

#### 3.3.1 SubscriptionController
- `index()` - Página de planos/preços (busca do Stripe)
- `checkout(Request $request)` - Redireciona para Stripe Checkout (via POST)
  - Recebe `priceId` via body do request (não na URL, para segurança)
  - **IMPORTANTE:** Validar `$priceId` contra allowlist de preços ativos antes de criar sessão
  - **ANTI-DUPLICAÇÃO:** Antes de criar sessão, verificar via `getSubscriptionSource()`:
    ```php
    $source = $user->getSubscriptionSource();
    if ($source && $source->subscribed('default')) {
        // Verificar stripe_product do item atual
        // Se mesmo produto → Redirecionar para Billing Portal
        // Se outro produto → Redirecionar para upgrade via Portal
    }
    ```
  - Isso mantém consistência com `hasFeature()` e prepara para assinaturas coletivas
- `success()` - Callback de sucesso (exibe "processando", aguarda webhook, usa `session_id`)
- `cancel()` - Callback de cancelamento/desistência
- `show()` - Status da assinatura do usuário, link para portal, aviso de grace period
- `billingPortal()` - Redireciona para Stripe Billing Portal

#### 3.3.2 RefundRequestController
- `create()` - Formulário de solicitação
- `store()` - Salva solicitação

#### 3.3.3 WebhookController (extends Cashier)
- Handlers customizados para eventos específicos

### 3.4 Middlewares

#### 3.4.1 EnsureUserIsSubscribed
```php
// Verifica se usuário tem assinatura ativa
// Redireciona para página de planos se não tiver
```

#### 3.4.2 EnsureUserHasFeature
```php
// Verifica se usuário tem acesso a feature específica
// Parâmetro: feature_key
```

### 3.5 Services

#### 3.5.1 StripeService
```php
class StripeService
{
    // Busca produtos e preços do Stripe
    public function getActiveProducts(): Collection
    public function getPricesForProduct(string $productId): Collection
    public function getFormattedPlans(): array // Cache de 1 hora
    public function getAllowedPriceIds(): array // Lista de priceIds válidos para checkout
    public function isValidPriceId(string $priceId): bool // Validação de segurança
}
```

#### 3.5.2 SubscriptionService
```php
class SubscriptionService
{
    public function getUserFeatures(User $user): array
    public function validatePlanFeaturesIntegrity(): array // Retorna products órfãos ou sem features (debug/admin)
}
```

**Nota:** Features são gerenciadas 100% manualmente via Filament. Não há sincronização automática com Stripe. O método `validatePlanFeaturesIntegrity()` é apenas para auditoria (ex: listar produtos do Stripe que não têm features configuradas).

### 3.6 Events & Listeners

#### 3.6.1 Events (do Cashier - já existentes)
- `WebhookReceived`
- `WebhookHandled`

#### 3.6.2 Listeners Customizados
```php
// Listeners para eventos de webhook
SubscriptionCreatedListener::class      // subscription.created
SubscriptionCanceledListener::class     // subscription.updated (cancel_at_period_end=true)
SubscriptionEndedListener::class        // subscription.deleted (encerramento efetivo)
SubscriptionRenewedListener::class      // invoice.payment_succeeded (atualiza current_period_end)
PaymentFailedListener::class            // invoice.payment_failed (ver nota abaixo)
CheckoutCompletedListener::class        // checkout.session.completed
```

**Nota sobre cancelamento:**
- `subscription.updated` com `cancel_at_period_end=true` → Email "Você cancelou, acesso até X"
- `subscription.deleted` → Email final de encerramento (opcional)

**Nota sobre PaymentFailedListener (MVP):**
- **NÃO envia email** (Stripe já envia via Smart Retries)
- Apenas atualiza estado local para exibir banner in-app
- Registra log para monitoramento
- Pode setar flag `$user->has_payment_issue = true` para condicionar banner na UI

### 3.7 Notifications (Emails)

#### 3.7.1 Emails Transacionais do Sistema (não redundantes com Stripe)
| Email | Trigger | Conteúdo |
|-------|---------|----------|
| WelcomeSubscriber | Após checkout sucesso | Boas-vindas, como usar features |
| SubscriptionRenewingSoon | 7 dias antes da renovação | Lembrete, como cancelar se quiser |
| SubscriptionCanceled | Após cancelamento | Confirma, informa até quando tem acesso |
| RefundRequestReceived | Após solicitação de estorno | Confirma recebimento |

**Nota:** Emails de cobrança, falha de pagamento e recibos são enviados pelo próprio Stripe.

### 3.8 Views/Pages

#### 3.8.1 Páginas Públicas
- `/assinar` - Lista de planos com preços (dinâmico do Stripe)
- `/assinar/sucesso` - Confirmação pós-checkout
- `/assinar/cancelado` - Usuário desistiu do checkout

#### 3.8.2 Páginas Autenticadas (Assinante)
- `/minha-conta/assinatura` - Status da assinatura, link para portal Stripe
- `/minha-conta/estorno` - Formulário de solicitação de estorno

#### 3.8.3 Admin Filament
- Dashboard com métricas de assinaturas
- Lista de usuários com status de assinatura
- Lista de solicitações de estorno
- Gerenciamento de features por plano

---

## 4. Configurações

### 4.1 Variáveis de Ambiente (.env)

```env
# Stripe
STRIPE_KEY=pk_live_xxx
STRIPE_SECRET=sk_live_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx

# Cashier
CASHIER_CURRENCY=brl
CASHIER_CURRENCY_LOCALE=pt_BR

# URLs (para webhooks e redirects)
APP_URL=https://tesesesumulas.com.br
```

### 4.2 Config Cashier (config/cashier.php)

```php
return [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook' => [
        'secret' => env('STRIPE_WEBHOOK_SECRET'),
        'tolerance' => 300,
    ],
    'currency' => env('CASHIER_CURRENCY', 'brl'),
    'currency_locale' => env('CASHIER_CURRENCY_LOCALE', 'pt_BR'),
    'payment_notification' => null, // Stripe já envia
    'invoice_paper' => 'a4',
];
```

### 4.2.1 Config Subscription (config/subscription.php)

```php
return [
    // IDs dos produtos que representam tiers de assinatura
    // Usado em hasFeature() para identificar o item correto
    // OBRIGATÓRIO: Definir em .env (sem defaults para evitar erros silenciosos)
    'tier_product_ids' => array_filter([
        env('STRIPE_PRODUCT_PRO'),      // Obrigatório
        env('STRIPE_PRODUCT_PREMIUM'),  // Obrigatório
    ]),
];
```

**Validação no boot (AppServiceProvider):**
```php
public function boot()
{
    // Validar configuração crítica de assinaturas
    if (empty(config('subscription.tier_product_ids'))) {
        Log::critical('STRIPE_PRODUCT_PRO e STRIPE_PRODUCT_PREMIUM não configurados!');
        
        // Em produção, considerar lançar exception
        if (app()->environment('production')) {
            throw new \RuntimeException('Configuração de produtos Stripe ausente');
        }
    }
}
```

### 4.3 Configurações no Stripe Dashboard

#### 4.3.1 Produtos
- Criar produtos para cada tier (PRO, PREMIUM)
- Definir preços (mensal/anual) para cada produto
- Metadados do produto: `tier: pro` ou `tier: premium`

#### 4.3.2 Billing Portal
- Configurar em Stripe > Settings > Billing > Customer Portal
- Permitir: cancelamento, troca de plano, atualização de pagamento
- URL de retorno: `https://tesesesumulas.com.br/minha-conta/assinatura`

#### 4.3.3 Webhooks
- Endpoint: `https://tesesesumulas.com.br/stripe/webhook`
- Eventos a escutar:
  - `checkout.session.completed` -- ESSENCIAL: vincula sessão ao usuário
  - `customer.subscription.created`
  - `customer.subscription.updated` -- inclui cancelamento agendado
  - `customer.subscription.deleted` -- encerramento efetivo
  - `invoice.payment_succeeded` -- atualiza current_period_end
  - `invoice.payment_failed`
  - `customer.updated`

**Vinculação de usuário:**
Ao criar a Checkout Session, usar `client_reference_id` com o `user_id` do Laravel.
No webhook `checkout.session.completed`, reconciliar usando esse ID.

#### 4.3.4 Cupons e Promotion Codes
- Criar Promotion Codes no Stripe Dashboard
- Habilitar campo "Promotion Code" no Stripe Checkout
- Usuário digita o código diretamente na interface do Stripe
- **Não implementar** passagem de cupom via URL (segurança)

---

## 5. Rotas

### 5.1 Rotas Públicas

```php
// Página de planos
Route::get('/assinar', [SubscriptionController::class, 'index'])->name('subscription.plans');

// Checkout (requer auth)
Route::middleware('auth')->group(function () {
    Route::post('/assinar/checkout', [SubscriptionController::class, 'checkout'])
        ->name('subscription.checkout'); // priceId via body (POST), não na URL
});

// Callbacks do Stripe Checkout
// DECISÃO: Manter FORA de auth para evitar problemas de sessão expirada
// A validação é feita via session_id (verificando stripe_webhook_events)
Route::get('/assinar/sucesso', [SubscriptionController::class, 'success'])
    ->name('subscription.success');
Route::get('/assinar/cancelado', [SubscriptionController::class, 'cancel'])
    ->name('subscription.cancel');

// Segurança da página de sucesso (sem auth):
// 1. Requer session_id válido na query string
// 2. Valida session_id contra stripe_webhook_events.stripe_object_id
// 3. Só exibe confirmação se processed_at != null
// 4. Não expor dados sensíveis - apenas "Pagamento confirmado" ou "Processando"

// Webhook Stripe
// IMPORTANTE: Usar a rota padrão do Cashier OU aplicar middleware explicitamente
// Opção A (recomendada): Estender CashierController e usar rota padrão do Cashier
// Opção B: Aplicar middleware manualmente:
Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook'])
    ->middleware('stripe.webhook') // Middleware de verificação de assinatura
    ->name('cashier.webhook');

// O WebhookController DEVE estender Laravel\Cashier\Http\Controllers\WebhookController
// para herdar a validação de assinatura do Stripe
```

### 5.2 Rotas Autenticadas (Assinante)

```php
Route::middleware(['auth', 'verified'])->prefix('minha-conta')->group(function () {
    Route::get('/assinatura', [SubscriptionController::class, 'show'])
        ->name('subscription.show');
    Route::get('/assinatura/portal', [SubscriptionController::class, 'billingPortal'])
        ->name('subscription.portal');
    
    Route::get('/estorno', [RefundRequestController::class, 'create'])
        ->name('refund.create');
    Route::post('/estorno', [RefundRequestController::class, 'store'])
        ->name('refund.store');
});
```

### 5.3 Rotas Admin Existente (/admin)

```php
// Manter todas as rotas atuais
// Não adicionar nada relacionado a assinaturas aqui
```

### 5.4 Rotas Admin Filament (/painel)

```php
// Filament gerencia automaticamente
// Configurar em FilamentServiceProvider ou AdminPanelProvider
// Rota base: /painel
```

---

## 6. Stripe Billing Portal

### 6.1 Funcionalidades Delegadas ao Portal Stripe
O Billing Portal do Stripe já oferece interface pronta para:

- ✅ Ver assinatura atual
- ✅ Trocar de plano (upgrade/downgrade)
- ✅ Atualizar método de pagamento
- ✅ Cancelar assinatura
- ✅ Ver histórico de faturas
- ✅ Baixar recibos/invoices

**Vantagem:** Não precisamos implementar essas telas, apenas redirecionar.

### 6.2 Configuração do Portal
No Stripe Dashboard > Settings > Billing > Customer Portal:

```
Branding: Logo do Teses e Súmulas
Business Name: Teses e Súmulas
Privacy Policy: https://tesesesumulas.com.br/privacidade
Terms of Service: https://tesesesumulas.com.br/termos

Features:
☑ Customers can update payment methods
☑ Customers can view invoice history
☑ Customers can update subscriptions
☑ Customers can cancel subscriptions

Cancellation:
- Collect cancellation reason: Yes
- Cancel immediately: No (cancelar ao fim do período)

Subscription update:
- Products: [Lista de produtos criados]
- Proration behavior: Create prorations
```

**Nota sobre Proration:**
- **Upgrade:** Cobrança proporcional imediata (padrão recomendado)
- **Downgrade:** Crédito aplicado nas próximas faturas (evita cobrança imediata de valor negativo)
- Configurar no Stripe Dashboard: `Settings > Billing > Subscriptions > Proration behavior`
- Opção `Always invoice immediately` pode ser agressiva para downgrades; preferir `Create prorations`

---

## 7. Grace Period (Período de Carência)

### 7.1 Comportamento após Cancelamento

Quando um usuário cancela a assinatura:

1. **Stripe marca** `cancel_at_period_end = true`
2. **Cashier atualiza** `ends_at` na tabela `subscriptions` com a data do fim do período
3. **Usuário mantém acesso** até `ends_at`
4. **Método `subscribed()`** retorna `true` durante grace period
5. **Método `onGracePeriod()`** retorna `true`
6. **Após `ends_at`:** `subscribed()` retorna `false`

### 7.2 Verificação no Código

```php
// Usuário ainda é assinante (inclui grace period)
if ($user->subscribed('default')) {
    // Tem acesso
}

// Usuário está no grace period (cancelou mas ainda tem acesso)
if ($user->subscription('default')?->onGracePeriod()) {
    // Mostrar aviso: "Sua assinatura termina em X dias"
}

// Usuário é assinante ativo (não cancelou)
if ($user->subscription('default')?->active()) {
    // Assinatura ativa sem cancelamento pendente
}
```

---

## 8. Sistema de Features

### 8.1 Mapeamento de Features por Plano

```php
// Tabela plan_features
// stripe_product_id | feature_key        | feature_value
// prod_xxx_pro      | no_ads            | true
// prod_xxx_pro      | exclusive_content | true
// prod_xxx_premium  | no_ads            | true
// prod_xxx_premium  | exclusive_content | true
// prod_xxx_premium  | ai_tools          | true  // futuro
```

### 8.2 Helper de Verificação

```php
// No User model
public function hasFeature(string $key): bool
{
    // Usa getSubscriptionSource() para suportar assinaturas coletivas futuras
    $source = $this->getSubscriptionSource();
    
    if (!$source || !$source->subscribed('default')) {
        return false;
    }
    
    // Null-safe: verifica se existe subscription e item
    $subscription = $source->subscription('default');
    if (!$subscription) {
        return false;
    }
    
    // Busca o item do tier (produto que está na nossa lista de tiers)
    $tierProductIds = config('subscription.tier_product_ids', []);
    
    // SEM FALLBACK: Se config não estiver definida, é erro de configuração
    if (empty($tierProductIds)) {
        Log::error('hasFeature: tier_product_ids não configurado', [
            'user_id' => $this->id,
            'feature_key' => $key
        ]);
        return false; // Nega acesso por segurança
    }
    
    $item = $subscription->items()
        ->whereIn('stripe_product', $tierProductIds)
        ->first();
    
    // Se não encontrou item do tier, é problema de integridade
    if (!$item) {
        Log::warning('hasFeature: subscription sem item de tier válido', [
            'user_id' => $this->id,
            'subscription_id' => $subscription->id,
            'tier_product_ids' => $tierProductIds
        ]);
        return false; // Nega acesso por segurança
    }
    
    return PlanFeature::where('stripe_product_id', $item->stripe_product)
        ->where('feature_key', $key)
        ->exists();
}

// Uso em views
@if(auth()->user()?->hasFeature('no_ads'))
    {{-- Não mostra ads --}}
@else
    @include('partials.ads')
@endif
```

**Melhoria futura:** Centralizar keys de features em constantes/enum para evitar typos.

### 8.3 Middleware de Feature

```php
// Uso em rotas
Route::get('/conteudo-exclusivo', [ContentController::class, 'exclusive'])
    ->middleware('feature:exclusive_content');
```

---

## 9. Preparação para Assinaturas Coletivas (Futuro)

### 9.1 Arquitetura Prevista

```
┌─────────────────────────────────────────────────────────┐
│                    TEAM/ORGANIZATION                     │
├─────────────────────────────────────────────────────────┤
│  id                                                      │
│  name (ex: "Escritório Silva Advogados")                │
│  owner_id (user_id do admin)                            │
│  stripe_id (customer id da org)                         │
│  max_seats (quantidade de licenças)                     │
│  created_at                                             │
└─────────────────────────────────────────────────────────┘
          │
          │ hasMany
          ▼
┌─────────────────────────────────────────────────────────┐
│                    TEAM_USER (pivot)                     │
├─────────────────────────────────────────────────────────┤
│  team_id                                                 │
│  user_id                                                 │
│  role (admin, member)                                   │
│  invited_at                                             │
│  accepted_at                                            │
└─────────────────────────────────────────────────────────┘
```

### 9.2 Preparações no Código Atual

Para facilitar a implementação futura:

1. **User Model:** Adicionar método `getSubscriptionSource()`
   ```php
   public function getSubscriptionSource(): ?Model
   {
       // Hoje: retorna $this (assinatura individual)
       // Futuro: pode retornar Team se usuário faz parte de um
       return $this;
   }
   ```

2. **Trait Billable:** O User já usa. No futuro, Team também usará.

3. **Verificações de Acesso:** Sempre passar pelo `getSubscriptionSource()`
   ```php
   public function isSubscriber(): bool
   {
       $source = $this->getSubscriptionSource();
       return $source?->subscribed('default') ?? false;
   }
   ```

4. **Não hardcodar** verificações diretas de `$user->subscribed()` em controllers.

---

## 10. Admin Panel (Filament)

### 10.1 Instalação e Configuração

```bash
composer require filament/filament:"^2.0"
php artisan filament:install
php artisan make:filament-user
```

**Nota:** O comando `--panels` é do Filament 3.x. No Filament 2.x, usar apenas `filament:install`.

**Rota base:** `/painel` (para não conflitar com `/admin` existente)

Configurar em `config/filament.php`:
```php
'path' => 'painel',
```

### 10.2 Resources do Filament

#### 10.2.1 UserResource
- Lista de usuários com coluna de status de assinatura
- Filtros: assinantes, não-assinantes, grace period, plano
- Ações: ver detalhes, ver no Stripe (link externo)
- **Não permite editar assinatura** (fazer no Stripe)

#### 10.2.2 RefundRequestResource
- Lista de solicitações de estorno
- Status: pendente, aprovado, rejeitado, processado
- Ações: aprovar, rejeitar, adicionar notas
- Link para processar estorno no Stripe

#### 10.2.3 PlanFeatureResource
- CRUD de features por produto Stripe
- Dropdown de seleção com produtos ativos do Stripe (busca via API)
- Alerta visual para produtos órfãos (no Stripe mas sem features configuradas)
- **Não há sincronização automática** - admin gerencia manualmente

### 10.3 Dashboard Widgets

1. **Métricas Simples (contagens locais)**
   - Total de assinantes ativos
   - Assinantes em grace period
   - Novos assinantes (mês atual)
   
2. **Tabelas Recentes**
   - Últimas assinaturas
   - Últimos cancelamentos
   - Solicitações de estorno pendentes

3. **Links Externos**
   - Botão "Ver no Stripe Dashboard" para métricas financeiras (MRR, churn, receita)

**Política:** Métricas financeiras detalhadas (MRR, churn rate, receita) ficam no Stripe Dashboard. Não duplicar cálculos complexos localmente.

---

## 11. Emails Transacionais

### 11.1 Configuração SES

Verificar arquivo `config/mail.php`:
```php
'default' => env('MAIL_MAILER', 'ses'),
```

Verificar `.env`:
```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=xxx
AWS_SECRET_ACCESS_KEY=xxx
AWS_DEFAULT_REGION=us-east-1
```

### 11.2 Emails do Sistema (Laravel)

| Notification Class | Trigger | Assunto |
|--------------------|---------|---------|
| `WelcomeSubscriberNotification` | Webhook: checkout.session.completed | "Bem-vindo ao plano {plan}!" |
| `SubscriptionRenewingSoonNotification` | Job agendado (7 dias antes, usa `current_period_end`) | "Sua assinatura será renovada em 7 dias" |
| `SubscriptionCanceledNotification` | Webhook: subscription.updated (cancel_at_period_end=true) | "Você cancelou. Acesso até {date}" |
| `RefundRequestReceivedNotification` | Após submit do form | "Recebemos sua solicitação de estorno" |

**Nota:** `PaymentFailedNotification` foi removido do MVP. O Stripe já envia emails automáticos de falha de pagamento via Smart Retries. Avaliar necessidade futura apenas se o email do Stripe não for suficiente.

### 11.3 Emails do Stripe (não duplicar)

O Stripe envia automaticamente:
- Recibo de pagamento (invoice.payment_succeeded)
- Invoice/fatura
- Atualização de método de pagamento
- **Falha de pagamento** (via Smart Retries)

**Política de emails - Regra Objetiva:**

| Situação | Quem Envia | Ação |
|----------|-----------|------|
| Pagamento bem-sucedido (recibo) | Stripe | Nós não enviamos |
| Boas-vindas ao plano | Nós | Stripe não envia |
| Falha de pagamento | Stripe (Smart Retries) | **MVP: Nós não enviamos email, apenas banner in-app** |
| Lembrete de renovação (7 dias) | Nós | Stripe não envia por padrão |
| Confirmação de cancelamento | Nós | Com data de acesso restante |
| Invoice/fatura mensal | Stripe | Nós não enviamos |

**Futuro (pós-MVP):** Se o email do Stripe para falha de pagamento não for suficiente, implementar nosso email **apenas após a 3ª tentativa falha** (verificar via webhook `invoice.payment_failed` + contador de `attempt_count`).

---

## 12. Fases de Implementação

### Fase 1: Infraestrutura Base (Prioridade Alta)
1. Instalar Laravel Cashier
2. Rodar migrations do Cashier
3. Configurar variáveis de ambiente
4. Adicionar trait Billable ao User
5. Criar tabelas customizadas (plan_features, refund_requests, stripe_webhook_events)
6. Configurar webhook no Stripe Dashboard
7. **Criar constantes/enums para identificadores:**
   - `SubscriptionName::DEFAULT = 'default'`
   - `FeatureKey::NO_ADS = 'no_ads'`
   - `FeatureKey::EXCLUSIVE_CONTENT = 'exclusive_content'`
   - Evita typos e facilita refactoring

### Fase 2: Registro e Autenticação (Prioridade Alta)
1. Habilitar registro público (`'register' => true`)
2. Adaptar views de auth ao layout do site (front.base)
3. Verificar envio de email de verificação (se desejado)

**Nota sobre roles:** Não criar role 'subscriber' via Spatie. O status de assinante vem exclusivamente do Cashier (`$user->subscribed()`). Roles Spatie são para permissões administrativas (admin, editor), não para billing.

### Fase 3: Checkout e Assinatura (Prioridade Alta)
1. Criar StripeService para buscar planos
2. Criar página de planos (`/assinar`)
3. Implementar checkout via Stripe Checkout
4. Criar páginas de sucesso/cancelamento
5. Implementar redirecionamento para Billing Portal

### Fase 4: Controle de Acesso (Prioridade Alta)
1. Implementar middlewares (EnsureUserIsSubscribed, EnsureUserHasFeature)
2. Criar helpers no User model (hasFeature, isSubscriber, shouldSeeAds)
3. Integrar verificação de ads nas views
4. Proteger rotas de conteúdo exclusivo

### Fase 5: Webhooks e Eventos (Prioridade Alta)
1. Configurar WebhookController customizado
2. Criar Listeners para eventos
3. Implementar envio de emails transacionais
4. Testar fluxo completo com Stripe CLI

### Fase 6: Estorno e Suporte (Prioridade Média)
1. Criar formulário de solicitação de estorno
2. Criar RefundRequest model e migration
3. Implementar controller e views

### Fase 7: Admin Filament (Prioridade Média)
1. Instalar Filament 2.x
2. Configurar rota /painel
3. Criar UserResource com filtros de assinatura
4. Criar RefundRequestResource
5. Criar PlanFeatureResource
6. Implementar Dashboard widgets

### Fase 8: Otimizações (Prioridade Baixa)
1. Implementar cache para planos do Stripe
2. Criar job para enviar lembrete de renovação
3. Adicionar métricas ao dashboard
4. Testes automatizados

---

## 13. Checklist de Configuração Stripe

### 13.1 Antes do Desenvolvimento
- [ ] Criar conta Stripe Brasil (se não existir)
- [ ] Obter chaves de API (test mode primeiro)
- [ ] Criar produtos e preços no Dashboard
- [ ] Configurar Billing Portal
- [ ] Configurar webhook endpoint

### 13.2 Antes de Ir para Produção
- [ ] Verificar conta Stripe (documentos)
- [ ] Ativar modo Live
- [ ] Atualizar chaves no .env de produção
- [ ] Atualizar webhook secret
- [ ] Testar fluxo completo em produção

---

## 14. Compatibilidade e Versões

### 14.1 Requisitos de Versão

| Pacote | Versão Requerida | Motivo |
|--------|------------------|--------|
| Laravel | 8.x | Versão atual do projeto |
| PHP | 8.3 | Versão em dev e produção |
| Laravel Cashier | ^13.x | Última versão compatível com Laravel 8 |
| Filament | ^2.x | Última versão compatível com Laravel 8 |
| Stripe PHP SDK | ^7.0 ou ^10.0 | Instalado automaticamente pelo Cashier |

### 14.2 Comandos de Instalação

```bash
# Cashier
composer require laravel/cashier:^13.0

# Filament
composer require filament/filament:^2.0
```

---

## 15. Considerações de Segurança

### 15.1 Webhook
- Verificação de assinatura do Stripe (automática via Cashier)
- Rota excluída do CSRF middleware
- HTTPS obrigatório em produção

### 15.1.1 Idempotência e Filas para Webhooks

**Problema:** O Stripe pode reenviar webhooks em caso de falha ou timeout. Processar o mesmo evento duas vezes pode causar emails duplicados ou inconsistências.

**Pré-requisito:** A validação de assinatura do Stripe (webhook secret) é feita automaticamente pelo Cashier **antes** de qualquer código customizado ser executado. O exemplo abaixo assume que a rota já passou por essa validação.

**Solução:**

1. **Tabela de eventos processados:** Ver seção 3.1.6 (`stripe_webhook_events`)

2. **Padrão atômico no WebhookController:**
   ```php
   // IMPORTANTE: Este código roda APÓS a validação do Cashier (webhook secret)
   // O WebhookController DEVE estender Laravel\Cashier\Http\Controllers\WebhookController
   
   public function handleWebhook(Request $request)
   {
       $eventId = $request->input('id');
       $eventType = $request->input('type');
       $payload = $request->input('data.object', []);
       
       // Extrair stripe_object_id dependendo do tipo de evento
       $stripeObjectId = $this->extractObjectId($eventType, $payload);
       $userId = $payload['client_reference_id'] ?? null; // Para checkout.session.completed
       
       // Padrão atômico: firstOrCreate evita race condition
       $webhookEvent = StripeWebhookEvent::firstOrCreate(
           ['stripe_event_id' => $eventId],
           [
               'event_type' => $eventType,
               'stripe_object_id' => $stripeObjectId,
               'user_id' => $userId,
               'received_at' => now()
           ]
       );
       
       // Se já foi processado com sucesso, retorna early
       if ($webhookEvent->processed_at !== null) {
           return response()->json(['status' => 'already_processed']);
       }
       
       // Se não foi recém-criado E não está processado, é reprocessamento de falha
       if (!$webhookEvent->wasRecentlyCreated) {
           $webhookEvent->increment('attempts');
       }
       
       try {
           $this->processEvent($request);
           
           // Só marca como processado APÓS sucesso
           $webhookEvent->update([
               'processed_at' => now(),
               'failed_at' => null,
               'last_error' => null
           ]);
           
       } catch (\Exception $e) {
           $webhookEvent->update([
               'failed_at' => now(),
               'last_error' => $e->getMessage()
           ]);
           throw $e;
       }
       
       return response()->json(['status' => 'processed']);
   }
   
   private function extractObjectId(string $eventType, array $payload): ?string
   {
       return match($eventType) {
           'checkout.session.completed' => $payload['id'] ?? null,        // cs_xxx
           'customer.subscription.created',
           'customer.subscription.updated',
           'customer.subscription.deleted' => $payload['id'] ?? null,     // sub_xxx
           'invoice.payment_succeeded',
           'invoice.payment_failed' => $payload['id'] ?? null,            // in_xxx
           default => $payload['id'] ?? null,
       };
   }
   ```

3. **Processamento em filas (recomendado):**
   - Usar `ShouldQueue` nos Listeners para webhooks
   - Responder 200 rapidamente e processar em background
   - Evita timeout do Stripe (5 segundos)
   - Marcar `processed_at` após o job ser enfileirado com sucesso

### 15.2 Validação de Price ID no Checkout
- **OBRIGATÓRIO:** Antes de criar Checkout Session, validar que o `priceId` recebido está na allowlist de preços ativos dos produtos esperados
- Buscar preços ativos via StripeService e recusar qualquer ID não listado
- Isso previne uso de preços de teste, desativados ou não autorizados

### 15.3 Dados Sensíveis
- Chaves API apenas no .env (nunca versionadas)
- Dados de cartão nunca tocam no servidor (Stripe Checkout)
- stripe_id do usuário é o único dado armazenado localmente

### 15.4 Controle de Acesso
- Middlewares para todas as rotas protegidas
- Verificação server-side (não confiar apenas em JS)

**Nota sobre Activity Log:** O Filament não inclui activity log automático. Para MVP, logs básicos via `Log::info()` são suficientes. Activity log estruturado (spatie/laravel-activitylog) pode ser adicionado em fase futura se necessário.

---

## 16. Monitoramento e Logs

### 16.1 Logs Importantes
- Falhas de webhook
- Erros de sincronização com Stripe
- Solicitações de estorno

### 16.2 Métricas Recomendadas
- Taxa de conversão: visitantes → assinantes
- Tempo médio de assinatura
- Taxa de churn mensal

---

## 17. Próximos Passos

1. **Revisar este documento** e aprovar escopo
2. **Criar ASSINATURA_SPECS.md** com especificações técnicas detalhadas
3. **Implementar** seguindo as fases definidas
4. **Testar** em ambiente de desenvolvimento (teses.test)
5. **Deploy** para produção (tesesesumulas.com.br)

---

## 18. Status Atual (Jan/2026)

**Resumo rápido (implementado até agora):**
- Guard suave em rotas de assinatura (`subscription.configured`).
- Webhook protegido com `stripe.webhook` + correção no handler de `invoice.payment_succeeded`.
- UI: link de estorno discreto em "Minha Assinatura".
- Script de teste automatizado criado: `scripts/test-subscription-flow.sh`.
- Fluxo validado em modo test via Stripe CLI.

**Referência detalhada:** consulte `ASSINATURA_SPECS.md` (seções “STATUS DA IMPLEMENTAÇÃO” e “Testes Realizados”).

---

## Anexo A: Glossário

| Termo | Definição |
|-------|-----------|
| **Cashier** | Biblioteca oficial do Laravel para integração com Stripe |
| **Stripe Checkout** | Página de pagamento hospedada pelo Stripe |
| **Billing Portal** | Portal de autoatendimento do cliente no Stripe |
| **Webhook** | Notificação HTTP enviada pelo Stripe quando eventos ocorrem |
| **Grace Period** | Período após cancelamento em que usuário mantém acesso |
| **MRR** | Monthly Recurring Revenue - receita recorrente mensal |
| **Churn** | Taxa de cancelamento de assinaturas |
| **Price ID** | Identificador único de um preço no Stripe (ex: price_xxx) |
| **Product ID** | Identificador único de um produto no Stripe (ex: prod_xxx) |

---

## Anexo B: Referências

- [Laravel 8 Documentation](https://laravel.com/docs/8.x)
- [Laravel Cashier Stripe](https://laravel.com/docs/8.x/billing)
- [Stripe API Reference](https://stripe.com/docs/api)
- [Stripe Checkout](https://stripe.com/docs/payments/checkout)
- [Stripe Billing Portal](https://stripe.com/docs/billing/subscriptions/customer-portal)
- [Stripe Webhooks](https://stripe.com/docs/webhooks)
- [Filament 2.x Documentation](https://filamentphp.com/docs/2.x)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v5)

---

**Documento criado para servir de base ao ASSINATURA_SPECS.md**

---

## Anexo C: Changelog de Revisão

### Revisão 1
**Data:** 18 de Janeiro de 2026  
**Revisado por:** Análise técnica de desenvolvedor externo (primeira revisão)

| # | Problema Identificado | Correção Aplicada |
|---|----------------------|-------------------|
| 1 | Contradição: 1 produto vs produtos por tier | Corrigido para 1 produto POR TIER (PRO e PREMIUM separados) |
| 2 | Mapeamento de features por product_id | Funciona corretamente com a correção #1 |
| 3 | Risco: checkout/{priceId} sem validação | Adicionada validação obrigatória via allowlist no StripeService |
| 4 | Cupom via URL especificação frágil | Removido. Usar Promotion Codes nativos do Stripe Checkout |
| 5 | Race condition: sucesso vs webhook | Adicionado tratamento de "processando" na página de sucesso |
| 6 | Eventos: deleted vs update para cancelamento | Corrigido: update (agendado) e deleted (encerrado) separados |
| 7 | Renewing sem data de renovação | Adicionada coluna `current_period_end` na tabela subscriptions |
| 8 | hasFeature() não null-safe | Corrigido com verificações de null |
| 9 | subscription_id ambíguo em refund_requests | Renomeado para `stripe_subscription_id` + FK `cashier_subscription_id` |
| 10 | Role subscriber pode dessincronizar | Removido. Usar exclusivamente `$user->subscribed()` do Cashier |
| 11 | Política de emails não clara | Clarificada: Stripe envia recibos, nós enviamos boas-vindas e ações |
| 12 | Comando Filament `--panels` incorreto | Corrigido para comando do Filament 2.x |
| 13 | Método show() não listado | Adicionado à lista do SubscriptionController |
| 14 | Falta checkout.session.completed | Adicionado ao webhook + client_reference_id para vinculação |
| 15 | Strings hardcoded vs princípio | Adicionada nota sobre constantes/enums (melhoria progressiva) |
| 16 | Métricas MRR/churn sem definição | Simplificado: métricas financeiras ficam no Stripe Dashboard |

### Revisão 2
**Data:** 18 de Janeiro de 2026  
**Revisado por:** Análise técnica de desenvolvedor externo (segunda revisão)

| # | Problema Identificado | Correção Aplicada |
|---|----------------------|-------------------|
| 17 | plan_features parece redundância | Clarificado: é autorização interna, não duplicação do Stripe |
| 18 | syncPlanFeatures() não existe no Stripe | Removido. Features são 100% manuais via Filament |
| 19 | Falta índice UNIQUE em plan_features | Adicionado UNIQUE(stripe_product_id, feature_key) |
| 20 | hasFeature() não usa getSubscriptionSource() | Padronizado para usar getSubscriptionSource() |
| 21 | current_period_end sub-especificado | Especificados todos os eventos que atualizam + filtros do job |
| 22 | Falta estratégia anti-duplicação no checkout | Adicionadas regras de verificação antes de criar sessão |
| 23 | Rota GET para checkout é arriscada | Alterado para POST |
| 24 | Falta idempotência/filas para webhooks | Adicionada seção 15.1.1 com tabela e código de exemplo |
| 25 | PaymentFailedNotification conflita com Stripe | Removido do MVP. Stripe já envia via Smart Retries |
| 26 | Polling na página de sucesso pode confirmar grace period | Especificado uso de session_id para confirmação específica |
| 27 | "Always invoice immediately" agressivo para downgrade | Alterado para "Create prorations" + documentação |
| 28 | Estornos sem identificadores de pagamento | Adicionados stripe_invoice_id e stripe_payment_intent_id |
| 29 | Constantes/enums marcados como futuro | Movidos para Fase 1 (MVP) |
| 30 | Activity log não é automático no Filament | Removido do MVP, nota sobre implementação futura |

### Revisão 3
**Data:** 18 de Janeiro de 2026  
**Revisado por:** Análise técnica de desenvolvedor externo (terceira revisão)

| # | Problema Identificado | Correção Aplicada |
|---|----------------------|-------------------|
| 31 | Regressão: texto ainda sugeria $user->subscribed() na página de sucesso | Removida alternativa, mantido apenas validação por session_id |
| 32 | PlanFeatureResource dizia "sincronização" mas features são manuais | Alterado para "seleção/validação" + dropdown de produtos + alerta de órfãos |
| 33 | Tabela stripe_webhook_events não estava na seção 3.1 | Adicionada seção 3.1.6 com estrutura completa |
| 34 | Idempotência marcava evento cedo demais (podia perder eventos) | Reescrito: marca received_at, só processed_at após sucesso, registra falhas |
| 35 | Race condition no exists() + create() | Alterado para firstOrCreate atômico + wasRecentlyCreated |
| 36 | Exemplo de webhook sem contexto de validação Stripe | Adicionado pré-requisito explícito sobre validação via Cashier |
| 37 | feature_value sem semântica documentada | Adicionada nota: MVP ignora valor (booleano), futuro usa para quotas |
| 38 | items()->first() frágil para múltiplos itens | Adicionado critério via config('subscription.tier_product_ids') |
| 39 | priceId na URL facilita replay/enumeração | Movido priceId para body do POST |
| 40 | Política de emails ainda ambígua sobre falha de pagamento | Adicionada tabela objetiva: MVP não envia email de falha, apenas banner in-app |

### Revisão 4
**Data:** 18 de Janeiro de 2026  
**Revisado por:** Análise técnica de desenvolvedor externo (quarta revisão)

| # | Problema Identificado | Correção Aplicada |
|---|----------------------|-------------------|
| 41 | checkout() usava $user->subscribed() direto, violando padrão | Padronizado para usar getSubscriptionSource() como em hasFeature() |
| 42 | Rota webhook sem middleware explícito documentado | Adicionada explicação sobre estender CashierController + middleware |
| 43 | stripe_webhook_events sem session_id para polling | Adicionados campos stripe_object_id e user_id para suportar polling |
| 44 | Job de renovação vs trialing/past_due não especificado | Documentada regra MVP: apenas 'active', futuro inclui 'trialing' |
| 45 | Config com defaults fake (prod_xxx_*) perigosa | Removidos defaults, validação no boot com Log::critical e exception |
| 46 | hasFeature() com fallback first() mascara erros | Removido fallback, retorna false + log de erro se config ausente |
| 47 | PaymentFailedListener propósito implícito | Esclarecido: não envia email no MVP, apenas atualiza estado/banner |
| 48 | Rotas sucesso/cancelado dentro de auth pode perder sessão | Movidas para fora de auth, validação via session_id documentada |
| 49 | WebhookController sem extração de stripe_object_id | Adicionado método extractObjectId() para popular campo na tabela |

---

*Fim do documento*
