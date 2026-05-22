# Prompt — Fase 7-B (popup para logados não inscritos)

Cole o bloco abaixo numa **nova conversa** do Cursor.

**Fase 8** (`PROJECT_BRIEF` + fecho): usar depois `ARQUIVOS_MD/NEWSLETTER_SENDY_EXECUTION_PROMPT.md` (bloco Fase 8).

---

## Bloco para colar no chat

```
Executar integração Newsletter Sendy — Fase 7-B (popup para contas logadas não inscritas).

Antes de qualquer código, ler nesta ordem:
1. PROJECT_BRIEF.md (raiz)
2. ARQUIVOS_MD/NEWSLETTER_SENDY_PLAN.md (STATUS TRACKER + secção FASE 7-B)
3. ARQUIVOS_MD/NEWSLETTER_SENDY_EXECUTION_PROMPT_7B.md (este arquivo)
4. AGENTS.md / CLAUDE.md

Confirmar em uma frase: "Li o briefing e o plano. Estou pronto para começar a Fase 7-B."

## Estado já entregue (Fases 0–7 validated, em prod)

| Fase | Entregáveis principais |
|------|------------------------|
| 0–5 | Flag, SendyService, migrations, /newsletters, registro/Google, toggle perfil |
| 6 | Popup **só visitantes** (`@guest`), A/B, cookies, `POST /newsletter/event`, dedup (`9fdc9fa`) |
| 7 | `SiteStats`, `newsletter:sync`, Kernel + `schedule:run` no Vito (`d67024f`) |

### Fase 6 — não regredir

- Partial: `partials/newsletter-popup.blade.php` + `newsletter-popup-content.blade.php`
- Só renderiza com `newsletter_integration_enabled` + `newsletter_popup_enabled`
- Hoje: `@guest` — **7-B remove esta limitação com elegibilidade Sendy/cache**
- Dedup subscribe 60s; Alpine `@once('alpinejs-3.14.3')`
- Testes: `php artisan test --compact --filter=Popup`

### Regras de ouro

1. Uma fase por vez. Fase 8 só após "Pode avançar para a Fase 8".
2. Kill switch OFF = sem popup nem subscribe UI.
3. Sendy nunca quebra o site (`try/catch`, fallback).
4. Testes + `vendor/bin/pint --dirty --format agent`.
5. PHP 8.3 · Português com o user.
6. **Não** editar `PROJECT_BRIEF.md` (Fase 8).

## Objetivo Fase 7-B

Mostrar o popup também para **utilizadores logados** que **não estão inscritos na lista Sendy**.

## Implementar (ver FASE 7-B no plano)

1. `NewsletterPopupVisibility::shouldRender(): bool`
   - Flags ON + (guest OU auth com `!SendyService::isSubscribed(email)`; fallback `!wantsNewsletter()` se Sendy falhar)
2. `newsletter-popup.blade.php` — usar helper; remover `@guest` seco
3. `newsletter-popup-content.blade.php` — logado: nome/email pré-preenchidos, email readonly; visitante: igual hoje
4. Testes `PopupConfigTest` + unit do visibility (mock Sendy / `fakeSendyConnection`)
5. Browser: logado não inscrito vê popup; inscrito não vê; guest inalterado

## Critérios de aceite

- [ ] `--filter=Popup` verde
- [ ] Pint OK
- [ ] Sem regressão dedup / tracking
- [ ] User validou no browser

Não escrever código até o user confirmar: "Pode avançar para a Fase 7-B" / "ok".
```

---

# Instruções para a IA executora

### Workflow

1. Ler FASE 7-B no plano (spec completa).
2. Confirmar com user antes de codar.
3. Implementar → `--filter=Popup` → Pint → passos browser → atualizar STATUS TRACKER (linha 7-B).
4. **Não** iniciar Fase 8 nesta sessão.

### Arquivos-chave

| Arquivo | Função |
|---------|--------|
| `resources/views/partials/newsletter-popup.blade.php` | Gate actual `@guest` |
| `resources/views/partials/newsletter-popup-content.blade.php` | Alpine + form |
| `app/Http/Controllers/NewsletterSubscriptionController.php` | subscribe + dedup |
| `app/Services/Sendy/SendyService.php` | `isSubscribed()`, `wantsNewsletter` via User |
| `app/Models/User.php` | `wantsNewsletter()` |
| `tests/Feature/Newsletter/PopupConfigTest.php` | Teste auth invertido |

### Complexidade esperada

Baixa a média-baixa. Sem migrations/rotas novas.

### Após 7-B validada

Próximo chat: `NEWSLETTER_SENDY_EXECUTION_PROMPT.md` → Fase 8 (`PROJECT_BRIEF` + suite final).
