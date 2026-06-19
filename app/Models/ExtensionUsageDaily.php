<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Contador diário e agregado de uso da extensão Chrome (LH-7 / passo S5).
 *
 * Não armazena PII nem o termo pesquisado: apenas data, versão da extensão e nº de hits.
 */
class ExtensionUsageDaily extends Model
{
    protected $fillable = [
        'date',
        'extension_version',
        'hits',
    ];

    protected function casts(): array
    {
        return [
            'hits' => 'integer',
        ];
    }

    /**
     * Versão considerada quando o header está ausente ou é inválido.
     */
    public const UNKNOWN_VERSION = 'unknown';

    /**
     * Registra um hit da extensão para a data de hoje, sanitizando a versão recebida.
     *
     * Aceita apenas dígitos e pontos (ex.: "1.0.0"); qualquer outra coisa vira "unknown".
     */
    public static function record(?string $rawVersion): void
    {
        $version = self::sanitizeVersion($rawVersion);

        $row = self::firstOrCreate([
            'date' => now()->toDateString(),
            'extension_version' => $version,
        ]);

        $row->increment('hits');
    }

    /**
     * Normaliza a versão: apenas `[0-9.]` com até 12 chars; senão, "unknown".
     */
    public static function sanitizeVersion(?string $rawVersion): string
    {
        $rawVersion = is_string($rawVersion) ? trim($rawVersion) : '';

        if ($rawVersion !== '' && preg_match('/^[0-9.]{1,12}$/', $rawVersion) === 1) {
            return $rawVersion;
        }

        return self::UNKNOWN_VERSION;
    }
}
