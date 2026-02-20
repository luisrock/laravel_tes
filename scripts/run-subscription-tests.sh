#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

APP_ENV="${APP_ENV:-$(php -r "require 'vendor/autoload.php'; \$app=require 'bootstrap/app.php'; echo \$app->environment();" 2>/dev/null || echo 'unknown')}"
if [[ "${APP_ENV}" == "production" || "${APP_ENV}" == "prod" ]]; then
  echo "Este script não deve ser executado em produção."
  exit 1
fi

echo "==> PHPUnit: notificações e renovação (assinaturas)"
php artisan test --filter Subscription

if [[ "${RUN_E2E:-0}" == "1" ]]; then
  echo "==> E2E Stripe (checkout + webhook)"
  if ! command -v stripe >/dev/null 2>&1; then
    echo "Stripe CLI não encontrado. Instale com: brew install stripe/stripe-cli/stripe"
    exit 1
  fi

  ./scripts/test-subscription-flow.sh
fi
