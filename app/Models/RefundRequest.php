<?php

namespace App\Models;

use App\Enums\RefundRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Cashier\Subscription;

class RefundRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cashier_subscription_id',
        'stripe_subscription_id',
        'stripe_invoice_id',
        'stripe_payment_intent_id',
        'reason',
        'status',
        'admin_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RefundRequestStatus::class,
        ];
    }

    /**
     * Relacionamento com usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com subscription do Cashier.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'cashier_subscription_id');
    }

    /**
     * Scope para solicitacoes pendentes.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', RefundRequestStatus::Pending);
    }
}
