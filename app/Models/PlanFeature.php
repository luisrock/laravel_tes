<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_product_id',
        'feature_key',
        'feature_value',
    ];

    /**
     * Scope para buscar features de um produto específico.
     */
    public function scopeForProduct($query, string $productId)
    {
        return $query->where('stripe_product_id', $productId);
    }

    /**
     * Verifica se um produto tem uma feature específica.
     */
    public static function productHasFeature(string $productId, string $featureKey): bool
    {
        return static::where('stripe_product_id', $productId)
            ->where('feature_key', $featureKey)
            ->exists();
    }
}
