<?php

namespace App\Models;

use App\Enums\NewsletterEventAction;
use App\Enums\NewsletterEventSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class NewsletterSubscriptionEvent extends Model
{
    /** @use HasFactory<\Database\Factories\NewsletterSubscriptionEventFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'action',
        'source',
        'popup_variant',
        'popup_trigger',
        'ip',
        'user_agent',
        'referrer',
        'page_url',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByAction(Builder $query, NewsletterEventAction|string $action): Builder
    {
        $value = $action instanceof NewsletterEventAction ? $action->value : $action;

        return $query->where('action', $value);
    }

    public function scopeBySource(Builder $query, NewsletterEventSource|string $source): Builder
    {
        $value = $source instanceof NewsletterEventSource ? $source->value : $source;

        return $query->where('source', $value);
    }

    public function scopeInPeriod(Builder $query, Carbon|string $from, Carbon|string $to): Builder
    {
        return $query->whereBetween('created_at', [
            $from instanceof Carbon ? $from : Carbon::parse($from),
            $to instanceof Carbon ? $to : Carbon::parse($to),
        ]);
    }

    public function scopeSubscriptions(Builder $query): Builder
    {
        return $query->whereIn('action', [
            NewsletterEventAction::Subscribed->value,
            NewsletterEventAction::AlreadySubscribed->value,
        ]);
    }
}
