# Prompt — Continuação integração Newsletter Sendy

> **Fase 7-B (popup logados):** usar `ARQUIVOS_MD/NEWSLETTER_SENDY_EXECUTION_PROMPT_7B.md`  
> **Fase 8 (fecho):** bloco abaixo, após 7-B `validated`

---

## Bloco para colar no chat — Fase 8

```
Executar integração Newsletter Sendy — continuação (Fase 8).

Antes de qualquer código, ler nesta ordem:
1. PROJECT_BRIEF.md (raiz)
2. ARQUIVOS_MD/NEWSLETTER_SENDY_PLAN.md (STATUS TRACKER + secção FASE 8)
3. ARQUIVOS_MD/NEWSLETTER_SENDY_EXECUTION_PROMPT.md (este arquivo)
4. AGENTS.md / CLAUDE.md

Confirmar em uma frase: "Li o briefing e o plano. Estou pronto para começar a Fase 8."

## Estado já entregue (Fases 0–7 e 7-B validated)

| Fase | Entregáveis principais |
|------|------------------------|
| 0–6 | (ver plano) |
| 7 | `SiteStats`, `newsletter:sync`, scheduler, cron Vito `schedule:run` |
| 7-B | Popup para logados não inscritos Sendy + `NewsletterPopupVisibility` |

### Regras de ouro

1. Fase 8 = documentação + QA final — sem features novas.
2. Kill switch, Sendy, testes, Pint, português.

## Próxima fase: 8

**Objetivo:** `PROJECT_BRIEF.md`, Pint, suite completa, checklist ativação prod.

Implementar conforme **FASE 8** do plano.

Não escrever código até o user confirmar: "Pode avançar para a Fase 8" / "ok".
```

---

# Instruções Fase 8

1. Secção Newsletter (Sendy) em `PROJECT_BRIEF.md` (modelos, serviços, Filament, env, comando, popup 7-B).
2. `vendor/bin/pint --dirty --format agent`
3. `php artisan test --compact`
4. Checklist prod: flag ON, `newsletter:sync --all` opcional
5. STATUS TRACKER linha 8 → `validated`
