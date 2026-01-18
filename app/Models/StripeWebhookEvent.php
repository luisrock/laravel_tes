<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripeWebhookEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_event_id',
        'event_type',
        'stripe_object_id',
        'user_id',
        'received_at',
        'processed_at',
        'failed_at',
        'attempts',
        'last_error',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Relacionamento com usuário.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se o evento já foi processado com sucesso.
     */
    public function isProcessed(): bool
    {
        return $this->processed_at !== null;
    }

    /**
     * Verifica se uma checkout session foi processada.
     */
    public static function checkoutSessionProcessed(string $sessionId): bool
    {
        return static::where('stripe_object_id', $sessionId)
            ->where('event_type', 'checkout.session.completed')
            ->whereNotNull('processed_at')
            ->exists();
    }
}
