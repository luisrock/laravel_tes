<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TeseAnalysisSection extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'generated_at';

    protected $fillable = [
        'tese_id',
        'tribunal',
        'section_type',
        'content',
        'status',
        'is_active',
        'ai_model_id',
        'prompt_key',
        'prompt_hash',
        'source_hash',
        'tokens_input',
        'tokens_output',
        'cost_usd',
        'price_snapshot_input',
        'price_snapshot_output',
        'provider_request_id',
        'latency_ms',
        'finish_reason',
        'raw_usage',
        'error_message',
        'activated_by',
        'activated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tokens_input' => 'integer',
        'tokens_output' => 'integer',
        'cost_usd' => 'decimal:6',
        'price_snapshot_input' => 'decimal:4',
        'price_snapshot_output' => 'decimal:4',
        'latency_ms' => 'integer',
        'raw_usage' => 'array',
        'generated_at' => 'datetime',
        'activated_at' => 'datetime',
    ];

    /**
     * Modelo de IA usado para gerar.
     */
    public function aiModel()
    {
        return $this->belongsTo(AiModel::class, 'ai_model_id');
    }

    /**
     * Usuário que ativou esta versão.
     */
    public function activator()
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    /**
     * Escopo: seções ativas.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Escopo: seções publicadas.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Escopo: histórico de uma seção específica.
     */
    public function scopeForSection($query, int $teseId, string $tribunal, string $sectionType)
    {
        return $query->where('tese_id', $teseId)
            ->where('tribunal', $tribunal)
            ->where('section_type', $sectionType)
            ->orderBy('generated_at', 'desc');
    }

    /**
     * Ativa esta versão (desativa outras).
     * Só pode ativar se status = 'published'.
     */
    public function ativar(int $userId): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        DB::transaction(function () use ($userId) {
            // Desativa todas da mesma seção
            self::where('tese_id', $this->tese_id)
                ->where('tribunal', $this->tribunal)
                ->where('section_type', $this->section_type)
                ->update(['is_active' => false]);

            // Ativa esta
            $this->update([
                'is_active' => true,
                'activated_by' => $userId,
                'activated_at' => now(),
            ]);
        });

        return true;
    }
}
