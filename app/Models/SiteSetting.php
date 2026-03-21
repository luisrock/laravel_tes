<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    /** @use HasFactory<\Database\Factories\SiteSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Obtém o valor de uma configuração com cache de 1 hora.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember(
            "site_setting:{$key}",
            3600,
            fn () => static::where('key', $key)->value('value') ?? $default
        );
    }

    /**
     * Define o valor de uma configuração e invalida o cache.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("site_setting:{$key}");
    }

    /**
     * Remove o cache de uma key específica.
     */
    public static function clearCache(string $key): void
    {
        Cache::forget("site_setting:{$key}");
    }

    /**
     * Interpreta o valor armazenado como booleano (ex.: '1'/'0').
     *
     * Importante: em PHP, (bool) '0' é true — não usar cast direto em strings vindas da DB.
     */
    public static function getAsBool(string $key, bool $default): bool
    {
        $raw = static::get($key, null);

        if ($raw === null || $raw === '') {
            return $default;
        }

        if (is_bool($raw)) {
            return $raw;
        }

        if (is_int($raw) || is_float($raw)) {
            return (int) $raw !== 0;
        }

        $normalized = strtolower(trim((string) $raw));

        return match ($normalized) {
            '1', 'true', 'yes', 'on' => true,
            '0', 'false', 'no', 'off' => false,
            default => (bool) filter_var($normalized, FILTER_VALIDATE_BOOLEAN),
        };
    }
}
