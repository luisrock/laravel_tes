<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Arr;

class SubscriptionUi
{
    public const LABEL_ACTIVE = 'Ativa';
    public const LABEL_GRACE = 'Carência';
    public const LABEL_CANCELED = 'Cancelada';
    public const LABEL_NONE = 'Sem assinatura';

    public static function tierLabels(): array
    {
        return config('subscription.tier_labels', []);
    }

    public static function tierLabel(?string $productId): string
    {
        if (!$productId) {
            return self::LABEL_NONE;
        }

        return Arr::get(self::tierLabels(), $productId, $productId);
    }

    public static function tierColor(string $label): string
    {
        return match ($label) {
            'PRO' => 'primary',
            'PREMIUM' => 'success',
            self::LABEL_NONE => 'gray',
            default => 'secondary',
        };
    }

    public static function resolveTierProductId(iterable $items): ?string
    {
        $tierProductIds = config('subscription.tier_product_ids', []);

        if (empty($tierProductIds)) {
            return null;
        }

        foreach ($items as $item) {
            if (in_array($item->stripe_product ?? null, $tierProductIds, true)) {
                return $item->stripe_product;
            }
        }

        return null;
    }

    public static function statusLabel(?string $stripeStatus, ?CarbonInterface $endsAt = null): string
    {
        if ($endsAt) {
            return $endsAt->isFuture() ? self::LABEL_GRACE : self::LABEL_CANCELED;
        }

        if ($stripeStatus === 'active') {
            return self::LABEL_ACTIVE;
        }

        if (!$stripeStatus) {
            return self::LABEL_NONE;
        }

        return ucfirst(str_replace('_', ' ', $stripeStatus));
    }

    public static function statusColor(string $label): string
    {
        return match ($label) {
            self::LABEL_ACTIVE => 'success',
            self::LABEL_GRACE => 'warning',
            self::LABEL_CANCELED => 'danger',
            self::LABEL_NONE => 'gray',
            default => 'secondary',
        };
    }
}
