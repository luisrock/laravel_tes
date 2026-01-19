#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

TEST_EMAIL="${TEST_EMAIL:-teste@teses.test}"
TEST_PASSWORD="${TEST_PASSWORD:-Teste@12345}"
SESSION_ID="${SESSION_ID:-}"
POLL_SECONDS="${POLL_SECONDS:-30}"
CANCEL_SUBSCRIPTION="${CANCEL_SUBSCRIPTION:-0}"
SUBSCRIPTION_ID="${SUBSCRIPTION_ID:-}"

echo "==> Validando Stripe CLI..."
if ! command -v stripe >/dev/null 2>&1; then
  echo "Stripe CLI não encontrado. Instale com: brew install stripe/stripe-cli/stripe"
  exit 1
fi

echo "==> Validando env mínimo..."
REQUIRED_VARS=(STRIPE_KEY STRIPE_SECRET STRIPE_WEBHOOK_SECRET STRIPE_PRODUCT_PRO STRIPE_PRODUCT_PREMIUM CASHIER_CURRENCY CASHIER_CURRENCY_LOCALE)
for var in "${REQUIRED_VARS[@]}"; do
  if ! grep -qE "^${var}=" .env; then
    echo "Variável ausente no .env: ${var}"
    exit 1
  fi
done

if grep -qE "^STRIPE_WEBHOOK_SECRET=$" .env; then
  echo "STRIPE_WEBHOOK_SECRET vazio. Rode:"
  echo "  stripe listen --forward-to https://teses.test/stripe/webhook"
  exit 1
fi

echo "==> Criando/garantindo usuário de teste..."
php artisan tinker --execute="\\\\App\\\\Models\\\\User::firstOrCreate(['email'=>'${TEST_EMAIL}'], ['name'=>'Usuario Teste','password'=>bcrypt('${TEST_PASSWORD}')]);"
echo "Usuário de teste pronto: ${TEST_EMAIL} / ${TEST_PASSWORD}"

if [[ "${CANCEL_SUBSCRIPTION}" == "1" ]]; then
  if [[ -z "${SUBSCRIPTION_ID}" ]]; then
    SUBSCRIPTION_ID="$(php artisan tinker --execute="\\$user=\\\\App\\\\Models\\\\User::where('email','${TEST_EMAIL}')->first(); \\$sub=\\$user?->subscription('default'); echo \\$sub?->stripe_id ?? '';")"
  fi
  if [[ -n "${SUBSCRIPTION_ID}" ]]; then
    echo "==> Cancelando subscription ${SUBSCRIPTION_ID}..."
    printf "yes\n" | stripe subscriptions cancel "${SUBSCRIPTION_ID}"
  else
    echo "Nenhuma subscription encontrada para cancelar."
  fi
fi

if [[ -n "${SESSION_ID}" ]]; then
  echo "==> Aguardando processamento do checkout.session.completed..."
  for ((i=1; i<=POLL_SECONDS; i++)); do
    processed="$(php artisan tinker --execute="echo \\\\App\\\\Models\\\\StripeWebhookEvent::checkoutSessionProcessed('${SESSION_ID}') ? '1' : '0';")"
    if [[ "${processed}" == "1" ]]; then
      echo "Webhook processado para session_id=${SESSION_ID}"
      break
    fi
    sleep 1
  done

  echo "==> Verificando status da assinatura local..."
  php artisan tinker --execute="\\$user=\\\\App\\\\Models\\\\User::where('email','${TEST_EMAIL}')->first(); echo \\$user?->isSubscriber() ? 'assinante' : 'nao_assinante';"
else
  echo "SESSION_ID não informado. Após concluir o checkout, rode:"
  echo "  SESSION_ID=cs_test_xxx ./scripts/test-subscription-flow.sh"
fi
