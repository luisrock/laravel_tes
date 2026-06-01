<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeseAnalysisJob extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'tese_id',
        'tribunal',
        'section_type',
        'ai_model_id',
        'status',
        'attempts',
        'max_attempts',
        'last_error',
        'locked_by',
        'started_at',
        'completed_at',
        'input_tokens',
        'output_tokens',
        'cost_usd',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'max_attempts' => 'integer',
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'cost_usd' => 'float',
        'created_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Modelo de IA a usar.
     */
    public function aiModel()
    {
        return $this->belongsTo(AiModel::class, 'ai_model_id');
    }

    /**
     * Escopo: jobs na fila.
     */
    public function scopeQueued($query)
    {
        return $query->where('status', 'queued');
    }

    /**
     * Escopo: jobs com erro.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'error');
    }

    /**
     * Escopo: jobs em execução.
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Verifica se pode tentar novamente.
     */
    public function canRetry(): bool
    {
        return $this->attempts < $this->max_attempts;
    }
}
