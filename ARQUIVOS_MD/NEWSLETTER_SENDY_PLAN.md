---
name: newsletter sendy integration
overview: "Integrar opt-in/opt-out da newsletter (Sendy lista 2, brand 1) em todo o site: auto-inscrição no registro/Google (variante B + toast), toggle no painel, form AJAX em `/newsletters`, popup configurável para visitantes (A/B e gatilhos) e stats granulares. Arquitetura híbrida: escritas via API Sendy, leituras via MySQL do Sendy, cache em `users.newsletter_subscribed_at`. 8 fases com validação humana entre cada uma."
todos:
  - id: phase0
    content: "FASE 0 — Briefing, feature flag e leitura do plano (sem código de domínio)"
    status: completed
  - id: phase1
    content: "FASE 1 — Config (services.sendy, connection sendy), SendyService + jobs + testes unitários (sem expor em UI)"
    status: completed
  - id: phase2
    content: "FASE 2 — Migrations (users cache + newsletter_subscription_events) + models/enums/DTOs + testes"
    status: completed
  - id: phase3
    content: "FASE 3 — Endpoint público + form AJAX em /newsletters (substitui link externo, 3 estados)"
    status: completed
  - id: phase4
    content: "FASE 4 — Rule::email + auto-inscrição registro/Google (variante B) + toast"
    status: completed
  - id: phase5
    content: "FASE 5 — Toggle Livewire no painel /minha-conta/perfil"
    status: completed
  - id: phase6
    content: "FASE 6 — Popup visitante (Alpine.js, 3 gatilhos, A/B, cookies) + Filament NewsletterPopupSettings"
    status: completed
  - id: phase7
    content: "FASE 7 — Filament NewsletterStats (dashboard) + comando newsletter:sync + schedule"
    status: pending
  - id: phase8
    content: "FASE 8 — Atualizar PROJECT_BRIEF.md + Pint + suite completa de testes"
    status: pending
isProject: false
---

# Newsletter Sendy — Integração T&S

Integração ponta-a-ponta para captura de inscrições na newsletter do T&S, sincronizada com Sendy (lista 2 / brand 1) por API + DB.

---

## STATUS TRACKER — atualizar a cada fase

> O agente executor DEVE atualizar esta tabela no fim de cada fase, marcando o status e a data. Isso permite retomar o trabalho em uma nova conversa sem perder contexto.

| Fase | Descrição curta | Status | Data | Commit (opcional) |
|------|-----------------|--------|------|-------------------|
| 0 | Briefing + feature flag + .env.example confirmado | `validated` | 2026-05-20 | — |
| 1 | SendyService + jobs + testes unit | `validated` | 2026-05-20 | — |
| 2 | Migrations + Models + Enums + DTOs | `validated` | 2026-05-20 | — |
| 3 | Form AJAX em /newsletters + Filament kill switch | `validated` | 2026-05-20 | 4253b7c |
| 4 | Auto-inscrição registro/Google + Rule::email + toast | `validated` | 2026-05-21 | — |
| 5 | Toggle no painel /minha-conta/perfil | `validated` | 2026-05-20 | 4253b7c |
| 6 | Popup visitante + Filament settings | `validated` | 2026-05-21 | — |
| 7 | Filament stats + sync command | `pending` | — | — |
| 8 | PROJECT_BRIEF + Pint + suite final | `pending` | — | — |

Status válidos: `pending` | `in_progress` | `awaiting_validation` | `validated` | `blocked`.

---

## PRINCÍPIOS DE EXECUÇÃO (LEIA ANTES DE COMEÇAR)

1. **Compartimentalização**: cada fase é uma unidade independente. NÃO avançar para a próxima fase sem o gate de validação humana da anterior aprovado.
2. **Site sempre estável**: cada fase deve manter o site 100% funcional. O user pode commitar/empurrar para prod a qualquer momento.
3. **Feature flag global**: `SiteSetting::getAsBool('newsletter_integration_enabled')` controla TUDO. Default `false` até a Fase 8. Toda integração com Sendy só acontece se a flag estiver `true`. **Sem isso, o trabalho é invisível em prod.**
4. **Falhas no Sendy NÃO podem quebrar o site**: todo chamado externo dentro de `try/catch`, log estruturado, fallback silencioso (registro/login funcionam mesmo se Sendy estiver offline).
5. **Testes obrigatórios por fase**: a fase só está concluída quando os testes da fase passam (`php artisan test --compact --filter=...`).
6. **Validação no browser obrigatória** quando a fase tem componente visual: o agente reporta os passos exatos para o user testar em `https://teses.test`.
7. **Atualizar o plano no fim de cada fase**: marcar status, adicionar notas, registrar decisões tomadas durante a implementação. Atualizar também a tabela do STATUS TRACKER.
8. **Não criar verification scripts** quando testes Pest cobrem. Tinker pode ser usado para debug pontual via Boost MCP.
9. **Pint dirty antes de cada commit/handoff**: `vendor/bin/pint --dirty --format agent`.

## CHECKLIST DE GATE (aplicar no fim de CADA fase)

```
[ ] Código implementado conforme spec da fase
[ ] Testes Pest da fase verdes (`php artisan test --compact --filter=NomeDoTeste`)
[ ] Suite completa não regrediu (`php artisan test --compact`)
[ ] Pint passou (`vendor/bin/pint --dirty --format agent`)
[ ] Validação no browser concluída (se aplicável) — relatar passos exatos ao user
[ ] Tabela STATUS TRACKER atualizada (status `validated` + data)
[ ] Resumo da fase escrito na seção "Notas de execução" da fase
[ ] User explicitamente aprovou (responder "Pode avançar para a Fase N+1")
```

## CONVENÇÕES DO PROJETO

- PHP 8.3, Laravel 12 (estrutura legada — middleware em `Http/Kernel.php`, schedule em `Console/Kernel.php`).
- Filament 4 (painel `/admin/painel`), Tailwind v3 com prefixo `tw-`, Livewire 3.
- Form Requests para validação. Eloquent + relações tipadas. `php artisan make:*` para scaffolding.
- Honeypot Spatie (`@honeypot`) em todos os forms públicos.
- Laravel Boost MCP habilitado: usar `search-docs`, `database-schema`, `database-query`, `tinker`, `list-artisan-commands`.
- Toda comunicação com user em **português**.

### Validação de email (Laravel 12 — padrão do projeto)

Todos os endpoints/formulários da newsletter que aceitam email de terceiros devem usar o rule builder nativo:

```php
use Illuminate\Validation\Rule;

'email' => [
    'required',
    'string',
    'max:255',
    Rule::email()
        ->rfcCompliant(strict: false)
        ->validateMxRecord()
        ->preventSpoofing(),
],
```

Equivalente legado: `email:rfc,dns,spoof` (preferir `Rule::email()` em código novo).

- `NewsletterSubscribeRequest` usa `Rule::email()` (refatorado na Fase 4).
- Em dev/testes Pest, emails de teste devem usar domínios com MX real (ex.: `@gmail.com`), não `@example.com`.
- Requer extensão PHP `intl` para `preventSpoofing()` / `dns` (já usada no projeto).

### Ambiente dev vs prod (Sendy)

| | Dev (Mac) | Prod |
|---|-----------|------|
| `SENDY_DB_ENABLED` | `false` | `true` |
| Leituras | API Sendy | DB + API |
| Kill switch | Filament `/admin/painel/newsletter-integration-settings` | idem |

## DECISÕES ARQUITETURAIS (já fechadas)

- **Sendy é a fonte da verdade**; cache local em `users.newsletter_subscribed_at` (datetime nullable).
- **Escritas via API Sendy** (`/subscribe`, `/unsubscribe`).
- **Leituras via MySQL do Sendy** (connection readonly `sendy` em `config/database.php`).
- **Google OAuth**: opt-in automático no callback; toast pós-login informando, com link p/ desmarcar.
- **Popup visitante**: 3 gatilhos selecionáveis (timer, exit-intent, scroll), frequência por cookie, A/B com 2 variantes.
- **Feature flag global** `newsletter_integration_enabled` (SiteSetting) — kill switch.

---

# FASE 0 — Briefing, feature flag, .env.example

## Objetivo
Preparar o ambiente sem mudar comportamento. Garantir que o agente entendeu o projeto e tem acesso operacional ao Sendy (DB + API).

## Pré-requisitos
- Nenhum. Esta é a primeira fase.

## Arquivos a tocar
- `.env.example` — **já completo pelo user** (linhas 79-93). Apenas verificar.
- `database/seeders/SiteSettingsSeeder.php` — adicionar `newsletter_integration_enabled` (default `'0'`).
- `PROJECT_BRIEF.md` — **não tocar ainda**, isso é Fase 8.

## Implementação
1. Ler `PROJECT_BRIEF.md` na raiz e `.cursor/plans/newsletter_sendy_integration_caa2a18f.plan.md`.
2. Validar via Boost MCP `database-schema` que o schema atual do projeto está OK.
3. Validar acesso ao Sendy DB (deixar para Fase 1, mas conferir que `.env` tem as variáveis).
4. Atualizar `SiteSettingsSeeder` adicionando entrada padrão:
```php
SiteSetting::set('newsletter_integration_enabled', '0'); // kill-switch global
```
5. Rodar `php artisan db:seed --class=SiteSettingsSeeder` localmente.

## Variáveis de ambiente (referência — já no .env e .env.example)

```bash
# SENDY DB AND API
SENDY_API_TOKEN=
SENDY_API_BASE_URL=
SENDY_BRAND_ID=
SENDY_LIST_ID=
SENDY_LIST_INTERNAL_ID=
SENDY_DB_HOST=
SENDY_DB_PORT=
SENDY_DB_NAME=
SENDY_DB_USER=
SENDY_DB_PASSWORD=
SENDY_SILENT_AUTHENTICATED=true
SENDY_SILENT_VISITOR=false
```

## Testes
- `php artisan test --compact --filter=SiteSettings` (suite existente continua verde).

## Validação no browser
- Nenhuma (apenas mudança de seed).

## Critérios de aceitação
- [x] Seeder rodado, registro `newsletter_integration_enabled='0'` visível em `site_settings` (validar via `database-query`).
- [x] `.env.example` confirmado completo (linhas 79-93).
- [x] Plano + PROJECT_BRIEF lidos.

## Notas de execução
- **PHP**: 8.3.30 confirmado (`php -v`).
- **Seeder**: `newsletter_integration_enabled => '0'` adicionado em `SiteSettingsSeeder.php`; `php artisan db:seed --class=SiteSettingsSeeder` executado localmente.
- **BD**: registro confirmado via `database-query` e tinker (`SiteSetting::get()` → `'0'`, `getAsBool()` → `false`).
- **Schema**: `site_settings` validado via Boost `database-schema` (key unique, value text nullable).
- **`.env.example`**: 12 variáveis Sendy presentes (linhas 79-93); espaços trailing em `SENDY_DB_HOST` e `SENDY_SILENT_AUTHENTICATED` — sem impacto funcional.
- **Testes**: `--filter=SiteSetting` → 12 passed, 1 skipped. Suite completa: 562 passed; 1 falha flaky em `CollectionLivewireTest` (passou isolado na reexecução) — não relacionada a esta fase.
- **Pint**: `vendor/bin/pint --dirty --format agent` OK.
- **Validação humana**: user confirmou 2026-05-20.

---

# FASE 1 — SendyService + jobs + testes unit

## Objetivo
Implementar o cliente Sendy isolado. Sem expor em UI. Sem mexer em fluxos existentes. Apenas código + testes.

## Pré-requisitos
- Fase 0 validada.

## Arquivos a criar
- `config/services.php` — adicionar bloco `sendy` (no array existente).
- `config/database.php` — adicionar connection `sendy` (readonly).
- `app/Services/Sendy/SendyService.php`
- `app/Services/Sendy/SendyResult.php` (DTO)
- `app/Services/Sendy/NewsletterSubscriptionContext.php` (DTO)
- `app/Enums/SendyStatus.php`
- `app/Enums/NewsletterEventSource.php`
- `app/Enums/NewsletterEventAction.php`
- `app/Jobs/Newsletter/SubscribeToSendyJob.php`
- `app/Jobs/Newsletter/UnsubscribeFromSendyJob.php`
- `app/Jobs/Newsletter/SyncNewsletterStatusJob.php`
- `tests/Unit/Sendy/SendyServiceTest.php`
- `tests/Feature/Newsletter/SendyJobsTest.php`

## Spec do `config/services.php` (bloco `sendy`)
```php
'sendy' => [
    'api_base_url' => env('SENDY_API_BASE_URL'),
    'api_token' => env('SENDY_API_TOKEN'),
    'list_id' => env('SENDY_LIST_ID'),                 // hash (API)
    'list_internal_id' => env('SENDY_LIST_INTERNAL_ID'), // numérico (DB)
    'brand_id' => env('SENDY_BRAND_ID', 1),
    'silent_authenticated' => env('SENDY_SILENT_AUTHENTICATED', true),
    'silent_visitor' => env('SENDY_SILENT_VISITOR', false),
],
```

Observação: o SendyService envia o token como parâmetro `api_key` (nome exigido pela API), mas a config se chama `api_token` para consistência com `SENDY_API_TOKEN` do `.env`.

## Spec do `config/database.php` (connection `sendy`)
```php
'sendy' => [
    'driver' => 'mysql',
    'host' => env('SENDY_DB_HOST', '127.0.0.1'),
    'port' => env('SENDY_DB_PORT', 3306),
    'database' => env('SENDY_DB_NAME'),
    'username' => env('SENDY_DB_USER'),
    'password' => env('SENDY_DB_PASSWORD'),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
],
```

## Spec dos Enums
```php
enum SendyStatus: string {
    case Subscribed = 'subscribed';
    case Unsubscribed = 'unsubscribed';
    case Unconfirmed = 'unconfirmed';
    case Bounced = 'bounced';
    case SoftBounced = 'soft_bounced';
    case Complained = 'complained';
    case NotFound = 'not_found';
}

enum NewsletterEventSource: string {
    case Registration = 'registration';
    case GoogleOauth = 'google_oauth';
    case PanelToggle = 'panel_toggle';
    case NewslettersForm = 'newsletters_form';
    case Popup = 'popup';
    case Sync = 'sync';
}

enum NewsletterEventAction: string {
    case Subscribed = 'subscribed';
    case Unsubscribed = 'unsubscribed';
    case AlreadySubscribed = 'already_subscribed';
    case Failed = 'failed';
    case Impression = 'impression';
    case Dismissed = 'dismissed';
}
```

## Spec dos DTOs
```php
final class SendyResult {
    public function __construct(
        public bool $success,
        public string $message,
        public bool $alreadySubscribed = false,
    ) {}

    public static function success(string $message = 'true'): self { ... }
    public static function alreadySubscribed(): self { ... }
    public static function failure(string $message): self { ... }
}

final class NewsletterSubscriptionContext {
    public function __construct(
        public NewsletterEventSource $source,
        public ?int $userId = null,
        public ?string $ip = null,
        public ?string $userAgent = null,
        public ?string $referrer = null,
        public ?string $pageUrl = null,
        public ?string $popupVariant = null,
        public ?string $popupTrigger = null,
        public bool $silent = false,
    ) {}

    public static function fromRequest(
        NewsletterEventSource $source,
        Request $request,
        ?int $userId = null,
    ): self { ... }
}
```

## Spec do `SendyService`
```php
final class SendyService
{
    public function __construct(
        private HttpFactory $http,
        private DatabaseManager $db,
    ) {}

    public function isEnabled(): bool;  // checa SiteSetting newsletter_integration_enabled
    public function subscribe(string $email, ?string $name, NewsletterSubscriptionContext $ctx): SendyResult;
    public function unsubscribe(string $email, NewsletterSubscriptionContext $ctx): SendyResult;
    public function getStatus(string $email): SendyStatus;          // DB-first
    public function getStatusFromDb(string $email): ?SendyStatus;   // null se config faltando
    public function getStatusFromApi(string $email): SendyStatus;
    public function isSubscribed(string $email): bool;              // wrapper sobre getStatus
    public function activeSubscriberCount(): ?int;                  // null em falha
}
```

Implementação:
- **`isEnabled()`**: `SiteSetting::getAsBool('newsletter_integration_enabled')`. Se `false`, todos os métodos retornam early com `SendyResult::failure('Integration disabled')` ou enum `NotFound` e logam debug.
- **`subscribe()`**: POST `{api_base_url}/subscribe` com `api_key, name, email, list, ipaddress, referrer, gdpr=true, silent={ctx.silent ? 'true' : 'false'}, boolean=true`. Lê resposta texto. Mapeia "Already subscribed." para `alreadySubscribed=true`. Grava evento + atualiza cache no `users` (se `userId`).
- **`unsubscribe()`**: POST `{api_base_url}/unsubscribe` com `email, list, boolean=true`. Idem.
- **`getStatusFromDb()`**: `database-schema` ANTES de implementar! Inspecionar tabela `subscribers` do Sendy (campos esperados: `email`, `list`, `unsubscribed`, `bounced`, `complaint`, `confirmed`, `userID`/`list`). Query: `DB::connection('sendy')->table('subscribers')->where('email', $email)->where('list', config('services.sendy.list_internal_id'))->first()`. Mapear para enum.
- **`getStatusFromApi()`**: POST `/api/subscribers/subscription-status.php`. Mapear texto.
- **Try/catch + log** em TUDO. Falhas retornam `SendyResult::failure()` ou `SendyStatus::NotFound`. **Nunca throw para fora.**

## Spec dos Jobs
- `SubscribeToSendyJob(string $email, ?string $name, NewsletterSubscriptionContext $ctx)` implementa `ShouldQueue`. No `handle()`: chama `SendyService::subscribe()`. Sem retries automáticos (Sendy não é frágil). `tries = 2`, `backoff = [30, 120]`.
- `UnsubscribeFromSendyJob` idem.
- `SyncNewsletterStatusJob(int $userId)`: lê User, chama `SendyService::getStatusFromDb()`, atualiza `users.newsletter_subscribed_at` e `newsletter_synced_at`.
- Todos com `failOnTimeout` e log estruturado no `failed()`.

## Testes
- `tests/Unit/Sendy/SendyServiceTest.php`:
  - `Http::fake()` para subscribe success/already/erro.
  - `isEnabled()=false` → métodos retornam early.
  - `getStatusFromDb()` com `DB::connection('sendy')` fake (usar `Database\Connection` stub ou trait que swap connection para SQLite in-memory durante teste, com tabela `subscribers` mock).
- `tests/Feature/Newsletter/SendyJobsTest.php`:
  - Jobs despacham, chamam service, logam evento, atualizam users (com fake service binding).

## Estratégia de teste para a connection `sendy`
Os testes principais usam SQLite in-memory. Para mockar a connection `sendy`, criar helper em `tests/Pest.php`:
```php
function fakeSendyConnection(): void {
    config()->set('database.connections.sendy', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]);
    Schema::connection('sendy')->create('subscribers', function (Blueprint $t) {
        $t->id();
        $t->string('email');
        $t->integer('list');
        $t->tinyInteger('unsubscribed')->default(0);
        $t->tinyInteger('bounced')->default(0);
        $t->tinyInteger('complaint')->default(0);
        $t->tinyInteger('confirmed')->default(1);
    });
}
```

## Validação no browser
- Nenhuma (sem UI ainda).
- Mas o agente DEVE validar via tinker que `SendyService::activeSubscriberCount()` retorna um número real (smoke test contra Sendy real, com flag temporariamente em `true` local). Reportar o número ao user.

## Critérios de aceitação
- [ ] `php artisan test --compact --filter=Sendy` verde.
- [ ] `php artisan test --compact` (suite completa) sem regressão.
- [ ] Tinker mostra `app(\App\Services\Sendy\SendyService::class)->activeSubscriberCount()` retornando inteiro > 0 (com flag local temporariamente true).
- [ ] Tinker mostra `getStatusFromDb('email-conhecido-na-lista@exemplo.com')` retornando `SendyStatus::Subscribed` para um email que tu apontes.
- [ ] User reverteu flag local para `'0'` antes de avançar.

## Notas de execução
- **PHP**: 8.3.30.
- **Config**: `services.sendy`, connection `sendy`, `SENDY_DB_ENABLED` (default `true`; `false` em dev Mac — leituras via API; testes usam `fakeSendyConnection()`).
- **Smoke test DB prod**: inacessível do Mac (esperado). Validação via 18 testes Pest `--filter=Sendy`.
- **Arquivos**: SendyService, DTOs, enums, 3 jobs, testes Unit+Feature.
- **Validação humana**: 2026-05-20.

---

# FASE 2 — Migrations + Models + Enums (cache local)

## Objetivo
Adicionar persistência local: coluna no `users` para cache do estado de assinatura, e tabela `newsletter_subscription_events` para auditoria/stats.

## Pré-requisitos
- Fase 1 validada.

## Arquivos a criar/tocar
- `database/migrations/{ts}_add_newsletter_columns_to_users_table.php`
- `database/migrations/{ts}_create_newsletter_subscription_events_table.php`
- `app/Models/NewsletterSubscriptionEvent.php`
- `app/Models/User.php` — adicionar casts e accessor `wantsNewsletter`.
- `database/factories/NewsletterSubscriptionEventFactory.php`
- `tests/Feature/Newsletter/NewsletterEventsTest.php`

## Migrations

### `add_newsletter_columns_to_users_table`
```php
Schema::table('users', function (Blueprint $table) {
    $table->timestamp('newsletter_subscribed_at')->nullable()->after('email_verified_at');
    $table->timestamp('newsletter_synced_at')->nullable()->after('newsletter_subscribed_at');
    $table->index('newsletter_subscribed_at');
});
```
Down: drop dos 2 campos e do índice. **NÃO destrutiva em prod** (só ADD COLUMN nullable).

### `create_newsletter_subscription_events_table`
```php
Schema::create('newsletter_subscription_events', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('email')->index();
    $table->string('action', 32);
    $table->string('source', 32);
    $table->string('popup_variant', 8)->nullable();
    $table->string('popup_trigger', 16)->nullable();
    $table->ipAddress('ip')->nullable();
    $table->string('user_agent', 512)->nullable();
    $table->string('referrer', 1024)->nullable();
    $table->string('page_url', 512)->nullable();
    $table->json('meta')->nullable();
    $table->timestamps();
    $table->index(['action', 'source', 'created_at']);
});
```

## Model `NewsletterSubscriptionEvent`
- `$casts = ['meta' => 'array']`.
- `protected $fillable = [...todos os campos exceto id/timestamps]`.
- Scopes: `scopeByAction()`, `scopeBySource()`, `scopeInPeriod($from, $to)`, `scopeSubscriptions()` (action in subscribed|already_subscribed).
- Relation `user(): BelongsTo`.

## Atualização do `User` model
- Adicionar em `casts()`:
```php
'newsletter_subscribed_at' => 'datetime',
'newsletter_synced_at' => 'datetime',
```
- Accessor (não cast, pq depende da coluna):
```php
public function wantsNewsletter(): bool
{
    return $this->newsletter_subscribed_at !== null;
}
```
- Adicionar `'newsletter_subscribed_at', 'newsletter_synced_at'` ao `$fillable`? **NÃO** — apenas o SendyService deve escrever, via `forceFill`.

## Atualizar `SendyService` da Fase 1
- Agora que existem coluna e tabela, ativar a parte do service que grava evento + atualiza User. Adicionar testes específicos.

## Testes
- `tests/Feature/Newsletter/NewsletterEventsTest.php`:
  - Factory cria evento OK.
  - Scopes filtram corretamente.
  - SendyService grava evento e atualiza User após subscribe success.
  - SendyService grava evento `failed` se API retorna erro.
  - SendyService grava `already_subscribed` quando aplicável.

## Validação no browser
- Nenhuma.
- Confirmar via `database-schema` que as colunas estão criadas.

## Critérios de aceitação
- [ ] Migrations rodam local sem erro (`php artisan migrate`).
- [ ] Rollback funciona (`php artisan migrate:rollback --step=2 && php artisan migrate`).
- [ ] `php artisan test --compact --filter=Newsletter` verde.
- [ ] Suite completa sem regressão.

## Notas de execução
_(preencher)_

---

# FASE 3 — Form AJAX em /newsletters

## Objetivo
Primeiro entregável visível: substituir o link externo do Sendy na página `/newsletters` por um form interno AJAX com 3 estados (guest / auth-não-inscrito / auth-inscrito).

## Pré-requisitos
- Fases 1 e 2 validadas.
- Flag `newsletter_integration_enabled` continua em `'0'` (a UI nova renderiza só se flag=true).

## Arquivos a criar/tocar
- `app/Http/Controllers/NewsletterSubscriptionController.php` (novo)
- `app/Http/Requests/NewsletterSubscribeRequest.php` (novo)
- `routes/web.php` — adicionar `POST /newsletter/subscribe`, `DELETE /minha-conta/newsletter` (esse último vira ativo na Fase 5; criar agora só o subscribe).
- `resources/views/front/newsletters.blade.php` — substituir trecho 33-36.
- `tests/Feature/Newsletter/NewslettersPageFormTest.php`

## Spec do `NewsletterSubscribeRequest`
```php
use Illuminate\Validation\Rule;

public function rules(): array {
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => [
            'required',
            'string',
            'max:255',
            Rule::email()
                ->rfcCompliant(strict: false)
                ->validateMxRecord()
                ->preventSpoofing(),
        ],
    ];
}

public function messages(): array {
    return [
        'name.required' => 'Informe o seu nome.',
        'email.required' => 'Informe o seu email.',
        'email' => 'O email informado é inválido.',
    ];
}
```

public function authorize(): bool {
    // Se logado, força que email batenha o do user (evita phishing)
    if (auth()->check()) {
        return $this->input('email') === auth()->user()->email;
    }
    return true;
}
```

## Spec do Controller
```php
final class NewsletterSubscriptionController extends Controller
{
    public function __construct(private SendyService $sendy) {}

    public function subscribe(NewsletterSubscribeRequest $request): JsonResponse
    {
        if (! $this->sendy->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Inscrições temporariamente indisponíveis. Tente em alguns minutos.',
            ], 503);
        }

        $userId = auth()->id();
        $silent = $userId
            ? config('services.sendy.silent_authenticated', true)
            : config('services.sendy.silent_visitor', false);

        $ctx = NewsletterSubscriptionContext::fromRequest(
            $userId ? NewsletterEventSource::PanelToggle : NewsletterEventSource::NewslettersForm,
            $request,
            $userId,
        );
        $ctx->silent = (bool) $silent;

        $result = $this->sendy->subscribe(
            email: $request->string('email')->toString(),
            name: $request->string('name')->toString(),
            ctx: $ctx,
        );

        return response()->json([
            'success' => $result->success,
            'message' => $result->alreadySubscribed
                ? 'Você já está inscrito!'
                : ($result->success ? 'Inscrição realizada! Verifique seu email.' : 'Não foi possível inscrever agora. Tente novamente em instantes.'),
            'already_subscribed' => $result->alreadySubscribed,
        ]);
    }
}
```

## Rota
```php
Route::middleware(['throttle:5,1'])->post('/newsletter/subscribe',
    [NewsletterSubscriptionController::class, 'subscribe'])
    ->name('newsletter.subscribe');
```

## Spec da view `front/newsletters.blade.php`
Substituir o bloco atual (linhas 33-36):
```blade
                    <div class="tw-text-sm tw-text-slate-600">
                        Inscreva-se <a class="tw-text-brand-600 ...">aqui</a> para receber por email.
                    </div>
```

Por (estrutura simplificada — completar com Tailwind do projeto):
```blade
@php
    $isEnabled = \App\Models\SiteSetting::getAsBool('newsletter_integration_enabled');
    $user = auth()->user();
    $isAlreadySubscribed = $user && $user->wantsNewsletter();
@endphp

@if (! $isEnabled)
    {{-- Mantém o link externo antigo como fallback até a Fase 8 --}}
    <div class="tw-text-sm tw-text-slate-600">
        Inscreva-se <a class="tw-text-brand-600 ..." href="https://newsletter.maurolopes.com.br/subscription?f=guJ3cS2Vm7AxAFSQ24hY1x2LOVOrbH44BBFmy4NXDEULQPmQ9VecJ538XJVLM9JbWogaBgUkTuwWL1y8WaWG1w">aqui</a> para receber por email.
    </div>
@elseif ($isAlreadySubscribed)
    <div class="tw-rounded-lg tw-border tw-border-emerald-200 tw-bg-emerald-50 tw-p-4 tw-text-emerald-800">
        <p class="tw-font-medium">Você já está inscrito na newsletter!</p>
        <p class="tw-text-sm tw-mt-1">Para sair, vá em <a href="{{ route('user-panel.profile') }}" class="tw-underline">Minha Conta &gt; Perfil</a>.</p>
    </div>
@else
    <div x-data="newsletterForm()" x-init="init()" class="tw-rounded-lg tw-border tw-border-slate-200 tw-bg-white tw-p-4">
        <form @submit.prevent="submit" class="tw-space-y-3" novalidate>
            @csrf
            @honeypot
            <label class="tw-block">
                <span class="tw-text-sm tw-text-slate-700">Nome</span>
                <input type="text" name="name" required maxlength="255"
                       x-model="name" :readonly="locked"
                       class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-slate-300">
            </label>
            <label class="tw-block">
                <span class="tw-text-sm tw-text-slate-700">Email</span>
                <input type="email" name="email" required maxlength="255"
                       x-model="email" :readonly="locked"
                       class="tw-mt-1 tw-block tw-w-full tw-rounded-md tw-border-slate-300">
            </label>
            <button type="submit" :disabled="loading"
                    class="tw-w-full tw-rounded-md tw-bg-brand-600 tw-px-4 tw-py-2 tw-text-white hover:tw-bg-brand-700 disabled:tw-opacity-50">
                <span x-show="!loading">Quero receber a newsletter</span>
                <span x-show="loading">Inscrevendo…</span>
            </button>
            <p x-show="message" x-text="message" :class="success ? 'tw-text-emerald-700' : 'tw-text-rose-700'" class="tw-text-sm"></p>
        </form>
    </div>
    <script>
        function newsletterForm() {
            return {
                name: @json($user?->name ?? ''),
                email: @json($user?->email ?? ''),
                locked: @json((bool) $user),
                loading: false,
                message: '',
                success: false,
                init() {},
                async submit(event) {
                    this.loading = true;
                    this.message = '';
                    const formData = new FormData(event.target);
                    try {
                        const res = await fetch(@json(route('newsletter.subscribe')), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });
                        const data = await res.json();
                        this.success = !!data.success || !!data.already_subscribed;
                        this.message = data.message;
                    } catch (e) {
                        this.success = false;
                        this.message = 'Erro de rede. Tente em instantes.';
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
@endif
```

## Testes
- `tests/Feature/Newsletter/NewslettersPageFormTest.php`:
  - Flag OFF → view mantém link externo antigo (verifica `https://newsletter.maurolopes.com.br/subscription?f=` no HTML).
  - Flag ON + guest → form renderiza com inputs editáveis.
  - Flag ON + user inscrito → mensagem "Você já está inscrito".
  - Flag ON + user não inscrito → form pre-preenchido com readonly.
  - POST `/newsletter/subscribe` válido → 200 JSON success, evento gravado, SendyService chamado (fake).
  - POST com flag OFF → 503.
  - POST com user logado e email diferente → 403.
  - Rate limit 5/min → 6º request 429.
  - Honeypot acionado → request rejeitado (Spatie já lida com isso, só validar que continua funcionando).

## Validação no browser
1. Confirmar flag `newsletter_integration_enabled` em `'0'`: ir a `/newsletters` → deve aparecer o link externo antigo. **Confirmar que não regrediu.**
2. Setar flag para `'1'` localmente via tinker.
3. Como guest, ir a `/newsletters`: form com nome/email editáveis. Submeter com email teste. Receber feedback success.
4. Logar com user que já está no Sendy: ver "Você já está inscrito!".
5. Logar com user novo (não no Sendy): ver form pre-preenchido + readonly. Submeter. Receber success. Conferir no painel do Sendy que o email foi adicionado.
6. **IMPORTANTE**: voltar a flag para `'0'` antes do commit.

## Critérios de aceitação
- [x] Testes Pest da fase verdes (`--filter=NewslettersPageForm` + `NewsletterIntegrationSettings`).
- [x] Suite completa sem regressão.
- [x] Validação no browser concluída (user confirmou).
- [x] Flag OFF = nenhuma UI de inscrição (tudo ou nada; sem link externo).

## Notas de execução
- **UI:** form compacto à direita (`justify-between`); botão «Receba»; guest only; logado inscrito «Você está inscrito!»; logado não inscrito link AJAX «Receba atualização semanal».
- **Sync:** `CampaignsPageController` + `SendyService::syncUserSubscriptionState()` consulta API e atualiza cache `users`.
- **Filament:** `NewsletterIntegrationSettings` para kill switch (extra).
- **Commit:** `4253b7c` em `master`.
- **Prod:** migrate automático; `SiteSettingsSeeder --force` feito pelo user.

---

# FASE 4 — Auto-inscrição no registro + Google OAuth + toast (variante B)

## Objetivo
Inscrição automática na newsletter para novos usuários (email e Google), com toast informativo; falha no Sendy não bloqueia o cadastro.

## Pré-requisitos
- Fases 3 e 5 validadas.

## Tarefa transversal (início da Fase 4)

Refatorar `NewsletterSubscribeRequest` para `Rule::email()` (Laravel 12) conforme secção **Validação de email** em CONVENÇÕES. Atualizar testes em `NewslettersPageFormTest` se mensagens de erro mudarem.

## Arquivos entregues
- `app/Services/Sendy/NewUserNewsletterSubscription.php`
- `app/Actions/Fortify/CreateNewUser.php`
- `app/Http/Controllers/GoogleAuthController.php`
- `resources/views/partials/newsletter-registration-toast.blade.php`
- `resources/views/layouts/app.blade.php`
- `app/Http/Requests/NewsletterSubscribeRequest.php` (Rule::email)
- `tests/Feature/Newsletter/RegistrationNewsletterTest.php`
- `tests/Feature/Newsletter/GoogleOAuthOptInTest.php`

## Spec implementada (variante B — acordada com o user)

- **Sem checkbox** no registro.
- **`NewUserNewsletterSubscription`**: chamada síncrona a `SendyService::subscribe()` após criar o user (nunca quebra o cadastro).
- **Toast** (`partials/newsletter-registration-toast.blade.php` em `layouts/app.blade.php`):
  - `subscribed` — sucesso ou «Already subscribed.» (já na lista).
  - `invite` — falha Sendy; texto convida a inscrever via Minha Conta > Perfil.
- Sessão `newsletter.registration_toast` com `session()->pull()` na primeira página que renderiza o partial (sobrevive à tela de verificação de email).
- **Google**: só novos users (`$isNewUser`); re-login não dispara subscribe nem toast.

## Testes
- `NewslettersPageFormTest`: email inválido → 422 JSON; `Rule::email()` no request.
- `RegistrationNewsletterTest`: subscribe OK / já inscrito / falha Sendy / flag OFF; toast HTML; toast na `verification.notice`.
- `GoogleOAuthOptInTest`: novo user (OK, já inscrito, falha); re-login sem toast; flag OFF.

## Validação no browser
1. Flag = `'1'`. Registrar conta nova → toast `subscribed` (ou na `/email/verify`) + email no Sendy.
2. Simular falha Sendy → registo conclui + toast `invite` com link ao Perfil.
3. Login Google (conta nova) → toast em `/minha-conta` + email no Sendy.
4. Login Google (conta existente) → sem toast.
5. Flag = `'0'` → sem subscribe nem toast.
6. User validou 2026-05-21 (novo user inscrito + toast OK).

## Critérios de aceitação
- [x] `NewsletterSubscribeRequest` usa `Rule::email()` (RFC + MX + anti-spoof).
- [x] Testes da fase verdes (22 testes newsletter da fase + suite 622 passed).
- [x] Suite completa sem regressão.
- [x] Validação browser concluída (user confirmou 2026-05-21; verificação de email em dev não testada).
- [x] Flag OFF: sem subscribe nem toast.

## Notas de execução
- **Variante B** (sem checkbox): `NewUserNewsletterSubscription` + toast `subscribed`/`invite`.
- **Arquivos**: `CreateNewUser`, `GoogleAuthController`, `NewUserNewsletterSubscription`, partial toast, `layouts/app.blade.php`.
- **Testes**: `RegistrationNewsletterTest`, `GoogleOAuthOptInTest`, caso email inválido em `NewslettersPageFormTest`.
- **Pint**: OK. **Suite**: 622 passed.
- **Browser**: user confirmou toast + inscrição com novo user (2026-05-21).

---

# FASE 5 — Toggle Livewire no painel

## Objetivo
Permitir ao user logado entrar/sair da lista pelo painel, com confirm() no opt-out.

## Pré-requisitos
- Fase 4 validada.

## Arquivos a criar/tocar
- `app/Livewire/NewsletterToggle.php`
- `resources/views/livewire/newsletter-toggle.blade.php`
- `resources/views/user-panel/profile.blade.php` — adicionar card com `<livewire:newsletter-toggle />`.
- `routes/web.php` — `DELETE /minha-conta/newsletter` se o Livewire não cobrir (depende da implementação).
- `tests/Feature/Newsletter/PanelToggleTest.php`

## Spec do componente
```php
final class NewsletterToggle extends Component
{
    public bool $subscribed = false;
    public bool $loading = false;
    public ?string $message = null;
    public ?string $messageType = null; // success | error

    public function mount(SendyService $sendy): void
    {
        $this->subscribed = auth()->user()?->wantsNewsletter() ?? false;
    }

    public function subscribe(SendyService $sendy): void
    {
        if (! $sendy->isEnabled()) { $this->setError('Inscrições indisponíveis no momento.'); return; }
        $this->loading = true;
        $user = auth()->user();
        $result = $sendy->subscribe(
            $user->email,
            $user->name,
            new NewsletterSubscriptionContext(
                source: NewsletterEventSource::PanelToggle,
                userId: $user->id,
                ip: request()->ip(),
                userAgent: substr((string) request()->userAgent(), 0, 512),
                silent: (bool) config('services.sendy.silent_authenticated', true),
            ),
        );
        $this->subscribed = $result->success || $result->alreadySubscribed;
        $this->message = $result->success ? 'Inscrição confirmada!' : 'Não foi possível inscrever agora.';
        $this->messageType = $result->success ? 'success' : 'error';
        $this->loading = false;
    }

    public function unsubscribe(SendyService $sendy): void
    {
        // mesma estrutura, chama $sendy->unsubscribe()
    }

    public function render(): View
    {
        return view('livewire.newsletter-toggle');
    }

    private function setError(string $msg): void { ... }
}
```

## Spec da view do Livewire
```blade
<div>
    @if (! \App\Models\SiteSetting::getAsBool('newsletter_integration_enabled'))
        <p class="tw-text-sm tw-text-slate-500">As preferências de newsletter estarão disponíveis em breve.</p>
    @else
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-font-medium">Email semanal de atualização em Teses &amp; Súmulas</p>
                <p class="tw-text-sm tw-text-slate-500">
                    {{ $subscribed ? 'Você está inscrito.' : 'Você não está inscrito.' }}
                </p>
            </div>

            @if ($subscribed)
                <button type="button" wire:loading.attr="disabled" wire:click="$dispatch('confirm-unsubscribe')"
                        x-on:confirm-unsubscribe.window="if (confirm('Tem certeza de que quer parar de receber email semanal de atualização em teses e súmulas dos tribunais superiores?')) { $wire.unsubscribe() }"
                        class="tw-rounded-md tw-border tw-border-rose-300 tw-bg-white tw-px-3 tw-py-1.5 tw-text-sm tw-text-rose-700 hover:tw-bg-rose-50">
                    Sair da lista
                </button>
            @else
                <button type="button" wire:loading.attr="disabled" wire:click="subscribe"
                        class="tw-rounded-md tw-bg-brand-600 tw-px-3 tw-py-1.5 tw-text-sm tw-text-white hover:tw-bg-brand-700">
                    Entrar na lista
                </button>
            @endif
        </div>
        @if ($message)
            <p class="tw-mt-2 tw-text-sm {{ $messageType === 'success' ? 'tw-text-emerald-700' : 'tw-text-rose-700' }}">
                {{ $message }}
            </p>
        @endif
        <div wire:loading wire:target="subscribe,unsubscribe" class="tw-mt-1 tw-text-xs tw-text-slate-500">Atualizando…</div>
    @endif
</div>
```

Texto do confirm exato conforme pedido pelo user:
> "Tem certeza de que quer parar de receber email semanal de atualização em teses e súmulas dos tribunais superiores?"

## Inserção no profile
Em `resources/views/user-panel/profile.blade.php` antes do card "Atualizar senha":
```blade
<div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
    <h2 class="tw-text-lg tw-font-semibold tw-mb-4">Newsletter</h2>
    <livewire:newsletter-toggle />
</div>
```

## Testes
- Livewire test: subscribe muda estado + chama service.
- Livewire test: unsubscribe muda estado + chama service.
- Livewire test: flag OFF → estado neutro, botões não chamam Sendy.
- Tentar tocar componente sem auth → 403/redirect (middleware `auth` no group).

## Validação no browser
1. Flag = `'1'`. Logar com user não inscrito → ver botão "Entrar na lista". Clicar → muda para "Sair da lista". Confirmar no painel do Sendy.
2. Clicar "Sair da lista" → confirm aparece com texto exato. Cancelar → não muda. Confirmar → muda para "Entrar na lista".
3. Flag = `'0'` → mensagem placeholder "estarão disponíveis em breve".
4. Voltar flag a `'0'` antes do commit.

## Critérios de aceitação
- [x] Testes da fase verdes (`--filter=PanelToggle`).
- [x] Suite completa sem regressão.
- [x] Validação browser (user confirmou subscribe/unsubscribe + sync).

## Notas de execução
- **Adiantada** na mesma entrega que Fase 3 (commit `4253b7c`).
- `NewsletterToggle` Livewire em `user-panel/profile.blade.php`; sync no `mount()` via API.
- Confirm opt-out com texto exato do plano.

---

# FASE 6 — Popup visitante configurável

## Objetivo
Estimular visitantes (não logados) a se inscreverem via popup com 3 gatilhos selecionáveis, A/B test e cookies de frequência. Admin configura tudo no Filament.

## Pré-requisitos
- Fase 5 validada.

## Arquivos a criar/tocar
- `app/Filament/Pages/NewsletterPopupSettings.php` + view.
- `resources/views/partials/newsletter-popup.blade.php`
- `resources/views/front/base.blade.php` — incluir partial (só para guest).
- `app/Http/Controllers/NewsletterSubscriptionController.php` — adicionar método `trackEvent()` para impressões/dismiss.
- `routes/web.php` — `POST /newsletter/event` (rate limited).
- `tests/Feature/Newsletter/PopupConfigTest.php`
- `tests/Feature/Newsletter/PopupEventsTest.php`

## SiteSettings utilizadas
- `newsletter_popup_enabled` (bool, default false)
- `newsletter_popup_trigger` (timer|exit_intent|scroll, default timer)
- `newsletter_popup_delay_seconds` (int 5-120, default 20)
- `newsletter_popup_scroll_percent` (int 25-95, default 50)
- `newsletter_popup_frequency_days` (int 1-90, default 14)
- `newsletter_popup_variant_a_title` (default "Acompanhe as decisões mais importantes")
- `newsletter_popup_variant_a_body` (default "Receba semanalmente um resumo dos novos repetitivos e súmulas dos tribunais superiores.")
- `newsletter_popup_variant_a_cta` (default "Quero receber")
- `newsletter_popup_variant_b_enabled` (bool default false)
- `newsletter_popup_variant_b_title`/`_body`/`_cta` (defaults vazios)
- `newsletter_popup_split_percent` (0-100, default 50 — % do tráfego para B se enabled)

## Filament page (`NewsletterPopupSettings`)
Espelhar padrão de `app/Filament/Pages/MeteredWallSettings.php`. Formulário com:
- Toggle `enabled`
- Select `trigger` (3 opções)
- TextInput numérico `delay_seconds` (visible se trigger=timer)
- TextInput numérico `scroll_percent` (visible se trigger=scroll)
- TextInput numérico `frequency_days`
- Section "Variante A" — title/body/cta
- Section "Variante B" — toggle enabled + title/body/cta + split_percent
- Botão "Salvar" chama `SiteSetting::set()` para cada chave.

## Render no front (`partials/newsletter-popup.blade.php`)
```blade
@php
    $flagOn = \App\Models\SiteSetting::getAsBool('newsletter_integration_enabled');
    $popupOn = \App\Models\SiteSetting::getAsBool('newsletter_popup_enabled');
@endphp

@guest
    @if ($flagOn && $popupOn)
        @include('partials.newsletter-popup-content')
    @endif
@endguest
```

Conteúdo (`newsletter-popup-content`) com Alpine, escolhendo variante via cookie, gatilhos via `setTimeout` (timer), `mouseleave` (exit-intent) ou `scroll` (IntersectionObserver). Frequência: cookie `newsletter_popup_dismissed_until` com timestamp.

Variantes lidas do `SiteSetting`.

## Endpoint de tracking
```php
// route
Route::middleware(['throttle:30,1'])->post('/newsletter/event',
    [NewsletterSubscriptionController::class, 'trackEvent'])->name('newsletter.event');
```

```php
public function trackEvent(Request $request): JsonResponse {
    $validated = $request->validate([
        'action' => ['required', Rule::in(['impression', 'dismissed'])],
        'variant' => ['nullable', 'in:A,B'],
        'trigger' => ['nullable', 'in:timer,exit_intent,scroll'],
    ]);
    NewsletterSubscriptionEvent::create([
        'email' => '', // sem email em impression/dismiss
        'action' => $validated['action'],
        'source' => NewsletterEventSource::Popup->value,
        'popup_variant' => $validated['variant'] ?? null,
        'popup_trigger' => $validated['trigger'] ?? null,
        'ip' => $request->ip(),
        'user_agent' => substr((string) $request->userAgent(), 0, 512),
        'referrer' => substr((string) $request->headers->get('referer'), 0, 1024),
        'page_url' => substr((string) $request->headers->get('referer'), 0, 512),
    ]);
    return response()->json(['ok' => true]);
}
```

## Inserção no `base.blade.php`
Antes do `</body>`, depois do `livewireScripts`:
```blade
@include('partials.newsletter-popup')
```

## Testes
- `PopupConfigTest`:
  - Settings desabilitado → popup não aparece no HTML.
  - Settings habilitado + flag ON + guest → partial incluído.
  - Auth user → popup nunca aparece.
- `PopupEventsTest`:
  - POST `/newsletter/event` impression → linha em `newsletter_subscription_events`.
  - Rate limit 30/min.
  - Variant inválida → 422.

## Validação no browser
1. Configurar tudo no Filament `/painel` → Configurações → Newsletter Popup. Habilitar.
2. Acessar site como guest (em aba anônima) → após delay/scroll/exit-intent, popup aparece.
3. Dismiss → confirma cookie setado → não aparece mais por X dias.
4. Submeter inscrição → cookie `newsletter_subscribed` setado, evento `subscribed`/`popup` em `newsletter_subscription_events`.
5. Desabilitar popup no Filament → popup não aparece mais.
6. Voltar flag global para `'0'` antes do commit.

## Critérios de aceitação
- [x] Testes da fase verdes (`--filter=Popup` → 17 passed).
- [x] Validação browser (user confirmou 2026-05-21).
- [x] Gatilhos timer / scroll / exit-intent testados no Filament.
- [x] Reset de espera e reset completo (testes) no Filament.

## Notas de execução
- **Filament:** `NewsletterPopupSettings` em `/admin/painel/newsletter-popup-settings` (sort 53). Select gatilho com `->live()` (timer → segundos; scroll → %; exit-intent → sem campo extra). Botões **Resetar espera (X dias)** e **Reset completo (testes)** via `newsletter_popup_dismiss_reset_epoch` / `newsletter_popup_subscribed_reset_epoch`.
- **Front:** `partials/newsletter-popup.blade.php` + `newsletter-popup-content.blade.php`; include em `front/base.blade.php`. UI: cabeçalho gradiente brand, CTA brick, overlay escuro; X não sobrepõe título.
- **API:** `POST /newsletter/event` (30/min); `trackEvent()` → `impression`/`dismissed`. `POST /newsletter/subscribe` com `from_popup=1` → `source=popup`.
- **Cookies:** `newsletter_popup_dismissed_until` + `newsletter_popup_dismiss_epoch`; `newsletter_subscribed` + `newsletter_popup_subscribed_epoch`; `newsletter_popup_variant` (A/B).
- **Testes:** `PopupConfigTest`, `PopupEventsTest`.
- **Deploy:** sem migration nova nesta fase; **sem seeder obrigatório** para chaves popup (defaults no `mount()` Filament e no Blade). Ver secção «Deploy Fase 6» no `NEWSLETTER_SENDY_EXECUTION_PROMPT.md`.

---

# FASE 7 — Filament Stats + comando sync + schedule

## Objetivo
Visibilidade de stats no painel admin + reconciliação periódica do cache local.

## Pré-requisitos
- Fase 6 validada.

## Arquivos a criar/tocar
- `app/Filament/Pages/NewsletterStats.php` (read-only)
- `app/Filament/Widgets/NewsletterOverviewStats.php`
- `app/Filament/Widgets/NewsletterDailyChart.php`
- `app/Filament/Widgets/NewsletterBySourceChart.php`
- `app/Filament/Widgets/NewsletterPopupAbStats.php`
- `app/Console/Commands/SyncNewsletterStatus.php` (`newsletter:sync` com `--all`)
- `app/Console/Kernel.php` — schedule a cada 6h.
- `tests/Feature/Newsletter/SyncCommandTest.php`
- `tests/Feature/Newsletter/NewsletterStatsPageTest.php`

## Spec da Filament page
Page sem form, com 4 widgets. Header com botão "Sincronizar agora" que dispara o `SyncNewsletterStatus` command.

Widgets:
- `OverviewStats`: total inscritos atuais (Sendy DB count via `SendyService::activeSubscriberCount()`), novos nos últimos 7d, taxa A/B do popup.
- `DailyChart`: linha com inscrições por dia, últimos 30d.
- `BySourceChart`: pizza com % por source.
- `PopupAbStats`: tabela A vs B (impressões, conversões, taxa).

## Spec do comando
```bash
php artisan newsletter:sync         # processa users com newsletter_synced_at < now()-6h
php artisan newsletter:sync --all   # processa todos os users
php artisan newsletter:sync --user=123  # apenas um user
```
Estratégia: para cada user, consulta Sendy DB (única query batch via `whereIn`), atualiza `newsletter_subscribed_at` e `newsletter_synced_at`. Logs ao fim.

## Schedule em `app/Console/Kernel.php`
```php
$schedule->command('newsletter:sync')->everySixHours()->withoutOverlapping();
```

## Testes
- `SyncCommandTest`:
  - Comando atualiza colunas dos users que estão na lista do Sendy (fake DB).
  - `--all` processa todos.
  - User não encontrado no Sendy → `newsletter_subscribed_at` fica null.
- `NewsletterStatsPageTest`:
  - Admin acessa página → 200.
  - Não-admin → 403.
  - Widgets renderizam.

## Validação no browser
1. `/painel` → Newsletter Stats → ver gráficos populados.
2. Rodar `php artisan newsletter:sync --all` manualmente → ver users sendo atualizados.
3. Conferir schedule registrado (`php artisan schedule:list`).

## Critérios de aceitação
- [ ] Testes verdes.
- [ ] Suite completa sem regressão.
- [ ] Stats page funcional para admin.
- [ ] Schedule registrado.

## Notas de execução
_(preencher)_

---

# FASE 8 — PROJECT_BRIEF.md + Pint + ligar flag + suite final

## Objetivo
Documentar, formatar, ligar a flag global em prod e fechar.

## Pré-requisitos
- Fases 0-7 validadas.

## Arquivos a tocar
- `PROJECT_BRIEF.md` — nova seção "Newsletter (Sendy)".

## Tarefas
1. Adicionar seção em `PROJECT_BRIEF.md`:
   - Model: `NewsletterSubscriptionEvent`
   - Service: `App\Services\Sendy\SendyService`
   - Jobs: `Subscribe/UnsubscribeToSendyJob`, `SyncNewsletterStatusJob`
   - Comando: `newsletter:sync`
   - Filament pages: `NewsletterPopupSettings`, `NewsletterStats`
   - Feature flag: `newsletter_integration_enabled` (SiteSetting)
   - Variáveis de env relevantes
2. `vendor/bin/pint --dirty --format agent`.
3. `php artisan test --compact` — toda a suite verde.
4. Documentar processo de "ligar em prod":
   - Setar `newsletter_integration_enabled='1'` via Filament `/painel`.
   - Rodar `php artisan newsletter:sync --all` uma vez.

## Critérios de aceitação
- [ ] `PROJECT_BRIEF.md` atualizado.
- [ ] Pint passou.
- [ ] Suite completa verde.
- [ ] User confirmou que processo de ativação em prod está claro.

## Notas de execução
_(preencher)_

---

# CASOS DE BORDA CONTEMPLADOS

- **Sendy fora do ar**: todo método do service em `try/catch`. Falhas logadas. Site continua funcionando. Toggle/form mostra erro inline.
- **Flag global desligada**: nenhuma chamada externa, UI nova esconde-se, fallbacks antigos (link externo em `/newsletters`) seguem ativos.
- **Email mudado no perfil (Fortify)**: sync diário reconcilia. (Melhoria futura: listener em `UserProfileUpdated` que faz unsubscribe + subscribe.)
- **Hard bounce/complained**: API retorna erro específico, gravamos `failed` no evento, UI sugere contato.
- **Visitante já inscrito** (mesmo email vindo do form externo): API retorna "Already subscribed.", mapeamos para `alreadySubscribed=true`, UI mostra "Você já está inscrito".
- **Popup com cookie de dismiss**: respeita `frequency_days`.
- **A/B test**: cookie persiste variante por sessão para consistência.
- **Honeypot**: aplicado em todos os forms públicos.
- **Rate limit**: 5/min subscribe, 30/min event tracking.

# CENÁRIOS NÃO CONTEMPLADOS (combinar com user para futuro)

- Custom fields no Sendy (ex.: `tes_user_id`, `registered_at`).
- Re-engagement de unsubscribed após N meses.
- Multi-listas temáticas.
- Página própria de "minhas preferências de privacidade" (LGPD).
- Webhooks Sendy → T&S (Sendy não emite nativos).
- i18n (tudo em pt-br).
- Listener em `UserProfileUpdated` para email mudado.

# OPERACIONAL PRÉ-DEPLOY

Já feito (no `.env` local e prod):
- `SENDY_API_TOKEN`, `SENDY_API_BASE_URL`, `SENDY_BRAND_ID`, `SENDY_LIST_ID`, `SENDY_LIST_INTERNAL_ID`, `SENDY_DB_HOST`, `SENDY_DB_PORT`, `SENDY_DB_NAME`, `SENDY_DB_USER`, `SENDY_DB_PASSWORD`, `SENDY_SILENT_AUTHENTICATED`, `SENDY_SILENT_VISITOR`.
- `.env.example` espelhando todas as 12 variáveis (linhas 79-93).

Ainda a fazer (no início da Fase 0 / 1):
1. Confirmar que `SENDY_LIST_ID` (hash) está preenchido em ambos os ambientes.
2. Confirmar que `SENDY_LIST_INTERNAL_ID` = `2` em ambos.
3. Confirmar que `SENDY_DB_USER` (`forge_sendyuser`) tem apenas `SELECT` na DB do Sendy (recomendado por segurança).
4. Smoke test no início da Fase 1 via tinker: `app(SendyService::class)->activeSubscriberCount()` retorna inteiro > 0.

Pós Fase 8 (operação manual em prod):
- Setar `newsletter_integration_enabled='1'` em Filament `/painel`.
- Rodar `php artisan newsletter:sync --all` uma vez (popular cache local) — comando criado na **Fase 7**.

## Deploy automático (push `master`) — Fases 1–6 acumuladas

O Vito já executa `migrate --force` e `SiteSettingsSeeder --force`. Para este push:

| Item | Necessário? |
|------|-------------|
| **Migrations** | Só se ainda não correram em prod: `add_newsletter_columns_to_users`, `create_newsletter_subscription_events`. O deploy habitual aplica pendentes. |
| **Seeder** | **Não obrigatório** para popup. `SiteSettingsSeeder` só garante `newsletter_integration_enabled=0` via `firstOrCreate` (não sobrescreve valor existente). Chaves do popup **não** estão no seeder — defaults no Filament/Blade até o admin salvar. |
| **`.env` Sendy** | Já deve estar em prod (Fases 0–1). |
| **Pós-deploy manual** | Filament: ligar integração e popup se quiseres em prod; configurar gatilho/textos. `newsletter:sync` só após Fase 7. |
