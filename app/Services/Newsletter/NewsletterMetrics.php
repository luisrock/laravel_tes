<?php

namespace App\Services\Newsletter;

/**
 * @deprecated Use SiteMetrics
 */
final class NewsletterMetrics
{
    public static function newSubscriptionsLastDays(int $days = 7): int
    {
        $period = match ($days) {
            1 => '1',
            3 => '3',
            7 => '7',
            30 => '30',
            60 => '60',
            default => '7',
        };

        return SiteMetrics::newSubscriptions($period);
    }

    public static function cachedSubscribedUserCount(): int
    {
        return SiteMetrics::cachedSubscribedUserCount();
    }

    /**
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public static function dailySubscriptionCounts(int $days = 30): array
    {
        $period = (string) min(60, max(1, $days));

        return SiteMetrics::subscriptionTimeline($period);
    }

    /**
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public static function subscriptionsBySource(): array
    {
        return SiteMetrics::subscriptionsBySource('30');
    }

    /**
     * @return array<int, array{variant: string, impressions: int, conversions: int, rate: float|null}>
     */
    public static function popupVariantStats(): array
    {
        return SiteMetrics::popupVariantStats('30');
    }

    public static function popupConversionRateLastDays(int $days = 30): ?float
    {
        $period = match ($days) {
            1 => '1',
            3 => '3',
            7 => '7',
            60 => '60',
            default => '30',
        };

        return SiteMetrics::popupConversionRate($period);
    }
}
