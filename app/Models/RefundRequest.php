<?php

namespace App\Models;

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
     * Status possíveis para solicitações de estorno.
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_PROCESSED = 'processed';

    /**
     * Relacionamento com usuário.
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
     * Scope para solicitações pendentes.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
