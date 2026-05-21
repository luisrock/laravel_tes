# Prompt — Continuação integração Newsletter Sendy

Cole o bloco abaixo numa **nova conversa** do Cursor. Não precisa de ajustes manuais.

---

## Bloco para colar no chat

```
Executar integração Newsletter Sendy — continuação (Fase 7).

Antes de qualquer código, ler nesta ordem:
1. PROJECT_BRIEF.md (raiz)
2. ARQUIVOS_MD/NEWSLETTER_SENDY_PLAN.md (STATUS TRACKER + secção FASE 7)
3. ARQUIVOS_MD/NEWSLETTER_SENDY_EXECUTION_PROMPT.md (este arquivo)
4. AGENTS.md / CLAUDE.md

Confirmar em uma frase: "Li o briefing e o plano. Estou pronto para começar a Fase 7."

## Estado já entregue (Fases 0–6 validated)

| Fase | Entregáveis principais |
|------|------------------------|
| 0 | `newsletter_integration_enabled` no seeder; `.env.example` Sendy |
| 1 | `SendyService`, jobs, enums, DTOs, `fakeSendyConnection()` |
| 2 | migrations `users` + `newsletter_subscription_events` |
| 3 | `POST /newsletter/subscribe`, form AJAX `/newsletters`, kill switch Filament |
| 4 | Auto-inscrição registro/Google (variante B) + toast; `Rule::email()` |
| 5 | `NewsletterToggle` em `/minha-conta/perfil` |
| 6 | Popup visitante + `NewsletterPopupSettings` + `POST /newsletter/event` |

### Fase 6 — referência rápida (não regredir)

- **Filament:** `/admin/painel/newsletter-popup-settings` — gatilho `->live()` (timer→segundos, scroll→%, exit-intent→sem extra). Reset **espera** / **completo** via epochs em `SiteSetting`.
- **Front:** `partials/newsletter-popup*.blade.php` em `front/base.blade.php`; só `@guest` + duas flags.
- **Tracking:** `newsletter_subscription_events` com `source=popup`, actions `impression`/`dismissed`/`subscribed`.
- **Testes:** `--filter=Popup` (17 testes).

### Regras de ouro

1. Uma fase por vez. Avançar só após "Pode avançar para a Fase N+1".
2. Kill switch: `newsletter_integration_enabled` OFF = nada de UI inscrição.
3. Sendy nunca quebra o site (try/catch, `SendyResult`).
4. Testes: `php artisan test --compact --filter=...` + Pint `--dirty`.
5. PHP 8.3 · Português com o user.

## Próxima fase: 7

**Objetivo:** painel de stats de cadastros + reconciliação cache.

Implementar conforme secção **FASE 7** do plano:
- `NewsletterStats` (Filament, read-only) + 4 widgets (overview, daily chart, by source, popup A/B).
- Comando `php artisan newsletter:sync` (`--all`, `--user=ID`).
- Schedule `everySixHours()` em `app/Console/Kernel.php`.
- Botão «Sincronizar agora» na página stats.
- Testes: `SyncCommandTest`, `NewsletterStatsPageTest`.

Dados: `newsletter_subscription_events` + `SendyService::activeSubscriberCount()` + sync `users.newsletter_subscribed_at` via DB Sendy.

Não escrever código até o user confirmar: "Pode avançar para a Fase 7" / "ok".
```

---

# Instruções para a IA executora

### Workflow por fase

1. Ler secção FASE 7 no plano.
2. Confirmar com o user antes de codar.
3. `search-docs` para Filament 4 widgets/charts e Artisan commands.
4. Implementar → testes → Pint → passos browser → atualizar STATUS TRACKER.
5. Fase 8 depois: `PROJECT_BRIEF.md` + suite final + checklist prod.

### Arquivos-chave existentes (Fase 6)

| Arquivo | Função |
|---------|--------|
| `app/Filament/Pages/NewsletterPopupSettings.php` | Config popup + reset epochs |
| `app/Http/Controllers/NewsletterSubscriptionController.php` | `subscribe`, `trackEvent` |
| `resources/views/partials/newsletter-popup-content.blade.php` | Alpine + UI |
| `app/Models/NewsletterSubscriptionEvent.php` | Auditoria/stats (Fase 7 lê isto) |
| `app/Services/Sendy/SendyService.php` | API/DB Sendy, `activeSubscriberCount()` |
| `app/Jobs/Newsletter/SyncNewsletterStatusJob.php` | Job existente — reutilizar no comando |
| `tests/Pest.php` | `fakeSendyConnection()`, `createAdminUser()` |

### Deploy / prod (push master — já com Fase 6)

| Ação | Necessário? |
|------|-------------|
| `php artisan migrate --force` | Automático no Vito; só relevante se migrations 2026_05_20 newsletter ainda não correram em prod. |
| `php artisan db:seed --class=SiteSettingsSeeder --force` | Automático; **não** cria chaves do popup. Só `newsletter_integration_enabled` (default 0 se ausente). |
| Seeder extra para popup | **Não.** |
| Pós-deploy | Filament: integração + popup se desejado. `newsletter:sync --all` **após Fase 7**. |

### Convenções

- Laravel 10 legada: `Http/Kernel.php`, `Console/Kernel.php`.
- Filament 4 em `/admin/painel`; espelhar páginas existentes (`MeteredWallSettings`, widgets do projeto se houver).
- Tailwind `tw-` no front; admin usa Filament.

### Em caso de bloqueio

- Teste falha 2× → parar e reportar.
- Sendy DB inacessível em dev Mac → `SENDY_DB_ENABLED=false`; testes com `fakeSendyConnection()`.
