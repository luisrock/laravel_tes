<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CollectionItem extends Model
{
    /** @use HasFactory<\Database\Factories\CollectionItemFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'collection_id',
        'content_type',
        'content_id',
        'tribunal',
        'order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Coleção à qual o item pertence.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Retorna o conteúdo real (tese ou súmula) do item.
     * O content_type determina qual tabela consultar.
     */
    public function getContent(): ?object
    {
        $table = match ($this->content_type) {
            'tese' => "{$this->tribunal}_teses",
            'sumula' => "{$this->tribunal}_sumulas",
            default => null,
        };

        if ($table === null) {
            return null;
        }

        return \DB::table($table)->where('id', $this->content_id)->first();
    }

    /**
     * Retorna o texto de exibição do item conforme o tipo de conteúdo.
     */
    public static function resolveLabel(string $contentType, ?object $content): string
    {
        if ($content === null) {
            return 'Conteúdo não disponível';
        }

        if ($contentType === 'tese') {
            $numero = $content->numero ?? null;
            $tema = $content->tema_texto ?? $content->tema ?? $content->tese_texto ?? $content->texto ?? $content->tese ?? null;
            $temaInicio = $tema ? Str::limit($tema, 90) : null;

            if ($numero && $temaInicio) {
                return "Tema {$numero} — {$temaInicio}";
            }

            return $numero ? "Tema {$numero}" : ($temaInicio ?? 'Tese sem descrição');
        }

        $numero = $content->numero ?? null;
        $titulo = $content->titulo ?? $content->texto ?? null;

        if ($numero && $titulo) {
            return "Súmula nº {$numero} — {$titulo}";
        }

        return $numero ? "Súmula nº {$numero}" : ($titulo ?? 'Súmula sem descrição');
    }

    /**
     * Mapa estático de rotas por tipo e tribunal.
     *
     * @return array<string, array<string, array{string, string}>>
     */
    private static function routeMap(): array
    {
        return [
            'tese' => [
                'stf' => ['stftesepage', 'tese'],
                'stj' => ['stjtesepage', 'tese'],
                'tst' => ['tsttesepage', 'tese'],
                'tnu' => ['tnutesepage', 'tese'],
            ],
            'sumula' => [
                'stf' => ['stfsumulapage', 'sumula'],
                'stj' => ['stjsumulapage', 'sumula'],
                'tst' => ['tstsumulapage', 'sumula'],
                'tnu' => ['tnusumulapage', 'sumula'],
            ],
        ];
    }

    /**
     * Retorna a URL de detalhe do item, ou null se o tribunal não tiver rota.
     */
    public static function resolveDetailUrl(string $contentType, string $tribunal, ?object $content): ?string
    {
        if ($content === null) {
            return null;
        }

        $numero = $content->numero ?? null;

        if (! $numero) {
            return null;
        }

        $entry = self::routeMap()[$contentType][$tribunal] ?? null;

        if ($entry === null) {
            return null;
        }

        [$routeName, $param] = $entry;

        return route($routeName, [$param => $numero]);
    }
}
