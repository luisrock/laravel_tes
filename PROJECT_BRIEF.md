# Teses & Súmulas — Briefing para IA

> Documento compacto para dar contexto rápido a modelos de IA sobre objetivos do site e arquitetura. Para regras de codificação detalhadas (Laravel, Pint, Pest, Tailwind, etc.) ver `AGENTS.md` / `CLAUDE.md`. Para detalhes de API e histórico ver `README.md`.

## 1. Objetivo do site

**T&S — Teses e Súmulas** (`tesesesumulas.com.br`) é um motor de busca jurídica que reúne, em um único lugar, **súmulas, teses (repetitivos/repercussão geral) e enunciados** dos principais tribunais e órgãos brasileiros. Foco: ajudar advogados, magistrados, MP, defensores e estudantes a localizar precedentes para petições, aulas, decisões e estudo.

Tribunais/órgãos cobertos: **STF, STJ, TST, TNU, TCU, CARF, FONAJE/CNJ, CEJ/CJF**. Mapa em `config/tes_constants.php` (`lista_tribunais`).

Funcionalidades-chave:
- Busca por termo (única ou por tribunal), com operadores `AND/OR/NOT`, aspas, frases curtas, normalização e cache.
- Páginas individuais de súmula/tese e listagens por tribunal.
- **Temas** (`pesquisas`) — agregação de súmulas+teses por keyword (SEO).
- **Análise de Precedente com IA** ("Decifrando a Tese"): seções geradas para teses do STF/STJ, com PDFs de acórdãos (S3).
- **Quizzes** jurídicos públicos + banco de perguntas reutilizáveis.
- **Coleções** privadas/públicas (usuário salva súmulas/teses/temas em listas).
- **Newsletters** (campanhas publicadas).
- **Editable Contents** (páginas estáticas como `precedentes-vinculantes-cpc`, blocos da home).
- **Assinaturas Stripe** (atualmente OFF: `ENABLE_SUBSCRIPTIONS=false`), com tiers PRO/PREMIUM.
- **API REST** com Bearer Token para integração (extensão Chrome usa endpoints sem auth).

## 2. Stack

PHP 8.3.30 · Laravel 12 · MySQL (prod) / SQLite in-memory (testes) · Tailwind v3 + Vite · Blade · Livewire 3 (Coleções) · Filament 4 (admin auxiliar em `/painel`) · Spatie Permission 6 · Fortify 1 · Cashier 15 (Stripe) · Socialite 5 (Google) · Pest 3 + PHPUnit 11 · Pint 1 · Laravel Boost 2 (MCP habilitado).

Estrutura **Laravel 10 legada** (não migrada para a nova): middleware em `app/Http/Kernel.php`, exceções em `app/Exceptions/Handler.php`, schedule em `app/Console/Kernel.php`. NÃO mexer em `bootstrap/app.php`.

## 3. Ambientes

- **Dev**: Laravel Valet em `https://teses.test` · DB `forge_tes` (MySQL local).
- **Prod**: `https://tesesesumulas.com.br` · servidor `ssh vito@15.229.244.115`, path `/home/vito/tesesesumulas.com.br`.
- **Deploy**: automático via Vito Deploy após `git push` (branch principal). Script roda `composer install`, `npm ci && npm run build`, `php artisan migrate --force`, seeder `RolesAndPermissionsSeeder`, `optimize:clear`, `config:cache`, `view:cache`. Script só editável na UI web do Vito.

## 4. Domínio — modelos principais (`app/Models/`)

| Modelo | Tabela | Função |
|---|---|---|
| `User` | `users` | Auth (Fortify+Socialite), Cashier billable, Spatie roles, FilamentUser; cache `newsletter_subscribed_at` / `newsletter_synced_at` (Sendy). |
| `Quiz`, `Question`, `QuestionOption`, `QuestionTag`, `QuizCategory`, `QuizAttempt`, `QuizAnswer` | `quizzes`, `questions`, ... | Sistema de quizzes (N:N quiz↔questions via `quiz_question`). |
| `Collection`, `CollectionItem` | `collections`, `collection_items` | Coleções privadas/públicas do usuário; item polimórfico por `(content_type, content_id, tribunal)`. |
| `TeseAcordao` | `tese_acordaos` | PDFs de acórdãos relacionados a teses (S3). |
| `TeseAnalysisSection`, `TeseAnalysisJob` | `tese_analysis_sections`, `tese_analysis_jobs` | Análise IA "Decifrando a Tese" (seções por tese + jobs de geração). |
| `AiModel` | `ai_models` | Catálogo de modelos de IA disponíveis para geração. |
| `EditableContent` | `editable_contents` | Páginas/blocos editáveis (home, precedentes, toggles de visibilidade). |
| `Newsletter` | `newsletters` | Campanhas publicadas (arquivo editorial). |
| `NewsletterSubscriptionEvent` | `newsletter_subscription_events` | Auditoria de inscrições/opt-out/impressões do popup (Sendy). |
| `SiteSetting` | `site_settings` | Settings chave-valor genéricos (inclui kill switch da newsletter). |
| `ContentView` | `content_views` | Tracking de visualizações para metered wall. |
| `PlanFeature` | `plan_features` | Mapeia `stripe_product → feature_key` (paywall). |
| `RefundRequest` | `refund_requests` | Solicitações de estorno. |
| `StripeWebhookEvent` | `stripe_webhook_events` | Idempotência de webhooks. |

**Tabelas legadas (sem Eloquent)**: `pesquisas` (temas), `stf_sumulas`, `stf_teses`, `stj_sumulas`, `stj_teses`, `tst_sumulas`, `tst_teses`, `tnu_sumulas`, `tnu_teses`, `tnu_questoesdeordem`, `carf_sumulas`, `fonaje_civ_sumulas` / `cri_sumulas` / `faz_sumulas`, `cej_sumulas`. Consultadas via DB facade / serviços de busca.

## 5. Roles & Permissions (Spatie)

Seeder: `RolesAndPermissionsSeeder`.

Permissões: `view_ai_analysis`, `download_acordaos`, `search`, `use_ai`, `manage_all`, `manage_users`, `ad_free`.

| Role | Permissões |
|---|---|
| `admin` | TODAS |
| `registered` (usuário grátis) | `search`, `ad_free`, `view_ai_analysis`, `download_acordaos` |
| `subscriber` | `search`, `view_ai_analysis`, `download_acordaos`, `ad_free` |
| `premium` | acima + `use_ai` |

Hoje o conteúdo de IA está com **registerwall** (basta estar logado). Remover `view_ai_analysis` de `registered` reativa paywall. Admin de site: `hasRole('admin')`.

## 6. Rotas principais (`routes/web.php` + `api.php`)

Front público:
- `/` → `SearchPageController` (home + busca).
- `/temas`, `/tema/{slug}` → temas.
- `/sumulas/{trib}` e `/sumula/{trib}/{n}` (trib: stf|stj|tst|tnu).
- `/teses/{trib}` e `/tese/{trib}/{n}`.
- `POST /tese/{tribunal}/{tese_id}/resumir-ia` (enfileira análise IA, requer auth).
- `/quizzes`, `/quiz/{slug}`, `/quiz/{slug}/resultado`.
- `/newsletters`, `/newsletter/{slug}`; `POST /newsletter/subscribe`, `POST /newsletter/event` (popup + form AJAX, rate limited).
- `/colecoes` (diretório público), `/colecoes/{username}/{slug}` (coleção pública).
- `/contato`, `/termos`, `/privacidade`.
- `/assinar*` (Stripe Checkout — flag-gated por `ENABLE_SUBSCRIPTIONS`).

Área do usuário (`/minha-conta`):
- Dashboard, perfil, histórico, assinatura, estorno, coleções (CRUD).

Auth (Fortify): login/registro/2FA/reset. Google OAuth: `/auth/google`.

Admin (`/admin/*`, middleware `admin_access`):
- `roles`, `permissions`, `users`, `temas`, `quizzes`, `questions`, `acordaos`, `content/{slug}/edit`.

Filament secundário em `/painel` (auth via `canAccessPanel`): `UserResource`, `PlanFeatureResource`, `RefundRequestResource`, pages `CollectionSettings`, `MeteredWallSettings`, `NewsletterIntegrationSettings`, `NewsletterPopupSettings`, `SiteStats` (URL legada `/newsletter-stats` redireciona), widgets de subscriptions.

API (`routes/api.php`):
- Públicas (sem auth): `POST /api/`, `POST /api/{trib}.php` (compat extensão Chrome), `POST /api/unified-search`.
- Bearer Token (`bearer.token`): `GET/POST/DELETE /api/sumula|tese/{trib}/{n}`, `/api/random-themes`, `/api/newsletters`, CRUD de `/api/quizzes` e `/api/questions` (+ `/bulk` para criação em lote por IA).

## 7. Serviços de busca (`app/Services/Search*`)

Refatorado em Mar/2026 — funções globais antigas em `bootstrap/tes_functions.php` são fachadas que delegam às classes:

- `SearchQueryParser` — parser AND/OR/NOT, aspas, normalização.
- `SearchDatabaseService` — execução FULLTEXT MySQL.
- `SearchCacheManager` — cache 1h por tribunal (tags).
- `SearchTribunalRegistry` + `SearchTribunalConfig` — config tipada por tribunal.
- `SearchTribunalResult` + `SearchResultSection` — DTOs tipados de resultado.

Buscas FULLTEXT são MySQL-only. Em SQLite (testes) podem 500 — testes usam helper `assertRouteResponds()`.

Demais serviços (`app/Services/`):
- `AcordaoUploadService` (S3), `ContentViewService` (metered wall), `CollectionService`, `StripeService`, `SubscriptionService`.
- **Sendy (newsletter):** `Sendy/SendyService` (API + DB readonly), `Newsletter/NewsletterPopupVisibility`, `Newsletter/SiteMetrics`; jobs `SubscribeToSendyJob`, `UnsubscribeFromSendyJob`, `SyncNewsletterStatusJob`; comando `newsletter:sync` (schedule 6h em `Console/Kernel.php`).

## 8. Frontend

- Tailwind v3 (`tailwind.config.js`) + Vite (`vite.config.mjs`) + PostCSS.
- Layouts em `resources/views/layouts/`, páginas em `resources/views/front/`, admin em `views/admin/`, user panel em `views/user-panel/`, coleções em `views/colecoes/` e `views/livewire/`.
- Build prod: `npm run build`. Dev: `npm run dev` ou `composer run dev`.
- Cor primária da marca: bordô `#912F56` (ver `tailwind.config.js`).

## 9. Testes (Pest 3)

- Comandos: `php artisan test --compact` ou `--filter=Nome`.
- DB padrão: **SQLite in-memory** (`phpunit.xml`). Testes FULLTEXT em `tests/MySQL/` rodam com `php artisan test -c phpunit.mysql.xml` (banco `forge_tes_test`).
- Helpers em `tests/Pest.php`: `createAdminUser()`, `createPublishedQuiz()`, `createSubscribedUser()`, `assertRouteResponds()`.
- Cobertura: >240 testes (Arch, Auth, Smoke, Quiz, Subscription, Search*, Webhook, API, Filament, etc.).
- **Regra do projeto**: toda mudança precisa de teste novo ou atualizado.

## 10. Configurações importantes

- `config/teses.php` — toolbar de teste (papéis/metered wall).
- `config/tes_constants.php` — registry de tribunais (FONTE ÚNICA).
- `config/subscription.php` — flag `enabled`, tier_product_ids, features.
- `config/permission.php` — Spatie.
- `config/services.php` — credenciais OpenAI, Google, AWS, Matomo.
- Sempre `config('chave')`, NUNCA `env()` fora de `config/*`.

## 11. Convenções obrigatórias

1. PHP 8.3 sempre (`php -v` para confirmar).
2. Seguir `AGENTS.md` / `CLAUDE.md` (Laravel Boost guidelines).
3. **Laravel Boost MCP habilitado** — usar `search-docs`, `tinker`, `database-query`, `database-schema`, `browser-logs`, `list-artisan-commands`, `get-absolute-url`.
4. Criar arquivos via `php artisan make:*` com `--no-interaction`.
5. Eloquent + relações tipadas, eager loading, evitar `DB::` (exceto tabelas legadas dos tribunais).
6. Form Requests para validação. Controllers magros.
7. Antes de finalizar: `vendor/bin/pint --dirty --format agent` e rodar testes do escopo.
8. Não criar `.md` sem pedido explícito.
9. Responder ao usuário em **português**.
10. DRY, descritividade nos nomes, return types explícitos, PHPDoc para shapes de array.

## 12. Pastas auxiliares úteis

- `ARQUIVOS_MD/` — planejamentos arquivados e notas históricas (consultar quando precisar de contexto profundo de uma feature).
- `relatorios/` — relatórios pontuais.
- `scripts/` — scripts utilitários do projeto.
- `database/migrations/` — ordem cronológica revela a evolução do schema.

## 13. Newsletter (Sendy)

Integração opt-in/opt-out com **Sendy** (lista configurada em `.env`). Arquitetura híbrida: **escritas via API** (`/subscribe`, `/unsubscribe`), **leituras via MySQL** do Sendy (connection `sendy` em `config/database.php`, `SENDY_DB_ENABLED=true` em prod), **cache local** em `users.newsletter_subscribed_at`.

### Kill switch e UI

- **Flag global:** `SiteSetting` `newsletter_integration_enabled` — se `false`, sem chamadas Sendy nem UI de inscrição (toggle, form `/newsletters`, popup). Configurável em Filament → **Newsletter Sendy** (`NewsletterIntegrationSettings`).
- **Popup:** `newsletter_popup_enabled` + gatilhos/A/B em `NewsletterPopupSettings`. Partial `partials/newsletter-popup.blade.php`; elegibilidade em `NewsletterPopupVisibility` (visitantes + logados **não** inscritos na lista Sendy; fallback cache se Sendy falhar).
- **Front:** form AJAX em `/newsletters`; auto-inscrição no registro/Google (toast); toggle Livewire em `/minha-conta/perfil` (`NewsletterToggle`).

### Admin e métricas

- **Estatísticas:** Filament `SiteStats` (`/admin/painel/estatisticas`) — registos, inscrições, popup A/B, botão **Atualizar** → `php artisan newsletter:sync --all`. Detalhes em `ARQUIVOS_MD/NEWSLETTER_STATS_MANUAL.md`.
- **Eventos:** `NewsletterSubscriptionEvent` com `source` (registration, google_oauth, panel_toggle, newsletters_form, popup, sync) e ações incluindo `impression` / `dismissed` do popup.

### Variáveis de ambiente (`.env.example`)

`SENDY_API_TOKEN`, `SENDY_API_BASE_URL`, `SENDY_BRAND_ID`, `SENDY_LIST_ID`, `SENDY_LIST_INTERNAL_ID`, `SENDY_DB_ENABLED`, `SENDY_DB_HOST`, `SENDY_DB_PORT`, `SENDY_DB_NAME`, `SENDY_DB_USER`, `SENDY_DB_PASSWORD`, `SENDY_SILENT_AUTHENTICATED`, `SENDY_SILENT_VISITOR`.

### Ativação em produção (manual)

1. Após deploy com migrations: `php artisan db:seed --class=SiteSettingsSeeder --force` se faltar chaves.
2. Filament `/painel` → **Newsletter Sendy** → ligar integração; configurar **Newsletter Popup** se desejado.
3. Opcional uma vez: `php artisan newsletter:sync --all` (popular cache `newsletter_subscribed_at`).
4. Cron no servidor: apenas `* * * * * php artisan schedule:run` (já inclui `newsletter:sync` a cada 6h).

Plano completo e histórico de fases: `ARQUIVOS_MD/NEWSLETTER_SENDY_PLAN.md`.
