<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Collection extends Model
{
    /** @use HasFactory<\Database\Factories\CollectionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'slug',
        'is_private',
    ];

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
        ];
    }

    /**
     * Gera slug único para o usuário ao criar a coleção.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Collection $collection): void {
            $collection->slug = static::generateUniqueSlug(
                $collection->user_id,
                $collection->title
            );
        });
    }

    /**
     * Gera um slug único dentro do escopo do usuário.
     */
    public static function generateUniqueSlug(int $userId, string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 2;

        while (static::where('user_id', $userId)->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Usuário dono da coleção.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Itens da coleção, ordenados pelo campo order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CollectionItem::class)->orderBy('order');
    }

    /**
     * Verifica se a coleção contém um determinado conteúdo.
     */
    public function hasItem(string $contentType, int $contentId, string $tribunal): bool
    {
        return $this->items()
            ->where('content_type', $contentType)
            ->where('content_id', $contentId)
            ->where('tribunal', $tribunal)
            ->exists();
    }

    /**
     * Escopo: apenas coleções públicas.
     */
    public function scopePublic(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_private', false);
    }
}
