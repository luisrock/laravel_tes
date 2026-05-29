<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Prompt de IA editável pelo admin, identificado por uma `key` única (ex.: `stats_analyst_system`).
 *
 * Tabela própria `ai_prompts`, isolada do catálogo legado `ai_models` e das features `TeseAnalysis*`.
 */
class AiPrompt extends Model
{
    /** @use HasFactory<\Database\Factories\AiPromptFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'title',
        'content',
        'description',
    ];

    /**
     * Conteúdo do prompt para uma `key`, ou `null` se não existir registro.
     */
    public static function contentForKey(string $key): ?string
    {
        return static::query()->where('key', $key)->value('content');
    }
}
