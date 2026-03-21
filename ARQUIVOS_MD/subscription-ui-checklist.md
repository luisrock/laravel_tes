## Checklist UI - Assinaturas

Use este checklist no ambiente local (`https://teses.test`).
Objetivo: validar layout, textos e fluxo básico das telas de assinatura.

Pré-requisitos:
- Usuário de teste criado e login funcional.
- Stripe CLI rodando e `STRIPE_WEBHOOK_SECRET` atualizado no `.env` (se for testar checkout real).

Checklist:
- Acessar `/assinar` e confirmar:
  - Títulos e descrições dos planos.
  - Preços carregados corretamente.
  - Botões de ação visíveis.
- Iniciar checkout:
  - Redireciona para Stripe Checkout.
  - Campo de cupom aparece (Promotion Code).
- Concluir pagamento (modo test):
  - Retorna para `/assinar/sucesso?session_id=...`.
  - Mensagem de "processando" aparece até o webhook confirmar.
  - Após confirmação, status indica sucesso.
- Acessar `/minha-conta/assinatura`:
  - Status mostra "ativo".
  - Link para Billing Portal funciona.
  - Link de estorno aparece de forma discreta.
- Acessar `/minha-conta/estorno`:
  - Formulário válido e mensagens de validação.
  - Envio bem-sucedido e mensagem de confirmação.
- Cancelar assinatura via Billing Portal:
  - Status mostra grace period.
  - Mensagem indica data de término.

Observações:
- Anotar divergências de texto/estilo.
- Repetir após mudanças em views ou notificações.
