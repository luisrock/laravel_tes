# Prompt — Continuação integração Newsletter Sendy

Cole o bloco abaixo numa **nova conversa** do Cursor. Não precisa de ajustes manuais.

---

## Bloco para colar no chat

```
Executar integração Newsletter Sendy — continuação (Fase 6 em diante).

Antes de qualquer código, ler nesta ordem:
1. PROJECT_BRIEF.md (raiz)
2. ARQUIVOS_MD/NEWSLETTER_SENDY_PLAN.md (STATUS TRACKER + seção Fase 6)
3. ARQUIVOS_MD/NEWSLETTER_SENDY_EXECUTION_PROMPT.md (este arquivo — regras operacionais)
4. AGENTS.md / CLAUDE.md (regras do projeto)

Confirmar em uma frase: "Li o briefing e o plano. Estou pronto para começar a Fase N." (N = primeira fase `pending` no STATUS TRACKER → **6**).

## Estado já entregue

| Fase | Status | Entregáveis principais |
|------|--------|------------------------|
| 0 | validated | `newsletter_integration_enabled` no seeder; `.env.example` Sendy + `SENDY_DB_ENABLED` |
| 1 | validated | `SendyService`, jobs, enums, DTOs, `fakeSendyConnection()` |
| 2 | validated | migrations `users` + `newsletter_subscription_events`, models |
| 3 | validated | `POST /newsletter/subscribe`, form AJAX `/newsletters`, sync API no load |
| 4 | validated | **Variante B**: auto-inscrição registro + Google; toast; `Rule::email()` |
| 5 | validated | `NewsletterToggle` em `/minha-conta/perfil` |
| 6 | **pending** | popup visitante + Filament `NewsletterPopupSettings` |
| 7–8 | pending | stats/sync + `PROJECT_BRIEF.md` |

### Fase 4 — detalhe (não regredir)

- **Sem checkbox** no registro. Novos users (email ou Google) são inscritos automaticamente se `newsletter_integration_enabled=true`.
- **`NewUserNewsletterSubscription`**: `SendyService::subscribe()` **síncrono** após criar user — nunca quebra o cadastro.
- **Toast** (`partials/newsletter-registration-toast.blade.php` em `layouts/app.blade.php`):
  - `subscribed` — sucesso ou «Already subscribed.» (já na lista Sendy).
  - `invite` — falha Sendy; convida inscrição via Minha Conta > Perfil.
- Sessão `newsletter.registration_toast` com `session()->pull()` (sobrevive à página `/email/verify`).
- Google: só `$isNewUser`; re-login sem subscribe nem toast.
- `NewsletterSubscribeRequest` usa `Rule::email()` (RFC + MX + anti-spoof).

Extra: `app/Filament/Pages/NewsletterIntegrationSettings.php` — kill switch em `/admin/painel/newsletter-integration-settings`.

## Regras de ouro

1. **Uma fase por vez.** Só avançar após o user dizer: "Pode avançar para a Fase N+1".
2. **Kill switch:** `SiteSetting::getAsBool('newsletter_integration_enabled', false)`. Flag OFF = **nada** de UI de inscrição (sem link externo; tudo ou nada).
3. **Sendy não quebra o site:** try/catch + `SendyResult` / enums; nunca throw ao caller.
4. **Testes:** `php artisan test --compact --filter=...` da fase + suite completa. Pre-commit roda Pint + Pest + MySQL.
5. **Browser:** passos em `https://teses.test`; aguardar confirmação antes de marcar `validated`.
6. **Atualizar** STATUS TRACKER + notas no plano ao fim de cada fase.
7. **Pint:** `vendor/bin/pint --dirty --format agent`.
8. **Boost MCP:** `search-docs`, `database-schema`, `tinker`, `list-artisan-commands`.
9. **PHP 8.3** · **Português** com o user.

## Ambientes

- **Dev (Mac):** `SENDY_DB_ENABLED=false` — leituras Sendy via API; testes usam `fakeSendyConnection()`.
- **Prod:** `SENDY_DB_ENABLED=true`; deploy automático após push em `master`.
- **Flag ON/OFF:** Filament → Newsletter Sendy (não depender de tinker).

## Validação de email (Laravel 12)

`Rule::email()->rfcCompliant(strict: false)->validateMxRecord()->preventSpoofing()` em Form Requests da newsletter. Em testes Pest, emails válidos com domínio MX real (ex. `@gmail.com`).

## UI /newsletters (não regredir)

- Guest: inputs compactos + botão **Receba**.
- Logado inscrito: **Você está inscrito!**
- Logado não inscrito: link **Receba atualização semanal** (AJAX).

## Próxima fase: 6

Popup visitante (Alpine, 3 gatilhos timer/exit-intent/scroll, A/B, cookies), `NewsletterPopupSettings` no Filament, `POST /newsletter/event` para tracking. Spec completa na secção FASE 6 do plano. Espelhar `MeteredWallSettings.php`.

Não escrever código até o user confirmar: "Pode avançar para a Fase 6" / "ok".
```

---

# Instruções para a IA executora (referência permanente)

### Workflow por fase

1. Ler a seção da fase no plano.
2. Confirmar com o user: "Vou iniciar a Fase N. Faz sentido?"
3. Implementar conforme spec (`search-docs` antes de Laravel/Filament/Livewire).
4. Testes da fase + suite completa.
5. Pint.
6. Passos de validação no browser (se aplicável).
7. Aguardar confirmação do user.
8. Atualizar plano (STATUS TRACKER + notas).
9. Perguntar: "Posso avançar para a Fase N+1?"

### Arquivos-chave da Fase 4 (referência)

| Arquivo | Função |
|---------|--------|
| `app/Services/Sendy/NewUserNewsletterSubscription.php` | Subscribe síncrono + sessão toast |
| `app/Actions/Fortify/CreateNewUser.php` | Chama após criar user |
| `app/Http/Controllers/GoogleAuthController.php` | Só se `$isNewUser` |
| `resources/views/partials/newsletter-registration-toast.blade.php` | Toast subscribed/invite |
| `resources/views/layouts/app.blade.php` | Inclui partial |
| `tests/Feature/Newsletter/RegistrationNewsletterTest.php` | Testes registro |
| `tests/Feature/Newsletter/GoogleOAuthOptInTest.php` | Testes Google |

### Convenções do projeto

- Estrutura Laravel 10 legada (`Http/Kernel.php`, `Console/Kernel.php`). Não mexer em `bootstrap/app.php`.
- Tailwind `tw-`, Form Requests, Eloquent tipado, `@honeypot` em forms públicos.
- Filament settings: espelhar `MeteredWallSettings.php`.
- Helpers: `tests/Pest.php` → `fakeSendyConnection()`, `createAdminUser()`.

### Em caso de bloqueio

- Teste falha 2x → parar, reportar.
- Sendy indisponível → reportar, não mascarar.
- Plano vs código → parar, propor ajuste, aguardar decisão.
