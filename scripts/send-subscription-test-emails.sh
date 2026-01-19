#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

EMAIL="${1:-mauluis@gmail.com}"

echo "==> Enviando emails de teste para: ${EMAIL}"

php artisan tinker --execute="\$u=App\Models\User::where('email','${EMAIL}')->firstOrFail(); \$u->notifyNow(new App\Notifications\WelcomeSubscriberNotification());"
php artisan tinker --execute="\$u=App\Models\User::where('email','${EMAIL}')->firstOrFail(); \$u->notifyNow(new App\Notifications\SubscriptionCanceledNotification(now()->addDays(10)));"
php artisan tinker --execute="\$u=App\Models\User::where('email','${EMAIL}')->firstOrFail(); \$u->notifyNow(new App\Notifications\SubscriptionRenewingSoonNotification(now()->addDays(7)));"
php artisan tinker --execute="\$u=App\Models\User::where('email','${EMAIL}')->firstOrFail(); \$u->notifyNow(new App\Notifications\RefundRequestReceivedNotification(new App\Models\RefundRequest()));"

echo "==> Envio concluído."
