<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentView extends Model
{
    /** @use HasFactory<\Database\Factories\ContentViewFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'content_type',
        'content_id',
        'tribunal',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }

    /**
     * Usuário que visualizou o conteúdo.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Escopo: filtrar por usuário.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Escopo: filtrar views nas últimas 24 horas.
     */
    public function scopeInLast24Hours(Builder $query): Builder
    {
        return $query->where('viewed_at', '>=', now()->subHours(24));
    }

    /**
     * Escopo: filtrar por conteúdo específico.
     */
    public function scopeForContent(Builder $query, string $contentType, int $contentId, string $tribunal): Builder
    {
        return $query->where('content_type', $contentType)
            ->where('content_id', $contentId)
            ->where('tribunal', $tribunal);
    }
}
