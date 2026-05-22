<?php

namespace App\Services\Newsletter;

use App\Enums\NewsletterEventAction;
use App\Enums\NewsletterEventSource;
use App\Models\NewsletterSubscriptionEvent;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class SiteMetrics
{
    public const PERIOD_OPTIONS = [
        '1' => 'Últimas 24 horas',
        '3' => 'Últimos 3 dias',
        '7' => 'Últimos 7 dias',
        '30' => 'Últimos 30 dias',
        '60' => 'Últimos 60 dias',
    ];

    public static function periodStart(string $period = '30'): Carbon
    {
        return match ($period) {
            '1' => now()->subHours(24),
            '3' => now()->subDays(3),
            '7' => now()->subDays(7),
            '30' => now()->subDays(30),
            '60' => now()->subDays(60),
            default => now()->subDays(30),
        };
    }

    public static function periodLabel(string $period = '30'): string
    {
        return self::PERIOD_OPTIONS[$period] ?? self::PERIOD_OPTIONS['30'];
    }

    public static function newUserRegistrations(string $period = '30'): int
    {
        return User::query()
            ->where('created_at', '>=', self::periodStart($period))
            ->count();
    }

    public static function newSubscriptions(string $period = '30'): int
    {
        if (! Schema::hasTable('newsletter_subscription_events')) {
            return 0;
        }

        return NewsletterSubscriptionEvent::query()
            ->subscriptions()
            ->where('created_at', '>=', self::periodStart($period))
            ->count();
    }

    public static function newsletterPagesSubscriptions(string $period = '30'): int
    {
        if (! Schema::hasTable('newsletter_subscription_events')) {
            return 0;
        }

        return NewsletterSubscriptionEvent::query()
            ->subscriptions()
            ->where('source', NewsletterEventSource::NewslettersForm->value)
            ->where('created_at', '>=', self::periodStart($period))
            ->count();
    }

    public static function cachedSubscribedUserCount(): int
    {
        if (! Schema::hasColumn('users', 'newsletter_subscribed_at')) {
            return 0;
        }

        return User::query()->whereNotNull('newsletter_subscribed_at')->count();
    }

    /**
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public static function subscriptionTimeline(string $period = '30'): array
    {
        if (! Schema::hasTable('newsletter_subscription_events')) {
            return ['labels' => [], 'data' => []];
        }

        if ($period === '1') {
            return self::subscriptionTimelineHourly();
        }

        $days = (int) $period;
        $from = now()->subDays($days - 1)->startOfDay();

        $rows = NewsletterSubscriptionEvent::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->subscriptions()
            ->where('created_at', '>=', $from)
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $data = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $from->copy()->addDays($i);
            $key = $date->toDateString();
            $labels[] = $date->format('d/m');
            $data[] = (int) ($rows[$key] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    private static function subscriptionTimelineHourly(): array
    {
        $from = now()->subHours(23)->startOfHour();

        $hourExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m-%d %H:00', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m-%d %H:00')";

        $rows = NewsletterSubscriptionEvent::query()
            ->selectRaw("{$hourExpression} as hour, COUNT(*) as total")
            ->subscriptions()
            ->where('created_at', '>=', $from)
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('total', 'hour');

        $labels = [];
        $data = [];

        for ($i = 0; $i < 24; $i++) {
            $hour = $from->copy()->addHours($i);
            $key = $hour->format('Y-m-d H:00');
            $labels[] = $hour->format('H\h');
            $data[] = (int) ($rows[$key] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public static function subscriptionsBySource(string $period = '30'): array
    {
        if (! Schema::hasTable('newsletter_subscription_events')) {
            return ['labels' => [], 'data' => []];
        }

        $rows = NewsletterSubscriptionEvent::query()
            ->select('source', DB::raw('COUNT(*) as total'))
            ->subscriptions()
            ->where('created_at', '>=', self::periodStart($period))
            ->groupBy('source')
            ->orderByDesc('total')
            ->get();

        $labels = [];
        $data = [];

        foreach ($rows as $row) {
            $source = NewsletterEventSource::tryFrom($row->source);
            $labels[] = match ($source) {
                NewsletterEventSource::Registration => 'Registro no site',
                NewsletterEventSource::GoogleOauth => 'Conta Google',
                NewsletterEventSource::PanelToggle => 'Minha conta',
                NewsletterEventSource::NewslettersForm => 'Páginas de newsletters',
                NewsletterEventSource::Popup => 'Popup',
                NewsletterEventSource::Sync => 'Sincronização',
                default => $row->source,
            };
            $data[] = (int) $row->total;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * @return array<int, array{variant: string, impressions: int, conversions: int, rate: float|null}>
     */
    public static function popupVariantStats(string $period = '30'): array
    {
        if (! Schema::hasTable('newsletter_subscription_events')) {
            return [];
        }

        $from = self::periodStart($period);
        $stats = [];

        foreach (['A', 'B'] as $variant) {
            $base = NewsletterSubscriptionEvent::query()
                ->where('source', NewsletterEventSource::Popup->value)
                ->where('popup_variant', $variant)
                ->where('created_at', '>=', $from);

            $impressions = (clone $base)
                ->where('action', NewsletterEventAction::Impression->value)
                ->count();

            $conversions = (clone $base)
                ->whereIn('action', [
                    NewsletterEventAction::Subscribed->value,
                    NewsletterEventAction::AlreadySubscribed->value,
                ])
                ->count();

            $stats[] = [
                'variant' => $variant,
                'impressions' => $impressions,
                'conversions' => $conversions,
                'rate' => $impressions > 0 ? round(($conversions / $impressions) * 100, 1) : null,
            ];
        }

        return $stats;
    }

    public static function popupConversionRate(string $period = '30'): ?float
    {
        if (! Schema::hasTable('newsletter_subscription_events')) {
            return null;
        }

        $from = self::periodStart($period);

        $impressions = NewsletterSubscriptionEvent::query()
            ->where('source', NewsletterEventSource::Popup->value)
            ->where('action', NewsletterEventAction::Impression->value)
            ->where('created_at', '>=', $from)
            ->count();

        if ($impressions === 0) {
            return null;
        }

        $conversions = NewsletterSubscriptionEvent::query()
            ->where('source', NewsletterEventSource::Popup->value)
            ->subscriptions()
            ->where('created_at', '>=', $from)
            ->count();

        return round(($conversions / $impressions) * 100, 1);
    }
}
