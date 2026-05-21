# Prompt — Continuação integração Newsletter Sendy

Cole o bloco abaixo numa **nova conversa** do Cursor. Não precisa de ajustes manuais.

---

## Bloco para colar no chat

```
Executar integração Newsletter Sendy — continuação (Fase 8).

Antes de qualquer código, ler nesta ordem:
1. PROJECT_BRIEF.md (raiz)
2. ARQUIVOS_MD/NEWSLETTER_SENDY_PLAN.md (STATUS TRACKER + secção FASE 8)
3. ARQUIVOS_MD/NEWSLETTER_SENDY_EXECUTION_PROMPT.md (este arquivo)
4. AGENTS.md / CLAUDE.md

Confirmar em uma frase: "Li o briefing e o plano. Estou pronto para começar a Fase 8."

## Estado já entregue (Fases 0–7 validated; Fase 7 em deploy)

| Fase | Entregáveis principais |
|------|------------------------|
| 0 | `newsletter_integration_enabled` no seeder; `.env.example` Sendy |
| 1 | `SendyService`, jobs, enums, DTOs, `fakeSendyConnection()` |
| 2 | migrations `users` + `newsletter_subscription_events` |
| 3 | `POST /newsletter/subscribe`, form AJAX `/newsletters`, kill switch Filament |
| 4 | Auto-inscrição registro/Google (variante B) + toast; `Rule::email()` |
| 5 | `NewsletterToggle` em `/minha-conta/perfil` |
| 6 | Popup + `NewsletterPopupSettings` + `POST /newsletter/event` + anti-duplicata (`9fdc9fa`) |
| 7 | `SiteStats` (`/admin/painel/estatisticas`), `newsletter:sync`, Kernel unificado, cron Vito `schedule:run` |

### Fase 7 — referência rápida (não regredir)

- **Filament:** `/admin/painel/estatisticas` — filtro período (24h/3/7/30/60d); botão **Atualizar** → `newsletter:sync --all`.
- **Cards:** novos registos, novas inscrições, total Sendy, contas inscritas no site, conversão popup.
- **Scheduler:** `app/Console/Kernel.php` — queue, sitemap, matomo, newsletters:import, renewal, `newsletter:sync` (6h).
- **Cron Vito (única linha):** `* * * * * php8.3 …/artisan schedule:run` — remover linhas antigas que chamavam artisan direto.
- **Manual:** `ARQUIVOS_MD/NEWSLETTER_STATS_MANUAL.md`.
- **Redirect:** `/newsletter-stats` → `/estatisticas`.
- **Testes:** `--filter=SyncCommand`, `NewsletterStatsPage`, `SiteMetrics`.

### Regras de ouro

1. Uma fase por vez. Avançar só após "Pode avançar para a Fase N+1".
2. Kill switch: `newsletter_integration_enabled` OFF = nada de UI inscrição.
3. Sendy nunca quebra o site (try/catch, `SendyResult`).
4. Testes: `php artisan test --compact` + Pint `--dirty`.
5. PHP 8.3 · Português com o user.

## Próxima fase: 8

**Objetivo:** documentar no PROJECT_BRIEF, Pint, suite completa, checklist de ativação em prod.

Implementar conforme secção **FASE 8** do plano:
- Secção "Newsletter (Sendy)" em `PROJECT_BRIEF.md`.
- `vendor/bin/pint --dirty --format agent`.
- `php artisan test --compact` (suite completa verde).
- Documentar processo: ligar flag em Filament, `newsletter:sync --all` uma vez.

Não escrever código até o user confirmar: "Pode avançar para a Fase 8" / "ok".
```

---

# Instruções para a IA executora

### Workflow por fase

1. Ler secção FASE 8 no plano.
2. Confirmar com o user antes de codar (se aplicável — Fase 8 é sobretudo docs + QA).
3. Implementar → Pint → suite completa → atualizar STATUS TRACKER.
4. Handoff final da integração newsletter.

### Arquivos-chave existentes

| Arquivo | Função |
|---------|--------|
| `app/Filament/Pages/SiteStats.php` | Estatísticas gerais + filtro período |
| `app/Services/Newsletter/SiteMetrics.php` | Métricas agregadas |
| `app/Console/Commands/SyncNewsletterStatus.php` | `newsletter:sync` |
| `app/Console/Kernel.php` | Scheduler unificado |
| `ARQUIVOS_MD/NEWSLETTER_STATS_MANUAL.md` | Manual dos cards |
| `app/Filament/Pages/NewsletterIntegrationSettings.php` | Kill switch |
| `app/Filament/Pages/NewsletterPopupSettings.php` | Config popup |

### Deploy Vito

- **Cron:** só `schedule:run` (user já configurou no painel Vito).
- **Script deploy:** `migrate --force` automático; sem `SiteSettingsSeeder`.
- **Pós-deploy Fase 7+8:** `newsletter:sync --all` opcional; ligar flag se ainda OFF.

### Sessão anterior (Fase 7 — 2026-05-21)

- Stats, sync, Kernel migrado do crontab, UI «Estatísticas», botão Atualizar.
- Cron Vito atualizado para `schedule:run` (linhas artisan antigas removidas pelo user).
- **Pendente:** Fase 8 (PROJECT_BRIEF + fecho).

### Em caso de bloqueio

- Teste falha 2× → parar e reportar.
- Sendy DB inacessível em dev Mac → `SENDY_DB_ENABLED=false`; testes com `fakeSendyConnection()`.
