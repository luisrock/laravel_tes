# Especificações Técnicas: Sistema de Assinaturas

**Documento:** ASSINATURA_SPECS.md  
**Versão:** 1.1  
**Baseado em:** ASSINATURA_PLAN.md v1.4  
**Criado em:** 18 de Janeiro de 2026  
**Última Atualização:** 18 de Janeiro de 2026  
**Projeto:** Teses e Súmulas (https://tesesesumulas.com.br/)  

---

## 🚀 STATUS DA IMPLEMENTAÇÃO

> **Esta seção é atualizada a cada progresso para que futuros assistentes de IA possam retomar o trabalho.**

### Fases Concluídas

| Fase | Status | Data | Observações |
|------|--------|------|-------------|
| 1 - Infraestrutura Base | ✅ Concluída | 18/01/2026 | Cashier 13.17, migrations, models |
| 2 - User Model + Services | ✅ Concluída | 18/01/2026 | Billable, StripeService, SubscriptionService |
| 3 - Rotas e Controllers | ✅ Concluída | 18/01/2026 | 3 controllers, 10 rotas, webhook |
| 4 - Middlewares | ✅ Concluída | 18/01/2026 | subscribed, feature:xxx |
| 5 - Views Assinatura | ✅ Concluída | 18/01/2026 | 5 views minimalistas |
| 5b - UI Global | ✅ Concluída | 18/01/2026 | Header/Footer novos, layout unificado |
| 6 - Seed Features | ✅ Concluída | 19/01/2026 | Seeder `no_ads` aplicado em PROD (PRO/PREMIUM) |
| 7 - Notificações + Job de Renovação | ✅ Concluída | 19/01/2026 | Emails transacionais + job 7 dias antes |
| 10 - Filament (Admin de Assinaturas) | ✅ Concluída | 19/02/2026 | Refatoração para v4. Instalação + resources + widgets |

### Atualizações Recentes (19/01/2026)
- ✅ **Guard suave**: middleware `subscription.configured` bloqueia apenas rotas de assinatura quando faltar config (sem derrubar o site).
- ✅ **Webhook Stripe protegido**: rota usa `stripe.webhook` (VerifyWebhookSignature).
- ✅ **Webhook fix**: `invoice.payment_succeeded` agora retorna 200 (não chama método inexistente no Cashier).
- ✅ **UI ajuste**: link "Solicitar estorno" ficou discreto na página "Minha Assinatura".
- ✅ **Script de teste** criado: `scripts/test-subscription-flow.sh`.
- ✅ **Bateria de testes** criada: `scripts/run-subscription-tests.sh` (PHPUnit + E2E opcional).
- ✅ **Fluxo testado end‑to‑end** com Stripe CLI + checkout real (modo test).
- ✅ **Fase 6 concluída**: `PlanFeaturesSeeder` criado e executado em produção (feature `no_ads` para PRO/PREMIUM).
- ✅ **Histórico sanitizado**: removido `STRIPE_WEBHOOK_SECRET` do histórico do repositório.
- ✅ **Filament instalado (2.x)**: painel admin em `/painel` com acesso via `FilamentUser`.
- ✅ **Acesso restringido**: somente emails em `tes_constants.admins` (produção).
- ✅ **Resources Filament**: Users (read-only), Estornos (edit status/notes), PlanFeatures (CRUD).
- ✅ **Widgets Filament**: métricas, últimas assinaturas, estornos pendentes, últimos cancelamentos.
- ✅ **Filtro por plano**: PRO/PREMIUM com base nos `STRIPE_PRODUCT_*`.

### UI Global Implementada (Fase 5b)

```
Partials criados em resources/views/partials/:
├── header.blade.php          # Header responsivo com navegação
├── footer.blade.php          # Footer dark com colunas de links
└── header-footer-styles.blade.php  # CSS compartilhado

Características:
- Design minimalista full-width
- Linha accent gradiente azul-roxo no topo
- Logo "T&S" como ícone
- Navegação: Pesquisar, Temas, Atualizações, Extensão Chrome
- Responsivo com menu mobile hamburger
- Footer dark com 3 colunas: Navegação, Recursos, Conta
- Botão "Assinar" temporariamente escondido (comentado)

Layouts atualizados:
- front/base.blade.php: Inclui partials globalmente
- layouts/app.blade.php: Login/registro com mesmo layout
```

### Próximos Passos
- **Fase 8 (Otimizações)**: revisar cache de planos e métricas adicionais (se necessário).
- **Fase 9 (Qualidade)**: rodar checklist UI + testes completos no final.

### Arquivos Criados/Modificados

```
CRIADOS:
├── config/subscription.php
├── config/filament.php
├── app/Filament/Resources/UserResource.php
├── app/Filament/Resources/UserResource/Pages/ListUsers.php
├── app/Filament/Resources/RefundRequestResource.php
├── app/Filament/Resources/RefundRequestResource/Pages/ListRefundRequests.php
├── app/Filament/Resources/RefundRequestResource/Pages/EditRefundRequest.php
├── app/Filament/Resources/PlanFeatureResource.php
├── app/Filament/Resources/PlanFeatureResource/Pages/ListPlanFeatures.php
├── app/Filament/Resources/PlanFeatureResource/Pages/CreatePlanFeature.php
├── app/Filament/Resources/PlanFeatureResource/Pages/EditPlanFeature.php
├── app/Filament/Widgets/SubscriptionStats.php
├── app/Filament/Widgets/RecentSubscriptions.php
├── app/Filament/Widgets/PendingRefundRequests.php
├── app/Filament/Widgets/RecentCancellations.php
├── app/Models/PlanFeature.php
├── app/Models/RefundRequest.php
├── app/Models/StripeWebhookEvent.php
├── app/Services/StripeService.php
├── app/Services/SubscriptionService.php
├── app/Http/Controllers/SubscriptionController.php
├── app/Http/Controllers/WebhookController.php
├── app/Http/Controllers/RefundRequestController.php
├── app/Http/Middleware/EnsureUserIsSubscribed.php
├── app/Http/Middleware/EnsureUserHasFeature.php
├── database/seeders/PlanFeaturesSeeder.php
└── database/migrations/
    ├── 2026_01_18_000001_add_current_period_end_to_subscriptions.php
    ├── 2026_01_18_000002_create_plan_features_table.php
    ├── 2026_01_18_000003_create_refund_requests_table.php
    └── 2026_01_18_000004_create_stripe_webhook_events_table.php

MODIFICADOS:
├── .env (adicionadas variáveis Stripe TEST)
├── composer.json (adicionado laravel/cashier + filament)
├── composer.lock (deps Filament/Livewire)
├── app/Models/User.php (Billable + métodos)
├── app/Providers/AppServiceProvider.php (singletons + validação)
├── app/Http/Kernel.php (middlewares subscribed, feature)
├── app/Http/Middleware/VerifyCsrfToken.php (exceção stripe/webhook)
├── database/seeders/DatabaseSeeder.php (chama PlanFeaturesSeeder)
└── routes/web.php (10 rotas de assinatura)
```

### Rotas de Assinatura Implementadas

| Método | URI | Nome | Descrição |
|--------|-----|------|-----------|
| GET | `/assinar` | subscription.plans | Página de planos |
| POST | `/assinar/checkout` | subscription.checkout | Inicia checkout |
| GET | `/assinar/sucesso` | subscription.success | Callback sucesso |
| GET | `/assinar/cancelado` | subscription.cancel | Callback cancelamento |
| GET | `/assinar/status` | subscription.check-status | AJAX verificação |
| POST | `/stripe/webhook` | cashier.webhook | Webhook Stripe |
| GET | `/minha-conta/assinatura` | subscription.show | Status assinatura |
| GET | `/minha-conta/assinatura/portal` | subscription.portal | Billing Portal |
| GET | `/minha-conta/estorno` | refund.create | Form estorno |
| POST | `/minha-conta/estorno` | refund.store | Salvar estorno |

### Configuração Stripe (Modo TEST)

```
Produtos configurados:
- PRO: prod_ToerHJyGZMYe7B
  - Mensal: price_1Sr1XhAabNZCbwvi9aJ6xbPW (R$ 29,90)
  - Anual: price_1Sr1XhAabNZCbwvibpXbQtgH (R$ 99,90)
- PREMIUM: prod_Toeskw6vqELPlc
  - Mensal: price_1Sr1YqAabNZCbwvivbTXSsBp (R$ 49,90)
  - Anual: price_1Sr1YqAabNZCbwviniLkzpH5 (R$ 499,90)
```

### Próxima Etapa: Fase 5 - Views

Para continuar a implementação:
1. Criar views em `resources/views/subscription/`
   - `plans.blade.php` - Página de planos
   - `success.blade.php` - Sucesso após checkout
   - `cancel.blade.php` - Usuário desistiu
   - `show.blade.php` - Status da assinatura
   - `refund.blade.php` - Formulário de estorno
2. Testar fluxo visual no navegador

> **Nota:** As views já foram concluídas. O próximo passo real é **Fase 6 (Seed Features)**.

---

## ✅ Testes Realizados (19/01/2026)

### Resultado
- Checkout completo em modo **test** com cartão de teste.
- Webhooks recebidos e processados com sucesso.
- Página de sucesso confirmou apenas após webhook (`checkout.session.completed`).
- "Minha Assinatura" exibiu status ativo e link de estorno discreto.

### Checklist de Reteste (rápido)
- [ ] `stripe listen` rodando e `STRIPE_WEBHOOK_SECRET` atualizado no `.env`
- [ ] `php artisan config:clear`
- [ ] Login com usuário de teste
- [ ] Checkout concluído (cartão teste `4242 4242 4242 4242`)
- [ ] `/assinar/sucesso?session_id=...` confirma após webhook
- [ ] `/minha-conta/assinatura` mostra status correto
- [ ] Cancelar assinatura de teste (opcional para repetir o fluxo)

### Como testar (manual)
1. Rodar Stripe CLI:
   ```bash
   stripe login
   stripe listen --forward-to https://teses.test/stripe/webhook
   ```
2. Configurar `STRIPE_WEBHOOK_SECRET` no `.env` e rodar:
   ```bash
   php artisan config:clear
   ```
3. Criar usuário de teste (se necessário) e fazer login.
4. Acessar `/assinar`, selecionar plano e concluir pagamento com cartão teste `4242 4242 4242 4242`.
5. Verificar `/assinar/sucesso?session_id=cs_test_...` e `/minha-conta/assinatura`.

### Script automatizado
Arquivos:
- `scripts/test-subscription-flow.sh` (E2E manual com Stripe CLI)
- `scripts/run-subscription-tests.sh` (bateria rápida PHPUnit + E2E opcional)

Uso básico (PHPUnit de assinatura):
```bash
./scripts/run-subscription-tests.sh
```

Para incluir o E2E com Stripe CLI:
```bash
RUN_E2E=1 ./scripts/run-subscription-tests.sh
```

Uso direto do E2E (já existente):
```bash
./scripts/test-subscription-flow.sh
```

Após concluir o checkout, rodar:
```bash
SESSION_ID=cs_test_xxx ./scripts/test-subscription-flow.sh
```

Para cancelar a assinatura de teste:
```bash
CANCEL_SUBSCRIPTION=1 ./scripts/test-subscription-flow.sh
```

### Testes finais (ao final do projeto)
- Rodar a bateria completa com E2E no ambiente local (`RUN_E2E=1 ./scripts/run-subscription-tests.sh`).
- Checklist UI manual: `scripts/subscription-ui-checklist.md`.
- **PRODUÇÃO:** Nunca rodar `php artisan test` nem executar `./scripts/run-subscription-tests.sh` no servidor remoto (Vito), pois configurações e chaves diferem, além de gerar lixo no banco de dados. Testes automatizados são restritos ao ambiente de desenvolvimento.
- **PRODUÇÃO:** O arquivo `scripts/send-subscription-test-emails.sh` pode ser usado pois é seguro (apenas testa envio de e-mails SES).

---

## 📦 INSTRUÇÕES PARA DEPLOY EM PRODUÇÃO

### Comandos Pré-Deploy (já incluídos no script Vito Deploy)

O script de deploy já executa `composer install` e `php artisan migrate`, então as novas dependências e tabelas serão criadas automaticamente.

### Configuração Manual Necessária no .env de Produção

Adicionar as seguintes variáveis ao `.env` do servidor de produção:

```env
# Stripe (modo LIVE - usar chaves de produção!)
STRIPE_KEY=pk_live_51MmeSnAabNZCbwvi...
STRIPE_SECRET=sk_live_51MmeSnAabNZCbwvi...
STRIPE_WEBHOOK_SECRET=whsec_xxx

# Produtos Stripe (PRODUÇÃO)
STRIPE_PRODUCT_PRO=prod_ToedwoFT9ZWdne
STRIPE_PRODUCT_PREMIUM=prod_Toeh7EqG1BrdI7

# Cashier
CASHIER_CURRENCY=brl
CASHIER_CURRENCY_LOCALE=pt_BR
```

### Configurar Webhook no Stripe Dashboard (Produção)

1. Acesse Stripe Dashboard → Developers → Webhooks
2. Clique em "Add endpoint"
3. URL: `https://tesesesumulas.com.br/stripe/webhook`
4. Eventos a selecionar:
   - `checkout.session.completed`
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_succeeded`
   - `invoice.payment_failed`
5. Copie o "Signing secret" e adicione ao `.env` como `STRIPE_WEBHOOK_SECRET`

### Verificação Pós-Deploy

```bash
# Via SSH no servidor
ssh vito@15.229.244.115

# Verificar se migrations rodaram
cd /home/vito/tesesesumulas.com.br
php artisan migrate:status | grep 2026_01_18

# Verificar se config está carregada
php artisan tinker --execute="echo config('subscription.default_subscription_name');"
# Deve retornar: default

# Limpar cache se necessário
php artisan config:clear
php artisan cache:clear
```

---

## Sumário Executivo

Este documento detalha a implementação **passo a passo** do sistema de assinaturas, projetado para:

1. **Execução incremental** - Cada passo é independente e testável
2. **Reversibilidade** - Commits frequentes permitem rollback
3. **Preservação** - Nenhuma funcionalidade existente é afetada
4. **Validação** - Testes manuais entre cada fase

---

## Análise do Estado Atual

### Arquivos Chave Existentes

| Arquivo | Estado Atual | Ação Necessária |
|---------|--------------|-----------------|
| `app/Models/User.php` | Simples, usa HasRoles | Adicionar Billable + métodos |
| `routes/web.php` | `register => false` | Habilitar registro + novas rotas |
| `app/Http/Kernel.php` | Middlewares: admin_access, bearer.token | Adicionar novos middlewares |
| `app/Http/Middleware/VerifyCsrfToken.php` | Sem exceções | Adicionar `/stripe/webhook` |
| `resources/views/front/base.blade.php` | AdSense inline (linhas 93-100) | Adicionar lógica condicional |
| `resources/views/layouts/app.blade.php` | Layout auth separado | Avaliar migração para front.base |
| `composer.json` | Sem Cashier/Filament | Instalar dependências |

### Dependências a Instalar

```
laravel/cashier:^13.0 (compatível Laravel 8)
filament/filament:^2.0 (compatível Laravel 8)
```

---

## Fases de Implementação

```
┌─────────────────────────────────────────────────────────────────────┐
│  FASE 1: Infraestrutura Base (sem impacto em produção)             │
│  ├── 1.1 Instalar Cashier                                          │
│  ├── 1.2 Configurar ambiente (.env)                                │
│  ├── 1.3 Migrations customizadas                                   │
│  ├── 1.4 Models novos                                              │
│  └── 1.5 Constantes/Enums                                          │
│  🔒 COMMIT: "feat: infraestrutura base assinaturas"                │
├─────────────────────────────────────────────────────────────────────┤
│  FASE 2: User Model + Services (sem impacto em produção)           │
│  ├── 2.1 Adicionar Billable ao User                                │
│  ├── 2.2 Implementar métodos de verificação                        │
│  ├── 2.3 Criar StripeService                                       │
│  └── 2.4 Criar SubscriptionService                                 │
│  🔒 COMMIT: "feat: user model e services para assinaturas"         │
├─────────────────────────────────────────────────────────────────────┤
│  FASE 3: Rotas e Controllers (sem impacto em produção)             │
│  ├── 3.1 Criar SubscriptionController                              │
│  ├── 3.2 Criar WebhookController                                   │
│  ├── 3.3 Criar RefundRequestController                             │
│  ├── 3.4 Adicionar rotas ao web.php                                │
│  └── 3.5 Configurar CSRF exception                                 │
│  🔒 COMMIT: "feat: controllers e rotas de assinatura"              │
├─────────────────────────────────────────────────────────────────────┤
│  FASE 4: Middlewares (sem impacto em produção)                     │
│  ├── 4.1 Criar EnsureUserIsSubscribed                              │
│  ├── 4.2 Criar EnsureUserHasFeature                                │
│  └── 4.3 Registrar no Kernel                                       │
│  🔒 COMMIT: "feat: middlewares de assinatura"                      │
├─────────────────────────────────────────────────────────────────────┤
│  FASE 5: Views de Assinatura (sem impacto em produção)             │
│  ├── 5.1 Página de planos (/assinar)                               │
│  ├── 5.2 Página de sucesso                                         │
│  ├── 5.3 Página de cancelamento                                    │
│  ├── 5.4 Página de status da assinatura                            │
│  └── 5.5 Formulário de estorno                                     │
│  🔒 COMMIT: "feat: views do sistema de assinaturas"                │
├─────────────────────────────────────────────────────────────────────┤
│  FASE 6: Webhooks e Eventos (sem impacto em produção)              │
│  ├── 6.1 Criar Listeners                                           │
│  ├── 6.2 Registrar eventos                                         │
│  └── 6.3 Testar com Stripe CLI                                     │
│  🔒 COMMIT: "feat: webhooks e eventos stripe"                      │
├─────────────────────────────────────────────────────────────────────┤
│  FASE 7: Notificações (sem impacto em produção)                    │
│  ├── 7.1 WelcomeSubscriberNotification                             │
│  ├── 7.2 SubscriptionRenewingSoonNotification                      │
│  ├── 7.3 SubscriptionCanceledNotification                          │
│  ├── 7.4 RefundRequestReceivedNotification                         │
│  └── 7.5 Job de lembrete de renovação                              │
│  🔒 COMMIT: "feat: notificações de assinatura"                     │
├─────────────────────────────────────────────────────────────────────┤
│  FASE 8: Habilitar Registro de Usuários                            │
│  ├── 8.1 Alterar 'register' => true                                │
│  ├── 8.2 Adaptar views de auth (opcional)                          │
│  └── 8.3 Testar fluxo de registro                                  │
│  🔒 COMMIT: "feat: habilitar registro público"                     │
├─────────────────────────────────────────────────────────────────────┤
│  FASE 9: Integração de Ads (impacto controlado)                    │
│  ├── 9.1 Criar partial de ads                                      │
│  ├── 9.2 Modificar base.blade.php para usar shouldSeeAds()         │
│  └── 9.3 Testar exibição condicional                               │
│  🔒 COMMIT: "feat: ads condicionais para assinantes"               │
├─────────────────────────────────────────────────────────────────────┤
│  FASE 10: Admin Filament (sem impacto em produção)                 │
│  ├── 10.1 Instalar Filament 2.x                                    │
│  ├── 10.2 Configurar rota /painel                                  │
│  ├── 10.3 Criar UserResource                                       │
│  ├── 10.4 Criar RefundRequestResource                              │
│  ├── 10.5 Criar PlanFeatureResource                                │
│  └── 10.6 Dashboard widgets                                        │
│  🔒 COMMIT: "feat: painel admin filament"                          │
└─────────────────────────────────────────────────────────────────────┘
```

---

# FASE 1: Infraestrutura Base

## Objetivo
Instalar dependências e criar estrutura de banco de dados sem afetar funcionalidades existentes.

## Pré-requisitos
- [ ] Conta Stripe Brasil criada
- [ ] Chaves de API do Stripe (modo test)
- [ ] Produtos e preços criados no Stripe Dashboard
- [ ] Webhook endpoint configurado no Stripe

---

## Passo 1.1: Instalar Laravel Cashier

### Comando
```bash
composer require laravel/cashier:^13.0
```

### Verificação
```bash
composer show laravel/cashier
# Deve mostrar versão 13.x
```

### Critério de Sucesso
- [ ] Comando executou sem erros
- [ ] `composer.json` contém `"laravel/cashier": "^13.0"`
- [ ] Site continua funcionando normalmente

---

## Passo 1.2: Publicar e Rodar Migrations do Cashier

### Comandos
```bash
php artisan vendor:publish --tag="cashier-migrations"
php artisan migrate
```

### Tabelas Criadas
- `subscriptions`
- `subscription_items`

### Colunas Adicionadas em `users`
- `stripe_id`
- `pm_type`
- `pm_last_four`
- `trial_ends_at`

### Verificação
```bash
php artisan tinker
>>> Schema::hasColumn('users', 'stripe_id')
# Deve retornar true
```

### Critério de Sucesso
- [ ] Migrations executaram sem erros
- [ ] Tabela `users` tem colunas do Cashier
- [ ] Tabelas `subscriptions` e `subscription_items` existem
- [ ] Site continua funcionando normalmente

---

## Passo 1.3: Configurar Variáveis de Ambiente

### Arquivo: `.env`

```env
# Stripe (usar chaves de TEST primeiro)
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx

# Produtos Stripe (criar no Dashboard primeiro)
STRIPE_PRODUCT_PRO=prod_xxx
STRIPE_PRODUCT_PREMIUM=prod_xxx

# Cashier
CASHIER_CURRENCY=brl
CASHIER_CURRENCY_LOCALE=pt_BR
```

### Verificação
```bash
php artisan tinker
>>> config('cashier.currency')
# Deve retornar "brl"
```

### Critério de Sucesso
- [ ] Variáveis configuradas
- [ ] Tinker retorna valores corretos
- [ ] Site continua funcionando

---

## Passo 1.4: Criar Config Subscription

### Arquivo: `config/subscription.php`

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe Product IDs para Tiers de Assinatura
    |--------------------------------------------------------------------------
    |
    | IDs dos produtos que representam tiers de assinatura.
    | Usado em hasFeature() para identificar o item correto da subscription.
    | OBRIGATÓRIO: Definir em .env
    |
    */
    'tier_product_ids' => array_filter([
        env('STRIPE_PRODUCT_PRO'),
        env('STRIPE_PRODUCT_PREMIUM'),
    ]),

    /*
    |--------------------------------------------------------------------------
    | Feature Keys
    |--------------------------------------------------------------------------
    |
    | Constantes para evitar typos em verificações de features.
    |
    */
    'features' => [
        'no_ads' => 'no_ads',
        'exclusive_content' => 'exclusive_content',
        'ai_tools' => 'ai_tools', // futuro
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Names
    |--------------------------------------------------------------------------
    |
    | Nome padrão da subscription no Cashier.
    |
    */
    'default_subscription_name' => 'default',
];
```

### Verificação
```bash
php artisan config:cache
php artisan config:clear
php artisan tinker
>>> config('subscription.features.no_ads')
# Deve retornar "no_ads"
```

### Critério de Sucesso
- [ ] Arquivo criado
- [ ] Config acessível via helper
- [ ] Site continua funcionando

---

## Passo 1.5: Migration Customizada - current_period_end

### Arquivo: `database/migrations/2026_01_18_000001_add_current_period_end_to_subscriptions.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrentPeriodEndToSubscriptions extends Migration
{
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->timestamp('current_period_end')->nullable()->after('ends_at');
        });
    }

    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('current_period_end');
        });
    }
}
```

### Comando
```bash
php artisan migrate
```

### Critério de Sucesso
- [ ] Migration executou sem erros
- [ ] Coluna `current_period_end` existe em `subscriptions`

---

## Passo 1.6: Migration - Tabela plan_features

### Arquivo: `database/migrations/2026_01_18_000002_create_plan_features_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanFeaturesTable extends Migration
{
    public function up()
    {
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_product_id');
            $table->string('feature_key');
            $table->text('feature_value')->nullable();
            $table->timestamps();

            // Constraint para evitar duplicações
            $table->unique(['stripe_product_id', 'feature_key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('plan_features');
    }
}
```

### Comando
```bash
php artisan migrate
```

### Critério de Sucesso
- [ ] Tabela `plan_features` criada
- [ ] Constraint unique existe

---

## Passo 1.7: Migration - Tabela refund_requests

### Arquivo: `database/migrations/2026_01_18_000003_create_refund_requests_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefundRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('cashier_subscription_id')->nullable();
            $table->string('stripe_subscription_id');
            $table->string('stripe_invoice_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'processed'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->foreign('cashier_subscription_id')
                  ->references('id')
                  ->on('subscriptions')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('refund_requests');
    }
}
```

### Comando
```bash
php artisan migrate
```

### Critério de Sucesso
- [ ] Tabela `refund_requests` criada
- [ ] Foreign keys funcionando

---

## Passo 1.8: Migration - Tabela stripe_webhook_events

### Arquivo: `database/migrations/2026_01_18_000004_create_stripe_webhook_events_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStripeWebhookEventsTable extends Migration
{
    public function up()
    {
        Schema::create('stripe_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_event_id')->unique();
            $table->string('event_type');
            $table->string('stripe_object_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->integer('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stripe_webhook_events');
    }
}
```

### Comando
```bash
php artisan migrate
```

### Critério de Sucesso
- [ ] Tabela `stripe_webhook_events` criada
- [ ] Índices criados corretamente

---

## Passo 1.9: Criar Models

### Arquivo: `app/Models/PlanFeature.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_product_id',
        'feature_key',
        'feature_value',
    ];

    /**
     * Scope para buscar features de um produto específico.
     */
    public function scopeForProduct($query, string $productId)
    {
        return $query->where('stripe_product_id', $productId);
    }

    /**
     * Verifica se um produto tem uma feature específica.
     */
    public static function productHasFeature(string $productId, string $featureKey): bool
    {
        return static::where('stripe_product_id', $productId)
            ->where('feature_key', $featureKey)
            ->exists();
    }
}
```

### Arquivo: `app/Models/RefundRequest.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cashier_subscription_id',
        'stripe_subscription_id',
        'stripe_invoice_id',
        'stripe_payment_intent_id',
        'reason',
        'status',
        'admin_notes',
    ];

    /**
     * Status possíveis para solicitações de estorno.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PROCESSED = 'processed';

    /**
     * Relacionamento com usuário.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com subscription do Cashier.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(\Laravel\Cashier\Subscription::class, 'cashier_subscription_id');
    }

    /**
     * Scope para solicitações pendentes.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
```

### Arquivo: `app/Models/StripeWebhookEvent.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripeWebhookEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_event_id',
        'event_type',
        'stripe_object_id',
        'user_id',
        'received_at',
        'processed_at',
        'failed_at',
        'attempts',
        'last_error',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Relacionamento com usuário.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se o evento já foi processado com sucesso.
     */
    public function isProcessed(): bool
    {
        return $this->processed_at !== null;
    }

    /**
     * Verifica se uma checkout session foi processada.
     */
    public static function checkoutSessionProcessed(string $sessionId): bool
    {
        return static::where('stripe_object_id', $sessionId)
            ->where('event_type', 'checkout.session.completed')
            ->whereNotNull('processed_at')
            ->exists();
    }
}
```

### Verificação
```bash
php artisan tinker
>>> new \App\Models\PlanFeature();
>>> new \App\Models\RefundRequest();
>>> new \App\Models\StripeWebhookEvent();
# Não deve dar erro
```

### Critério de Sucesso
- [ ] Todos os models criados
- [ ] Tinker instancia sem erros
- [ ] Site continua funcionando

---

## Passo 1.10: Validação de Configuração no AppServiceProvider

### Arquivo: `app/Providers/AppServiceProvider.php`

Adicionar no método `boot()`:

```php
public function boot()
{
    // ... código existente ...

    // Validar configuração crítica de assinaturas
    if (app()->environment('production')) {
        $tierProductIds = config('subscription.tier_product_ids', []);
        
        if (empty($tierProductIds)) {
            \Log::critical('STRIPE_PRODUCT_PRO e STRIPE_PRODUCT_PREMIUM não configurados!');
            // Em produção, podemos optar por lançar exception ou apenas logar
            // throw new \RuntimeException('Configuração de produtos Stripe ausente');
        }
    }
}
```

### Critério de Sucesso
- [ ] Código adicionado sem erros
- [ ] Site continua funcionando
- [ ] Log não mostra critical em dev (produtos podem não estar configurados ainda)

---

## 🔒 COMMIT FASE 1

```bash
git add .
git commit -m "feat: infraestrutura base para sistema de assinaturas

- Instalado Laravel Cashier ^13.0
- Criado config/subscription.php
- Migrations: current_period_end, plan_features, refund_requests, stripe_webhook_events
- Models: PlanFeature, RefundRequest, StripeWebhookEvent
- Validação de config no AppServiceProvider

Ref: ASSINATURA_PLAN.md v1.4 - Fase 1"
```

### Checklist Final Fase 1
- [ ] Cashier instalado
- [ ] Todas as migrations executadas
- [ ] Config subscription.php criado
- [ ] Todos os models criados
- [ ] .env configurado (chaves test)
- [ ] Site funciona normalmente
- [ ] Admin existente funciona normalmente
- [ ] Commit realizado

---

# FASE 2: User Model + Services

## Objetivo
Estender o User model com trait Billable e métodos de verificação, criar services para Stripe.

---

## Passo 2.1: Adicionar Billable ao User Model

### Arquivo: `app/Models/User.php`

Substituir conteúdo completo:

```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Billable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Retorna a fonte da assinatura (prepara para assinaturas coletivas futuras).
     * 
     * Hoje: retorna $this (assinatura individual)
     * Futuro: pode retornar Team se usuário faz parte de um
     */
    public function getSubscriptionSource(): ?Model
    {
        // Futuro: verificar se usuário pertence a um Team com assinatura
        // if ($team = $this->currentTeam) {
        //     return $team;
        // }
        
        return $this;
    }

    /**
     * Verifica se usuário é assinante ativo (inclui grace period).
     */
    public function isSubscriber(): bool
    {
        $source = $this->getSubscriptionSource();
        $subscriptionName = config('subscription.default_subscription_name', 'default');
        
        return $source?->subscribed($subscriptionName) ?? false;
    }

    /**
     * Verifica se usuário tem acesso a uma feature específica.
     */
    public function hasFeature(string $featureKey): bool
    {
        $source = $this->getSubscriptionSource();
        $subscriptionName = config('subscription.default_subscription_name', 'default');

        if (!$source || !$source->subscribed($subscriptionName)) {
            return false;
        }

        $subscription = $source->subscription($subscriptionName);
        if (!$subscription) {
            return false;
        }

        // Busca o item do tier (produto que está na nossa lista de tiers)
        $tierProductIds = config('subscription.tier_product_ids', []);

        if (empty($tierProductIds)) {
            Log::error('hasFeature: tier_product_ids não configurado', [
                'user_id' => $this->id,
                'feature_key' => $featureKey,
            ]);
            return false;
        }

        $item = $subscription->items()
            ->whereIn('stripe_product', $tierProductIds)
            ->first();

        if (!$item) {
            Log::warning('hasFeature: subscription sem item de tier válido', [
                'user_id' => $this->id,
                'subscription_id' => $subscription->id,
                'tier_product_ids' => $tierProductIds,
            ]);
            return false;
        }

        return PlanFeature::productHasFeature($item->stripe_product, $featureKey);
    }

    /**
     * Retorna o nome do plano atual do usuário.
     */
    public function getSubscriptionPlan(): ?string
    {
        $source = $this->getSubscriptionSource();
        $subscriptionName = config('subscription.default_subscription_name', 'default');

        if (!$source || !$source->subscribed($subscriptionName)) {
            return null;
        }

        $subscription = $source->subscription($subscriptionName);
        if (!$subscription) {
            return null;
        }

        $tierProductIds = config('subscription.tier_product_ids', []);
        $item = $subscription->items()
            ->whereIn('stripe_product', $tierProductIds)
            ->first();

        return $item?->stripe_product;
    }

    /**
     * Verifica se usuário pode acessar conteúdo exclusivo.
     */
    public function canAccessExclusiveContent(): bool
    {
        return $this->hasFeature(config('subscription.features.exclusive_content', 'exclusive_content'));
    }

    /**
     * Verifica se usuário deve ver anúncios.
     */
    public function shouldSeeAds(): bool
    {
        return !$this->hasFeature(config('subscription.features.no_ads', 'no_ads'));
    }

    /**
     * Verifica se usuário está em grace period (cancelou mas ainda tem acesso).
     */
    public function isOnGracePeriod(): bool
    {
        $source = $this->getSubscriptionSource();
        $subscriptionName = config('subscription.default_subscription_name', 'default');

        $subscription = $source?->subscription($subscriptionName);
        
        return $subscription?->onGracePeriod() ?? false;
    }

    /**
     * Retorna a data de término do acesso (se em grace period).
     */
    public function getAccessEndsAt(): ?\Carbon\Carbon
    {
        $source = $this->getSubscriptionSource();
        $subscriptionName = config('subscription.default_subscription_name', 'default');

        $subscription = $source?->subscription($subscriptionName);
        
        return $subscription?->ends_at;
    }
}
```

### Verificação
```bash
php artisan tinker
>>> $user = \App\Models\User::first();
>>> $user->isSubscriber()
# Deve retornar false (ainda não tem assinatura)
>>> $user->shouldSeeAds()
# Deve retornar true (não tem feature no_ads)
```

### Critério de Sucesso
- [ ] User model atualizado
- [ ] Trait Billable carregado
- [ ] Métodos funcionam corretamente
- [ ] Login/logout funciona normalmente
- [ ] Admin funciona normalmente

---

## Passo 2.2: Criar StripeService

### Arquivo: `app/Services/StripeService.php`

```php
<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Stripe\Price;
use Stripe\Product;
use Stripe\StripeClient;

class StripeService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('cashier.secret'));
    }

    /**
     * Retorna todos os produtos ativos do Stripe.
     */
    public function getActiveProducts(): Collection
    {
        return Cache::remember('stripe_products', 3600, function () {
            $products = $this->stripe->products->all([
                'active' => true,
                'limit' => 100,
            ]);

            return collect($products->data);
        });
    }

    /**
     * Retorna os preços ativos de um produto.
     */
    public function getPricesForProduct(string $productId): Collection
    {
        return Cache::remember("stripe_prices_{$productId}", 3600, function () use ($productId) {
            $prices = $this->stripe->prices->all([
                'product' => $productId,
                'active' => true,
                'limit' => 100,
            ]);

            return collect($prices->data);
        });
    }

    /**
     * Retorna planos formatados para exibição na página de planos.
     * Estrutura: [
     *   'pro' => [
     *     'product' => Product,
     *     'prices' => ['monthly' => Price, 'yearly' => Price]
     *   ],
     *   ...
     * ]
     */
    public function getFormattedPlans(): array
    {
        return Cache::remember('stripe_formatted_plans', 3600, function () {
            $tierProductIds = config('subscription.tier_product_ids', []);
            $plans = [];

            foreach ($tierProductIds as $productId) {
                if (empty($productId)) {
                    continue;
                }

                try {
                    $product = $this->stripe->products->retrieve($productId);
                    $prices = $this->getPricesForProduct($productId);

                    $formattedPrices = [];
                    foreach ($prices as $price) {
                        $interval = $price->recurring?->interval ?? 'one_time';
                        $key = match ($interval) {
                            'month' => 'monthly',
                            'year' => 'yearly',
                            default => $interval,
                        };
                        $formattedPrices[$key] = [
                            'id' => $price->id,
                            'amount' => $price->unit_amount / 100,
                            'currency' => strtoupper($price->currency),
                            'interval' => $interval,
                        ];
                    }

                    // Usa metadata 'tier' ou infere do nome
                    $tier = $product->metadata['tier'] ?? strtolower($product->name);

                    $plans[$tier] = [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'prices' => $formattedPrices,
                    ];
                } catch (\Exception $e) {
                    \Log::error("Erro ao buscar produto Stripe: {$productId}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $plans;
        });
    }

    /**
     * Retorna lista de price IDs válidos para checkout.
     */
    public function getAllowedPriceIds(): array
    {
        return Cache::remember('stripe_allowed_price_ids', 3600, function () {
            $tierProductIds = config('subscription.tier_product_ids', []);
            $allowedIds = [];

            foreach ($tierProductIds as $productId) {
                if (empty($productId)) {
                    continue;
                }

                $prices = $this->getPricesForProduct($productId);
                foreach ($prices as $price) {
                    $allowedIds[] = $price->id;
                }
            }

            return $allowedIds;
        });
    }

    /**
     * Valida se um price ID é válido para checkout.
     */
    public function isValidPriceId(string $priceId): bool
    {
        return in_array($priceId, $this->getAllowedPriceIds());
    }

    /**
     * Limpa cache de planos (útil após alterações no Stripe).
     */
    public function clearCache(): void
    {
        Cache::forget('stripe_products');
        Cache::forget('stripe_formatted_plans');
        Cache::forget('stripe_allowed_price_ids');

        $tierProductIds = config('subscription.tier_product_ids', []);
        foreach ($tierProductIds as $productId) {
            if (!empty($productId)) {
                Cache::forget("stripe_prices_{$productId}");
            }
        }
    }
}
```

### Verificação
```bash
php artisan tinker
>>> $service = new \App\Services\StripeService();
>>> $service->getFormattedPlans()
# Deve retornar array (pode estar vazio se produtos não configurados)
```

### Critério de Sucesso
- [ ] Service criado
- [ ] Métodos funcionam (mesmo retornando vazio)
- [ ] Cache funciona
- [ ] Site continua funcionando

---

## Passo 2.3: Criar SubscriptionService

### Arquivo: `app/Services/SubscriptionService.php`

```php
<?php

namespace App\Services;

use App\Models\PlanFeature;
use App\Models\User;
use Illuminate\Support\Collection;

class SubscriptionService
{
    protected StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Retorna todas as features do usuário baseado em sua assinatura.
     */
    public function getUserFeatures(User $user): array
    {
        $productId = $user->getSubscriptionPlan();
        
        if (!$productId) {
            return [];
        }

        return PlanFeature::forProduct($productId)
            ->pluck('feature_key')
            ->toArray();
    }

    /**
     * Valida integridade entre produtos do Stripe e features configuradas.
     * Retorna produtos órfãos (sem features) ou features órfãs (produto inexistente).
     */
    public function validatePlanFeaturesIntegrity(): array
    {
        $tierProductIds = config('subscription.tier_product_ids', []);
        $issues = [
            'products_without_features' => [],
            'features_with_invalid_product' => [],
        ];

        // Verificar produtos sem features
        foreach ($tierProductIds as $productId) {
            if (empty($productId)) {
                continue;
            }

            $featureCount = PlanFeature::forProduct($productId)->count();
            if ($featureCount === 0) {
                $issues['products_without_features'][] = $productId;
            }
        }

        // Verificar features com produtos inválidos
        $featuresProductIds = PlanFeature::pluck('stripe_product_id')->unique();
        foreach ($featuresProductIds as $productId) {
            if (!in_array($productId, $tierProductIds)) {
                $issues['features_with_invalid_product'][] = $productId;
            }
        }

        return $issues;
    }

    /**
     * Seed de features para um produto (útil para setup inicial).
     */
    public function seedFeaturesForProduct(string $productId, array $featureKeys): void
    {
        foreach ($featureKeys as $key) {
            PlanFeature::firstOrCreate([
                'stripe_product_id' => $productId,
                'feature_key' => $key,
            ]);
        }
    }
}
```

### Verificação
```bash
php artisan tinker
>>> $service = app(\App\Services\SubscriptionService::class);
>>> $service->validatePlanFeaturesIntegrity()
# Deve retornar array com possíveis issues
```

### Critério de Sucesso
- [ ] Service criado
- [ ] Dependency injection funciona
- [ ] Métodos funcionam
- [ ] Site continua funcionando

---

## Passo 2.4: Registrar Services no Container (opcional)

O Laravel resolve automaticamente via auto-wiring, mas para clareza, podemos registrar explicitamente.

### Arquivo: `app/Providers/AppServiceProvider.php`

Adicionar no método `register()`:

```php
public function register()
{
    // ... código existente ...

    $this->app->singleton(\App\Services\StripeService::class);
    $this->app->singleton(\App\Services\SubscriptionService::class);
}
```

### Critério de Sucesso
- [ ] Services são singletons
- [ ] Site continua funcionando

---

## 🔒 COMMIT FASE 2

```bash
git add .
git commit -m "feat: user model com billable e services stripe

- User model: adicionado Billable, getSubscriptionSource, hasFeature, shouldSeeAds
- StripeService: busca produtos/preços com cache
- SubscriptionService: gerenciamento de features
- Preparado para assinaturas coletivas futuras

Ref: ASSINATURA_PLAN.md v1.4 - Fase 2"
```

### Checklist Final Fase 2
- [ ] User model atualizado com Billable
- [ ] Todos os métodos implementados
- [ ] StripeService criado
- [ ] SubscriptionService criado
- [ ] Tinker testa OK
- [ ] Login/logout funciona
- [ ] Admin funciona
- [ ] Commit realizado

---

# FASE 3: Rotas e Controllers

## Objetivo
Criar controllers e rotas para o sistema de assinaturas sem afetar rotas existentes.

---

## Passo 3.1: Criar SubscriptionController

### Arquivo: `app/Http/Controllers/SubscriptionController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\StripeWebhookEvent;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    protected StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Página de planos/preços.
     */
    public function index()
    {
        $plans = $this->stripeService->getFormattedPlans();

        return view('subscription.plans', [
            'plans' => $plans,
        ]);
    }

    /**
     * Inicia checkout via Stripe Checkout.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'priceId' => 'required|string',
        ]);

        $priceId = $request->input('priceId');
        $user = $request->user();

        // Validar price ID contra allowlist
        if (!$this->stripeService->isValidPriceId($priceId)) {
            Log::warning('Tentativa de checkout com priceId inválido', [
                'user_id' => $user->id,
                'price_id' => $priceId,
            ]);
            return back()->with('error', 'Plano inválido selecionado.');
        }

        // Verificar se usuário já tem assinatura ativa
        $source = $user->getSubscriptionSource();
        $subscriptionName = config('subscription.default_subscription_name', 'default');

        if ($source && $source->subscribed($subscriptionName)) {
            // Redirecionar para Billing Portal para upgrade/gerenciamento
            return $this->billingPortal($request);
        }

        // Criar sessão de checkout
        try {
            $checkoutSession = $user->newSubscription($subscriptionName, $priceId)
                ->checkout([
                    'success_url' => route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('subscription.cancel'),
                    'client_reference_id' => (string) $user->id,
                    'allow_promotion_codes' => true,
                ]);

            return redirect($checkoutSession->url);
        } catch (\Exception $e) {
            Log::error('Erro ao criar sessão de checkout', [
                'user_id' => $user->id,
                'price_id' => $priceId,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Erro ao iniciar checkout. Por favor, tente novamente.');
        }
    }

    /**
     * Página de sucesso após checkout.
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect()->route('subscription.plans')
                ->with('error', 'Sessão inválida.');
        }

        // Verificar se o webhook já processou esta sessão
        $isProcessed = StripeWebhookEvent::checkoutSessionProcessed($sessionId);

        return view('subscription.success', [
            'sessionId' => $sessionId,
            'isProcessed' => $isProcessed,
        ]);
    }

    /**
     * Página quando usuário cancela/desiste do checkout.
     */
    public function cancel()
    {
        return view('subscription.cancel');
    }

    /**
     * Página de status da assinatura do usuário.
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $subscriptionName = config('subscription.default_subscription_name', 'default');
        $subscription = $user->subscription($subscriptionName);

        return view('subscription.show', [
            'user' => $user,
            'subscription' => $subscription,
            'isSubscriber' => $user->isSubscriber(),
            'isOnGracePeriod' => $user->isOnGracePeriod(),
            'accessEndsAt' => $user->getAccessEndsAt(),
            'planName' => $user->getSubscriptionPlan(),
        ]);
    }

    /**
     * Redireciona para Stripe Billing Portal.
     */
    public function billingPortal(Request $request)
    {
        $user = $request->user();

        try {
            return $user->redirectToBillingPortal(route('subscription.show'));
        } catch (\Exception $e) {
            Log::error('Erro ao redirecionar para Billing Portal', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Erro ao acessar portal. Por favor, tente novamente.');
        }
    }

    /**
     * Endpoint AJAX para verificar status de processamento do checkout.
     */
    public function checkProcessingStatus(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return response()->json(['status' => 'error', 'message' => 'Session ID required'], 400);
        }

        $isProcessed = StripeWebhookEvent::checkoutSessionProcessed($sessionId);

        return response()->json([
            'status' => $isProcessed ? 'completed' : 'processing',
        ]);
    }
}
```

### Critério de Sucesso
- [ ] Controller criado
- [ ] Sem erros de syntax

---

## Passo 3.2: Criar WebhookController

### Arquivo: `app/Http/Controllers/WebhookController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\StripeWebhookEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Laravel\Cashier\Subscription;

class WebhookController extends CashierWebhookController
{
    /**
     * Handle incoming webhook.
     */
    public function handleWebhook(Request $request)
    {
        $eventId = $request->input('id');
        $eventType = $request->input('type');
        $payload = $request->input('data.object', []);

        // Extrair IDs relevantes do payload
        $stripeObjectId = $this->extractObjectId($eventType, $payload);
        $userId = $this->extractUserId($eventType, $payload);

        // Padrão atômico: firstOrCreate evita race condition
        $webhookEvent = StripeWebhookEvent::firstOrCreate(
            ['stripe_event_id' => $eventId],
            [
                'event_type' => $eventType,
                'stripe_object_id' => $stripeObjectId,
                'user_id' => $userId,
                'received_at' => now(),
            ]
        );

        // Se já foi processado com sucesso, retorna early
        if ($webhookEvent->isProcessed()) {
            return response()->json(['status' => 'already_processed']);
        }

        // Se não foi recém-criado, é reprocessamento
        if (!$webhookEvent->wasRecentlyCreated) {
            $webhookEvent->increment('attempts');
        }

        try {
            // Delegar para o Cashier processar o webhook
            $response = parent::handleWebhook($request);

            // Marcar como processado após sucesso
            $webhookEvent->update([
                'processed_at' => now(),
                'failed_at' => null,
                'last_error' => null,
            ]);

            return $response;
        } catch (\Exception $e) {
            $webhookEvent->update([
                'failed_at' => now(),
                'last_error' => $e->getMessage(),
            ]);

            Log::error('Webhook processing failed', [
                'event_id' => $eventId,
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle checkout.session.completed event.
     */
    protected function handleCheckoutSessionCompleted(array $payload)
    {
        $session = $payload['data']['object'];
        $clientReferenceId = $session['client_reference_id'] ?? null;

        if ($clientReferenceId) {
            // Atualizar o webhook event com o user_id
            StripeWebhookEvent::where('stripe_object_id', $session['id'])
                ->whereNull('user_id')
                ->update(['user_id' => $clientReferenceId]);
        }

        // Delegar para o Cashier
        return parent::handleCheckoutSessionCompleted($payload);
    }

    /**
     * Handle customer.subscription.created event.
     */
    protected function handleCustomerSubscriptionCreated(array $payload)
    {
        $subscription = $payload['data']['object'];

        // Atualizar current_period_end
        $this->updateCurrentPeriodEnd($subscription);

        return parent::handleCustomerSubscriptionCreated($payload);
    }

    /**
     * Handle customer.subscription.updated event.
     */
    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        $subscription = $payload['data']['object'];

        // Atualizar current_period_end
        $this->updateCurrentPeriodEnd($subscription);

        return parent::handleCustomerSubscriptionUpdated($payload);
    }

    /**
     * Handle invoice.payment_succeeded event.
     */
    protected function handleInvoicePaymentSucceeded(array $payload)
    {
        $invoice = $payload['data']['object'];
        $stripeSubscriptionId = $invoice['subscription'] ?? null;

        if ($stripeSubscriptionId) {
            // Buscar subscription no Stripe para obter current_period_end atualizado
            try {
                $stripe = new \Stripe\StripeClient(config('cashier.secret'));
                $stripeSubscription = $stripe->subscriptions->retrieve($stripeSubscriptionId);
                $this->updateCurrentPeriodEnd((array) $stripeSubscription);
            } catch (\Exception $e) {
                Log::warning('Could not update current_period_end after invoice payment', [
                    'subscription_id' => $stripeSubscriptionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return parent::handleInvoicePaymentSucceeded($payload);
    }

    /**
     * Atualiza current_period_end na subscription local.
     */
    protected function updateCurrentPeriodEnd(array $stripeSubscription): void
    {
        $stripeId = $stripeSubscription['id'] ?? null;
        $currentPeriodEnd = $stripeSubscription['current_period_end'] ?? null;

        if (!$stripeId || !$currentPeriodEnd) {
            return;
        }

        Subscription::where('stripe_id', $stripeId)
            ->update([
                'current_period_end' => \Carbon\Carbon::createFromTimestamp($currentPeriodEnd),
            ]);
    }

    /**
     * Extrai o object ID principal do payload.
     */
    protected function extractObjectId(string $eventType, array $payload): ?string
    {
        return match ($eventType) {
            'checkout.session.completed' => $payload['id'] ?? null,
            'customer.subscription.created',
            'customer.subscription.updated',
            'customer.subscription.deleted' => $payload['id'] ?? null,
            'invoice.payment_succeeded',
            'invoice.payment_failed' => $payload['id'] ?? null,
            default => $payload['id'] ?? null,
        };
    }

    /**
     * Extrai o user ID do payload quando possível.
     */
    protected function extractUserId(string $eventType, array $payload): ?int
    {
        // Para checkout.session.completed, usamos client_reference_id
        if ($eventType === 'checkout.session.completed') {
            $clientRefId = $payload['client_reference_id'] ?? null;
            return $clientRefId ? (int) $clientRefId : null;
        }

        // Para outros eventos, tentamos buscar pelo customer ID
        $customerId = $payload['customer'] ?? null;
        if ($customerId) {
            $user = User::where('stripe_id', $customerId)->first();
            return $user?->id;
        }

        return null;
    }
}
```

### Critério de Sucesso
- [ ] Controller criado
- [ ] Extends CashierWebhookController
- [ ] Sem erros de syntax

---

## Passo 3.3: Criar RefundRequestController

### Arquivo: `app/Http/Controllers/RefundRequestController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\RefundRequest;
use App\Notifications\RefundRequestReceivedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RefundRequestController extends Controller
{
    /**
     * Formulário de solicitação de estorno.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $subscriptionName = config('subscription.default_subscription_name', 'default');
        $subscription = $user->subscription($subscriptionName);

        if (!$subscription) {
            return redirect()->route('subscription.show')
                ->with('error', 'Você não possui uma assinatura ativa.');
        }

        return view('subscription.refund', [
            'user' => $user,
            'subscription' => $subscription,
        ]);
    }

    /**
     * Processa solicitação de estorno.
     */
    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:2000',
        ]);

        $user = $request->user();
        $subscriptionName = config('subscription.default_subscription_name', 'default');
        $subscription = $user->subscription($subscriptionName);

        if (!$subscription) {
            return redirect()->route('subscription.show')
                ->with('error', 'Você não possui uma assinatura ativa.');
        }

        // Verificar se já existe solicitação pendente
        $pendingRequest = RefundRequest::where('user_id', $user->id)
            ->where('stripe_subscription_id', $subscription->stripe_id)
            ->pending()
            ->first();

        if ($pendingRequest) {
            return back()->with('error', 'Você já possui uma solicitação de estorno pendente.');
        }

        // Buscar última invoice para obter payment_intent
        $stripeInvoiceId = null;
        $stripePaymentIntentId = null;

        try {
            $stripe = new \Stripe\StripeClient(config('cashier.secret'));
            $invoices = $stripe->invoices->all([
                'subscription' => $subscription->stripe_id,
                'status' => 'paid',
                'limit' => 1,
            ]);

            if (count($invoices->data) > 0) {
                $invoice = $invoices->data[0];
                $stripeInvoiceId = $invoice->id;
                $stripePaymentIntentId = $invoice->payment_intent;
            }
        } catch (\Exception $e) {
            Log::warning('Could not fetch invoice for refund request', [
                'subscription_id' => $subscription->stripe_id,
                'error' => $e->getMessage(),
            ]);
        }

        // Criar solicitação
        $refundRequest = RefundRequest::create([
            'user_id' => $user->id,
            'cashier_subscription_id' => $subscription->id,
            'stripe_subscription_id' => $subscription->stripe_id,
            'stripe_invoice_id' => $stripeInvoiceId,
            'stripe_payment_intent_id' => $stripePaymentIntentId,
            'reason' => $request->input('reason'),
            'status' => RefundRequest::STATUS_PENDING,
        ]);

        // Enviar notificação
        try {
            $user->notify(new RefundRequestReceivedNotification($refundRequest));
        } catch (\Exception $e) {
            Log::warning('Could not send refund request notification', [
                'refund_request_id' => $refundRequest->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('subscription.show')
            ->with('success', 'Sua solicitação de estorno foi enviada e será analisada em breve.');
    }
}
```

### Critério de Sucesso
- [ ] Controller criado
- [ ] Sem erros de syntax

---

## Passo 3.4: Adicionar Rotas

### Arquivo: `routes/web.php`

Adicionar no final do arquivo (antes do fechamento, se houver):

```php
/*
|--------------------------------------------------------------------------
| Subscription Routes
|--------------------------------------------------------------------------
*/

// Página de planos (pública)
Route::get('/assinar', [App\Http\Controllers\SubscriptionController::class, 'index'])
    ->name('subscription.plans');

// Checkout (requer auth)
Route::middleware('auth')->group(function () {
    Route::post('/assinar/checkout', [App\Http\Controllers\SubscriptionController::class, 'checkout'])
        ->name('subscription.checkout');
});

// Callbacks do Stripe Checkout (sem auth - validação via session_id)
Route::get('/assinar/sucesso', [App\Http\Controllers\SubscriptionController::class, 'success'])
    ->name('subscription.success');
Route::get('/assinar/cancelado', [App\Http\Controllers\SubscriptionController::class, 'cancel'])
    ->name('subscription.cancel');

// AJAX para verificar status de processamento
Route::get('/assinar/status', [App\Http\Controllers\SubscriptionController::class, 'checkProcessingStatus'])
    ->name('subscription.check-status');

// Rotas autenticadas do assinante
Route::middleware(['auth'])->prefix('minha-conta')->group(function () {
    Route::get('/assinatura', [App\Http\Controllers\SubscriptionController::class, 'show'])
        ->name('subscription.show');
    Route::get('/assinatura/portal', [App\Http\Controllers\SubscriptionController::class, 'billingPortal'])
        ->name('subscription.portal');

    Route::get('/estorno', [App\Http\Controllers\RefundRequestController::class, 'create'])
        ->name('refund.create');
    Route::post('/estorno', [App\Http\Controllers\RefundRequestController::class, 'store'])
        ->name('refund.store');
});

// Webhook Stripe (usa o controller que estende o Cashier)
Route::post('/stripe/webhook', [App\Http\Controllers\WebhookController::class, 'handleWebhook'])
    ->name('cashier.webhook');
```

### Critério de Sucesso
- [ ] Rotas adicionadas
- [ ] `php artisan route:list` mostra novas rotas
- [ ] Rotas existentes não afetadas

---

## Passo 3.5: Configurar CSRF Exception para Webhook

### Arquivo: `app/Http/Middleware/VerifyCsrfToken.php`

```php
<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'stripe/webhook',
    ];
}
```

### Verificação
```bash
php artisan route:list --path=stripe
# Deve mostrar a rota do webhook
```

### Critério de Sucesso
- [ ] Exception adicionada
- [ ] Site continua funcionando
- [ ] Webhook pode receber POST sem CSRF

---

## 🔒 COMMIT FASE 3

```bash
git add .
git commit -m "feat: controllers e rotas de assinatura

- SubscriptionController: planos, checkout, sucesso, portal
- WebhookController: estende Cashier, idempotência, current_period_end
- RefundRequestController: criar e processar solicitações
- Rotas: /assinar/*, /minha-conta/*, /stripe/webhook
- CSRF exception para webhook

Ref: ASSINATURA_PLAN.md v1.4 - Fase 3"
```

### Checklist Final Fase 3
- [ ] SubscriptionController criado
- [ ] WebhookController criado
- [ ] RefundRequestController criado
- [ ] Rotas adicionadas ao web.php
- [ ] CSRF exception configurada
- [ ] `php artisan route:list` OK
- [ ] Rotas existentes funcionam
- [ ] Admin funciona
- [ ] Commit realizado

---

# FASE 4: Middlewares

## Objetivo
Criar middlewares para controle de acesso baseado em assinatura e features.

---

## Passo 4.1: Criar EnsureUserIsSubscribed

### Arquivo: `app/Http/Middleware/EnsureUserIsSubscribed.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsSubscribed
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->isSubscriber()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Subscription required',
                    'message' => 'Você precisa ser assinante para acessar este recurso.',
                ], 403);
            }

            return redirect()->route('subscription.plans')
                ->with('warning', 'Você precisa ser assinante para acessar este recurso.');
        }

        return $next($request);
    }
}
```

---

## Passo 4.2: Criar EnsureUserHasFeature

### Arquivo: `app/Http/Middleware/EnsureUserHasFeature.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  string  $featureKey  The feature key to check
     */
    public function handle(Request $request, Closure $next, string $featureKey)
    {
        if (!$request->user() || !$request->user()->hasFeature($featureKey)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Feature not available',
                    'message' => 'Seu plano não inclui acesso a este recurso.',
                ], 403);
            }

            return redirect()->route('subscription.plans')
                ->with('warning', 'Seu plano não inclui acesso a este recurso. Considere fazer upgrade!');
        }

        return $next($request);
    }
}
```

---

## Passo 4.3: Registrar Middlewares no Kernel

### Arquivo: `app/Http/Kernel.php`

Adicionar ao array `$routeMiddleware`:

```php
protected $routeMiddleware = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
    'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
    'can' => \Illuminate\Auth\Middleware\Authorize::class,
    'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
    'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
    'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
    'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    'admin_access' => \App\Http\Middleware\AdminMiddleware::class,
    'bearer.token' => \App\Http\Middleware\BearerTokenMiddleware::class,
    
    // Subscription middlewares
    'subscribed' => \App\Http\Middleware\EnsureUserIsSubscribed::class,
    'feature' => \App\Http\Middleware\EnsureUserHasFeature::class,
];
```

### Verificação
```bash
php artisan tinker
>>> app('router')->getMiddleware()
# Deve listar 'subscribed' e 'feature'
```

### Critério de Sucesso
- [ ] Middlewares criados
- [ ] Registrados no Kernel
- [ ] Site continua funcionando

---

## 🔒 COMMIT FASE 4

```bash
git add .
git commit -m "feat: middlewares de assinatura

- EnsureUserIsSubscribed: verifica se usuário é assinante
- EnsureUserHasFeature: verifica se plano tem feature específica
- Registrados no Kernel como 'subscribed' e 'feature'

Ref: ASSINATURA_PLAN.md v1.4 - Fase 4"
```

### Checklist Final Fase 4
- [ ] EnsureUserIsSubscribed criado
- [ ] EnsureUserHasFeature criado
- [ ] Middlewares registrados no Kernel
- [ ] Site continua funcionando
- [ ] Commit realizado

---

# FASE 5: Views de Assinatura

## Objetivo
Criar todas as views necessárias para o sistema de assinaturas.

---

## Passo 5.1: Criar Diretório e Views Base

### Criar diretório
```bash
mkdir -p resources/views/subscription
```

---

## Passo 5.2: View - Página de Planos

### Arquivo: `resources/views/subscription/plans.blade.php`

```blade
@extends('front.base')

@section('title', 'Planos de Assinatura - Teses e Súmulas')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8 text-center">
            <h1 class="display-5 fw-bold mb-3">Escolha seu Plano</h1>
            <p class="lead text-muted">
                Acesse conteúdo exclusivo e navegue sem anúncios.
            </p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(empty($plans))
        <div class="alert alert-info text-center">
            <p class="mb-0">Planos em breve disponíveis. Cadastre-se para ser notificado!</p>
        </div>
    @else
        <div class="row justify-content-center">
            @foreach($plans as $tier => $plan)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm {{ $tier === 'premium' ? 'border-primary' : '' }}">
                        @if($tier === 'premium')
                            <div class="card-header bg-primary text-white text-center py-2">
                                <small class="fw-bold">MAIS POPULAR</small>
                            </div>
                        @endif
                        
                        <div class="card-body d-flex flex-column">
                            <h3 class="card-title text-center mb-3">{{ $plan['name'] }}</h3>
                            
                            @if(!empty($plan['description']))
                                <p class="text-muted text-center">{{ $plan['description'] }}</p>
                            @endif

                            <div class="text-center mb-4">
                                @if(isset($plan['prices']['monthly']))
                                    <div class="mb-2">
                                        <span class="h2 fw-bold">
                                            R$ {{ number_format($plan['prices']['monthly']['amount'], 2, ',', '.') }}
                                        </span>
                                        <span class="text-muted">/mês</span>
                                    </div>
                                @endif
                                
                                @if(isset($plan['prices']['yearly']))
                                    <div class="text-success small">
                                        ou R$ {{ number_format($plan['prices']['yearly']['amount'], 2, ',', '.') }}/ano
                                        <br>
                                        <span class="badge bg-success">
                                            Economize {{ round(100 - ($plan['prices']['yearly']['amount'] / ($plan['prices']['monthly']['amount'] * 12)) * 100) }}%
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <hr>

                            <ul class="list-unstyled mb-4 flex-grow-1">
                                <li class="mb-2">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    Navegação sem anúncios
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    Conteúdo exclusivo
                                </li>
                                @if($tier === 'premium')
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        Ferramentas de IA <span class="badge bg-secondary">Em breve</span>
                                    </li>
                                @endif
                            </ul>

                            <div class="d-grid gap-2">
                                @auth
                                    @if(isset($plan['prices']['monthly']))
                                        <form action="{{ route('subscription.checkout') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="priceId" value="{{ $plan['prices']['monthly']['id'] }}">
                                            <button type="submit" class="btn {{ $tier === 'premium' ? 'btn-primary' : 'btn-outline-primary' }} btn-lg w-100">
                                                Assinar Mensal
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if(isset($plan['prices']['yearly']))
                                        <form action="{{ route('subscription.checkout') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="priceId" value="{{ $plan['prices']['yearly']['id'] }}">
                                            <button type="submit" class="btn btn-outline-success w-100">
                                                Assinar Anual
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}?redirect={{ urlencode(route('subscription.plans')) }}" 
                                       class="btn {{ $tier === 'premium' ? 'btn-primary' : 'btn-outline-primary' }} btn-lg">
                                        Fazer Login para Assinar
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row justify-content-center mt-5">
            <div class="col-lg-8 text-center text-muted">
                <small>
                    <i class="bi bi-shield-check me-1"></i>
                    Pagamento seguro via Stripe. Cancele quando quiser.
                </small>
            </div>
        </div>
    @endif
</div>
@endsection
```

---

## Passo 5.3: View - Página de Sucesso

### Arquivo: `resources/views/subscription/success.blade.php`

```blade
@extends('front.base')

@section('title', 'Assinatura Confirmada - Teses e Súmulas')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div id="processing-state" class="{{ $isProcessed ? 'd-none' : '' }}">
                <div class="spinner-border text-primary mb-4" role="status" style="width: 4rem; height: 4rem;">
                    <span class="visually-hidden">Processando...</span>
                </div>
                <h2 class="mb-3">Processando seu pagamento...</h2>
                <p class="text-muted">
                    Aguarde enquanto confirmamos sua assinatura. Isso pode levar alguns segundos.
                </p>
            </div>

            <div id="success-state" class="{{ $isProcessed ? '' : 'd-none' }}">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                </div>
                <h2 class="mb-3">Assinatura Confirmada!</h2>
                <p class="text-muted mb-4">
                    Bem-vindo ao Teses e Súmulas! Agora você tem acesso a todos os benefícios do seu plano.
                </p>
                <div class="d-grid gap-3">
                    <a href="{{ route('subscription.show') }}" class="btn btn-primary btn-lg">
                        Ver Minha Assinatura
                    </a>
                    <a href="{{ route('searchpage') }}" class="btn btn-outline-secondary">
                        Explorar Conteúdo
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!$isProcessed)
<script>
    (function() {
        const sessionId = '{{ $sessionId }}';
        const checkInterval = 2000; // 2 segundos
        const maxAttempts = 30; // 1 minuto máximo
        let attempts = 0;

        function checkStatus() {
            attempts++;
            
            fetch('{{ route("subscription.check-status") }}?session_id=' + encodeURIComponent(sessionId))
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'completed') {
                        document.getElementById('processing-state').classList.add('d-none');
                        document.getElementById('success-state').classList.remove('d-none');
                    } else if (attempts < maxAttempts) {
                        setTimeout(checkStatus, checkInterval);
                    } else {
                        // Timeout - mostrar mensagem alternativa
                        document.getElementById('processing-state').innerHTML = `
                            <div class="alert alert-info">
                                <h5>Processamento em andamento</h5>
                                <p>Seu pagamento está sendo processado. Você receberá um email de confirmação em breve.</p>
                                <a href="{{ route('subscription.show') }}" class="btn btn-primary">
                                    Ver Status da Assinatura
                                </a>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error checking status:', error);
                    if (attempts < maxAttempts) {
                        setTimeout(checkStatus, checkInterval);
                    }
                });
        }

        // Iniciar polling
        setTimeout(checkStatus, checkInterval);
    })();
</script>
@endif
@endsection
```

---

## Passo 5.4: View - Página de Cancelamento

### Arquivo: `resources/views/subscription/cancel.blade.php`

```blade
@extends('front.base')

@section('title', 'Checkout Cancelado - Teses e Súmulas')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="mb-4">
                <i class="bi bi-x-circle text-muted" style="font-size: 5rem;"></i>
            </div>
            <h2 class="mb-3">Checkout Cancelado</h2>
            <p class="text-muted mb-4">
                Você cancelou o processo de assinatura. Sem problemas! 
                Você pode voltar quando quiser.
            </p>
            <div class="d-grid gap-3">
                <a href="{{ route('subscription.plans') }}" class="btn btn-primary btn-lg">
                    Ver Planos Novamente
                </a>
                <a href="{{ route('searchpage') }}" class="btn btn-outline-secondary">
                    Voltar para o Site
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
```

---

## Passo 5.5: View - Status da Assinatura

### Arquivo: `resources/views/subscription/show.blade.php`

```blade
@extends('front.base')

@section('title', 'Minha Assinatura - Teses e Súmulas')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="mb-4">Minha Assinatura</h1>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($isSubscriber)
                {{-- Usuário é assinante --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1">Status da Assinatura</h5>
                                @if($isOnGracePeriod)
                                    <span class="badge bg-warning text-dark">Cancelada - Acesso até {{ $accessEndsAt->format('d/m/Y') }}</span>
                                @else
                                    <span class="badge bg-success">Ativa</span>
                                @endif
                            </div>
                            <a href="{{ route('subscription.portal') }}" class="btn btn-outline-primary">
                                <i class="bi bi-gear me-1"></i>
                                Gerenciar Assinatura
                            </a>
                        </div>

                        @if($subscription)
                            <hr>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">Plano</small>
                                    <strong>{{ $planName ?? 'Assinante' }}</strong>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">Status no Stripe</small>
                                    <strong>{{ ucfirst($subscription->stripe_status) }}</strong>
                                </div>
                                @if($subscription->current_period_end)
                                    <div class="col-md-6 mb-3">
                                        <small class="text-muted d-block">Próxima Renovação</small>
                                        <strong>{{ $subscription->current_period_end->format('d/m/Y') }}</strong>
                                    </div>
                                @endif
                                @if($isOnGracePeriod && $accessEndsAt)
                                    <div class="col-md-6 mb-3">
                                        <small class="text-muted d-block">Acesso até</small>
                                        <strong class="text-warning">{{ $accessEndsAt->format('d/m/Y') }}</strong>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                @if($isOnGracePeriod)
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-2"></i>
                        Você cancelou sua assinatura. Seu acesso permanece ativo até <strong>{{ $accessEndsAt->format('d/m/Y') }}</strong>.
                        <br>
                        <a href="{{ route('subscription.portal') }}" class="alert-link">Clique aqui para reativar</a> se mudar de ideia.
                    </div>
                @endif

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Seus Benefícios</h5>
                        <ul class="list-unstyled mb-0">
                            @if(auth()->user()->hasFeature('no_ads'))
                                <li class="mb-2">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    Navegação sem anúncios
                                </li>
                            @endif
                            @if(auth()->user()->hasFeature('exclusive_content'))
                                <li class="mb-2">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    Acesso a conteúdo exclusivo
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Ações</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('subscription.portal') }}" class="btn btn-outline-primary">
                                <i class="bi bi-credit-card me-1"></i>
                                Atualizar Pagamento
                            </a>
                            <a href="{{ route('subscription.portal') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-receipt me-1"></i>
                                Ver Faturas
                            </a>
                            <a href="{{ route('refund.create') }}" class="btn btn-outline-danger">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Solicitar Estorno
                            </a>
                        </div>
                    </div>
                </div>

            @else
                {{-- Usuário não é assinante --}}
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-star text-muted mb-3" style="font-size: 3rem;"></i>
                        <h4 class="mb-3">Você ainda não é assinante</h4>
                        <p class="text-muted mb-4">
                            Assine para navegar sem anúncios e acessar conteúdo exclusivo.
                        </p>
                        <a href="{{ route('subscription.plans') }}" class="btn btn-primary btn-lg">
                            Ver Planos
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
```

---

## Passo 5.6: View - Formulário de Estorno

### Arquivo: `resources/views/subscription/refund.blade.php`

```blade
@extends('front.base')

@section('title', 'Solicitar Estorno - Teses e Súmulas')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <h1 class="mb-4">Solicitar Estorno</h1>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="alert alert-info mb-4">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Antes de solicitar estorno:</strong>
                <ul class="mb-0 mt-2">
                    <li>Se deseja apenas cancelar a renovação, use o <a href="{{ route('subscription.portal') }}">Portal de Gerenciamento</a>.</li>
                    <li>Estornos são analisados caso a caso pela nossa equipe.</li>
                    <li>Você receberá um email com a resposta em até 5 dias úteis.</li>
                </ul>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('refund.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="reason" class="form-label">Motivo do Estorno</label>
                            <textarea 
                                name="reason" 
                                id="reason" 
                                class="form-control @error('reason') is-invalid @enderror"
                                rows="5"
                                placeholder="Por favor, descreva o motivo da sua solicitação de estorno..."
                                required
                                minlength="10"
                                maxlength="2000"
                            >{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Mínimo 10 caracteres</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Enviar Solicitação
                            </button>
                            <a href="{{ route('subscription.show') }}" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

---

## 🔒 COMMIT FASE 5

```bash
git add .
git commit -m "feat: views do sistema de assinaturas

- plans.blade.php: página de planos com preços
- success.blade.php: confirmação com polling AJAX
- cancel.blade.php: checkout cancelado
- show.blade.php: status da assinatura do usuário
- refund.blade.php: formulário de solicitação de estorno

Ref: ASSINATURA_PLAN.md v1.4 - Fase 5"
```

### Checklist Final Fase 5
- [ ] Diretório subscription criado
- [ ] Todas as views criadas
- [ ] Views usam front.base corretamente
- [ ] Site continua funcionando
- [ ] Commit realizado

---

# FASE 6: Webhooks e Eventos

## Objetivo
Criar Listeners para eventos de webhook e notificações.

---

## Passo 6.1: Criar Listeners

### Arquivo: `app/Listeners/SubscriptionCreatedListener.php`

```php
<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\WelcomeSubscriberNotification;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookReceived;

class SubscriptionCreatedListener
{
    public function handle(WebhookReceived $event)
    {
        if ($event->payload['type'] !== 'customer.subscription.created') {
            return;
        }

        $subscription = $event->payload['data']['object'];
        $customerId = $subscription['customer'];

        $user = User::where('stripe_id', $customerId)->first();

        if (!$user) {
            Log::warning('SubscriptionCreatedListener: User not found for customer', [
                'customer_id' => $customerId,
            ]);
            return;
        }

        // Enviar email de boas-vindas
        try {
            $user->notify(new WelcomeSubscriberNotification());
        } catch (\Exception $e) {
            Log::error('Error sending welcome notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
```

### Arquivo: `app/Listeners/SubscriptionCanceledListener.php`

```php
<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\SubscriptionCanceledNotification;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookReceived;
use Laravel\Cashier\Subscription;

class SubscriptionCanceledListener
{
    public function handle(WebhookReceived $event)
    {
        if ($event->payload['type'] !== 'customer.subscription.updated') {
            return;
        }

        $subscription = $event->payload['data']['object'];
        
        // Verificar se foi cancelamento agendado
        if (!($subscription['cancel_at_period_end'] ?? false)) {
            return;
        }

        $customerId = $subscription['customer'];
        $user = User::where('stripe_id', $customerId)->first();

        if (!$user) {
            return;
        }

        // Buscar data de término
        $localSubscription = Subscription::where('stripe_id', $subscription['id'])->first();
        $endsAt = $localSubscription?->ends_at;

        try {
            $user->notify(new SubscriptionCanceledNotification($endsAt));
        } catch (\Exception $e) {
            Log::error('Error sending cancellation notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
```

### Arquivo: `app/Listeners/PaymentFailedListener.php`

```php
<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookReceived;

class PaymentFailedListener
{
    /**
     * MVP: Não envia email (Stripe já envia via Smart Retries).
     * Apenas loga para monitoramento.
     */
    public function handle(WebhookReceived $event)
    {
        if ($event->payload['type'] !== 'invoice.payment_failed') {
            return;
        }

        $invoice = $event->payload['data']['object'];
        $customerId = $invoice['customer'];

        $user = User::where('stripe_id', $customerId)->first();

        Log::warning('Payment failed for user', [
            'user_id' => $user?->id,
            'customer_id' => $customerId,
            'invoice_id' => $invoice['id'],
            'attempt_count' => $invoice['attempt_count'] ?? 1,
        ]);

        // Futuro: Pode-se setar flag no usuário para exibir banner
        // $user?->update(['has_payment_issue' => true]);
    }
}
```

---

## Passo 6.2: Registrar Eventos

### Arquivo: `app/Providers/EventServiceProvider.php`

Adicionar ao array `$listen`:

```php
use Laravel\Cashier\Events\WebhookReceived;

protected $listen = [
    Registered::class => [
        SendEmailVerificationNotification::class,
    ],
    
    // Stripe Webhook Events
    WebhookReceived::class => [
        \App\Listeners\SubscriptionCreatedListener::class,
        \App\Listeners\SubscriptionCanceledListener::class,
        \App\Listeners\PaymentFailedListener::class,
    ],
];
```

### Verificação
```bash
php artisan event:list
# Deve mostrar WebhookReceived com os listeners
```

---

## 🔒 COMMIT FASE 6

```bash
git add .
git commit -m "feat: listeners para webhooks stripe

- SubscriptionCreatedListener: envia email de boas-vindas
- SubscriptionCanceledListener: notifica sobre cancelamento
- PaymentFailedListener: loga falhas (Stripe envia email)
- Registrado WebhookReceived no EventServiceProvider

Ref: ASSINATURA_PLAN.md v1.4 - Fase 6"
```

---

# FASE 7: Notificações

## Objetivo
Criar classes de notificação para emails transacionais.

---

## Passo 7.1: Criar Notificações

### Arquivo: `app/Notifications/WelcomeSubscriberNotification.php`

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeSubscriberNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Bem-vindo ao Teses e Súmulas!')
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line('Sua assinatura foi ativada com sucesso.')
            ->line('Agora você tem acesso a:')
            ->line('✓ Navegação sem anúncios')
            ->line('✓ Conteúdo exclusivo')
            ->action('Explorar Conteúdo', url('/'))
            ->line('Obrigado por assinar o Teses e Súmulas!');
    }
}
```

### Arquivo: `app/Notifications/SubscriptionCanceledNotification.php`

```php
<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionCanceledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ?Carbon $endsAt;

    public function __construct(?Carbon $endsAt = null)
    {
        $this->endsAt = $endsAt;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('Sua assinatura foi cancelada')
            ->greeting('Olá, ' . $notifiable->name)
            ->line('Confirmamos o cancelamento da sua assinatura.');

        if ($this->endsAt) {
            $message->line('Você ainda terá acesso até: ' . $this->endsAt->format('d/m/Y'));
        }

        return $message
            ->line('Sentiremos sua falta! Se mudar de ideia, pode reativar a qualquer momento.')
            ->action('Reativar Assinatura', route('subscription.plans'))
            ->line('Obrigado por ter sido assinante do Teses e Súmulas.');
    }
}
```

### Arquivo: `app/Notifications/SubscriptionRenewingSoonNotification.php`

```php
<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRenewingSoonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Carbon $renewsAt;

    public function __construct(Carbon $renewsAt)
    {
        $this->renewsAt = $renewsAt;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Sua assinatura será renovada em breve')
            ->greeting('Olá, ' . $notifiable->name)
            ->line('Sua assinatura do Teses e Súmulas será renovada automaticamente em ' . $this->renewsAt->format('d/m/Y') . '.')
            ->line('Se você deseja cancelar ou alterar seu plano, pode fazer isso a qualquer momento.')
            ->action('Gerenciar Assinatura', route('subscription.portal'))
            ->line('Obrigado por continuar conosco!');
    }
}
```

### Arquivo: `app/Notifications/RefundRequestReceivedNotification.php`

```php
<?php

namespace App\Notifications;

use App\Models\RefundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundRequestReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected RefundRequest $refundRequest;

    public function __construct(RefundRequest $refundRequest)
    {
        $this->refundRequest = $refundRequest;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Recebemos sua solicitação de estorno')
            ->greeting('Olá, ' . $notifiable->name)
            ->line('Recebemos sua solicitação de estorno e ela será analisada pela nossa equipe.')
            ->line('Prazo de resposta: até 5 dias úteis.')
            ->line('Você receberá um email com nossa decisão.')
            ->action('Ver Status da Assinatura', route('subscription.show'))
            ->line('Obrigado pela paciência.');
    }
}
```

---

## Passo 7.2: Criar Job de Lembrete de Renovação

### Arquivo: `app/Jobs/SendRenewalReminders.php`

```php
<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\SubscriptionRenewingSoonNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;

class SendRenewalReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $daysAhead = 7;
        $targetDate = Carbon::now()->addDays($daysAhead)->startOfDay();
        $endOfTargetDate = $targetDate->copy()->endOfDay();

        // Buscar subscriptions ativas que renovam em 7 dias
        $subscriptions = Subscription::where('stripe_status', 'active')
            ->whereNull('ends_at') // Não está em grace period
            ->whereBetween('current_period_end', [$targetDate, $endOfTargetDate])
            ->with('user')
            ->get();

        foreach ($subscriptions as $subscription) {
            if (!$subscription->user) {
                continue;
            }

            try {
                $subscription->user->notify(
                    new SubscriptionRenewingSoonNotification($subscription->current_period_end)
                );

                Log::info('Renewal reminder sent', [
                    'user_id' => $subscription->user->id,
                    'renews_at' => $subscription->current_period_end,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send renewal reminder', [
                    'user_id' => $subscription->user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
```

---

## Passo 7.3: Agendar Job no Console Kernel

### Arquivo: `app/Console/Kernel.php`

Adicionar no método `schedule()`:

```php
protected function schedule(Schedule $schedule)
{
    // ... outros jobs ...

    // Enviar lembretes de renovação às 10h
    $schedule->job(new \App\Jobs\SendRenewalReminders())
        ->dailyAt('10:00')
        ->withoutOverlapping();
}
```

---

## 🔒 COMMIT FASE 7

```bash
git add .
git commit -m "feat: notificações e job de lembrete

- WelcomeSubscriberNotification
- SubscriptionCanceledNotification
- SubscriptionRenewingSoonNotification
- RefundRequestReceivedNotification
- SendRenewalReminders job (diário às 10h)

Ref: ASSINATURA_PLAN.md v1.4 - Fase 7"
```

---

# FASE 8: Habilitar Registro de Usuários

## Objetivo
Habilitar registro público de usuários.

---

## Passo 8.1: Alterar Configuração de Rotas Auth

### Arquivo: `routes/web.php`

Alterar de:
```php
Auth::routes([
    'register' => false,
]);
```

Para:
```php
Auth::routes([
    'register' => true,
]);
```

### Critério de Sucesso
- [ ] Página `/register` acessível
- [ ] Registro funciona
- [ ] Login funciona
- [ ] Logout funciona

---

## 🔒 COMMIT FASE 8

```bash
git add .
git commit -m "feat: habilitar registro público de usuários

- Alterado 'register' => true em Auth::routes

Ref: ASSINATURA_PLAN.md v1.4 - Fase 8"
```

---

# FASE 9: Integração de Ads Condicionais

## Objetivo
Modificar exibição de anúncios para assinantes não verem ads.

---

## Passo 9.1: Criar Partial de Ads

### Arquivo: `resources/views/partials/ads.blade.php`

```blade
{{-- 
    Partial de Anúncios
    Só exibe ads se:
    1. Usuário não está logado OU
    2. Usuário logado deve ver ads (não tem feature no_ads)
--}}
@if(!auth()->check() || auth()->user()->shouldSeeAds())
    @yield('ad-content')
@endif
```

---

## Passo 9.2: Modificar base.blade.php

A modificação exata depende da estrutura atual. Baseado na análise, o AdSense está nas linhas 93-100.

**Estratégia:** Envolver o código de ads em verificação condicional.

### Antes (aproximado):
```blade
@if(config('app.env') == 'production')
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6476437932373204" crossorigin="anonymous"></script>
@endif
```

### Depois:
```blade
@if(config('app.env') == 'production' && (!auth()->check() || auth()->user()->shouldSeeAds()))
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6476437932373204" crossorigin="anonymous"></script>
@endif
```

**Nota:** Esta alteração deve ser feita com cuidado. O script de ads só carrega se:
1. Ambiente é produção **E**
2. Usuário não está logado **OU** usuário deve ver ads

---

## 🔒 COMMIT FASE 9

```bash
git add .
git commit -m "feat: ads condicionais para assinantes

- Criado partials/ads.blade.php
- Modificado base.blade.php para verificar shouldSeeAds()
- Assinantes com feature no_ads não veem anúncios

Ref: ASSINATURA_PLAN.md v1.4 - Fase 9"
```

---

# FASE 10: Admin Filament

## Objetivo
Instalar e configurar painel admin com Filament (focado em assinaturas).

**Nota:** O painel vive em `/painel` para não conflitar com o `/admin` existente.

---

## Checklist da Fase 10 (atualizado)

- [x] Instalar Filament 2.x (`composer require filament/filament:^2.0`)
- [x] Publicar config (`php artisan vendor:publish --tag=filament-config`)
- [x] Configurar rota base em `/painel` (`config/filament.php`)
- [x] Implementar `FilamentUser` no `User` (acesso restrito a admins)
- [x] Resources criados: `UserResource` (read-only), `RefundRequestResource` (editar status/notas), `PlanFeatureResource` (CRUD)
- [x] Widgets criados: métricas, últimas assinaturas, estornos pendentes, últimos cancelamentos
- [x] Filtro por plano (PRO/PREMIUM) na lista de usuários
- [ ] Validar uso real do `/painel` e ajustar UX conforme necessidade

---

## Passos Detalhados

### Passo 10.1: Instalar Filament

```bash
composer require filament/filament:^2.0
```

### Passo 10.1.1: Publicar configuração (opcional, mas feito)

```bash
php artisan vendor:publish --tag=filament-config
```

### Passo 10.2: Configurar Rota Base

Arquivo: `config/filament.php`
```php
'path' => 'painel',
```

### Passo 10.3: Acesso ao painel

- Implementar `FilamentUser` no model `User`.
- Permitir acesso apenas para emails em `config('tes_constants.admins')`.
- **Não é necessário criar novo usuário** se o admin já existe.

### Passo 10.4: Resources (feito)

- UserResource (read-only, filtro por status/plano, link Stripe)
- RefundRequestResource (status e notas internas, links Stripe)
- PlanFeatureResource (CRUD com produtos do Stripe)

### Passo 10.5: Widgets (feito)

- Métricas (ativos, grace period, estornos pendentes)
- Últimas assinaturas
- Estornos pendentes
- Últimos cancelamentos

---

## 🔒 COMMIT FASE 10

```bash
git add .
git commit -m "feat: painel admin filament

- Instalado Filament 2.x
- Configurado rota /painel
- Criado usuário admin

Ref: ASSINATURA_PLAN.md v1.4 - Fase 10"
```

---

# Apêndice A: Checklist de Validação por Fase

## Validação Pré-Deploy

Após cada fase, verificar:

- [ ] Site carrega normalmente
- [ ] Login/logout funciona
- [ ] Admin existente (/admin) funciona
- [ ] Nenhum erro no log (storage/logs/laravel.log)
- [ ] Migrations revertíveis (`php artisan migrate:rollback`)

## Teste de Integração Stripe

1. **Stripe CLI** (local):
   ```bash
   stripe listen --forward-to localhost:8000/stripe/webhook
   ```

2. **Testar checkout**:
   - Criar conta test
   - Assinar plano
   - Verificar webhook recebido
   - Verificar subscription no banco

3. **Testar cancelamento**:
   - Acessar Billing Portal
   - Cancelar assinatura
   - Verificar grace period funciona

---

# Apêndice B: Rollback de Emergência

## Reverter Fase Específica

```bash
# Reverter último commit
git revert HEAD

# Ou reverter para commit específico
git log --oneline
git revert <commit-hash>

# Reverter migrations
php artisan migrate:rollback --step=<número>
```

## Remover Cashier Completamente

```bash
# 1. Reverter migrations
php artisan migrate:rollback --path=database/migrations/2026_01_18*

# 2. Remover Billable do User model
# 3. Remover dependência
composer remove laravel/cashier
```

---

# Apêndice C: Comandos Úteis

```bash
# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Verificar rotas
php artisan route:list --path=assinar
php artisan route:list --path=minha-conta

# Testar models
php artisan tinker
>>> \App\Models\User::first()->shouldSeeAds()

# Verificar eventos
php artisan event:list

# Stripe CLI
stripe listen --forward-to localhost:8000/stripe/webhook
stripe trigger checkout.session.completed
```

---

*Fim do documento ASSINATURA_SPECS.md*
