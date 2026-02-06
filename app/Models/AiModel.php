<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiModel extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'provider',
        'name',
        'model_id',
        'price_input_per_million',
        'price_output_per_million',
        'is_active',
        'deprecated_at',
    ];

    protected $casts = [
        'price_input_per_million' => 'decimal:4',
        'price_output_per_million' => 'decimal:4',
        'is_active' => 'boolean',
        'deprecated_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Seções geradas por este modelo.
     */
    public function sections()
    {
        return $this->hasMany(TeseAnalysisSection::class, 'ai_model_id');
    }

    /**
     * Jobs que usam este modelo.
     */
    public function jobs()
    {
        return $this->hasMany(TeseAnalysisJob::class, 'ai_model_id');
    }

    /**
     * Escopo: apenas modelos ativos.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNull('deprecated_at');
    }

    /**
     * Calcula custo baseado em tokens.
     */
    public function calculateCost(int $inputTokens, int $outputTokens): float
    {
        $inputCost = ($inputTokens / 1_000_000) * $this->price_input_per_million;
        $outputCost = ($outputTokens / 1_000_000) * $this->price_output_per_million;
        return round($inputCost + $outputCost, 6);
    }
}
