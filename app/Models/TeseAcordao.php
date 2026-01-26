<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class TeseAcordao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tese_acordaos';

    protected $fillable = [
        'tese_id',
        'tribunal',
        'numero_acordao',
        'tipo',
        'label',
        's3_key',
        'filename_original',
        'file_size',
        'mime_type',
        'checksum',
        'version',
        'uploaded_by',
        'upload_ip',
        'deleted_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'version' => 'integer',
        'uploaded_by' => 'integer',
        'deleted_by' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relacionamento com usuário que fez upload
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Relacionamento com usuário que deletou
     */
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Busca dados da tese com cache (evita N+1)
     * Cache de 1 hora por tese
     */
    public function getTese()
    {
        $cacheKey = "tese_{$this->tribunal}_{$this->tese_id}";

        return Cache::remember($cacheKey, 3600, function () {
            $table = $this->tribunal === 'STF' ? 'stf_teses' : 'stj_teses';
            return DB::table($table)->where('id', $this->tese_id)->first();
        });
    }

    /**
     * Gera presigned URL com expiração de 1 hora
     * Cache de 55 minutos para evitar múltiplas requisições
     */
    public function getPresignedUrlAttribute(): ?string
    {
        if (!$this->s3_key) {
            return null;
        }

        $cacheKey = "presigned_url_{$this->id}";

        return Cache::remember($cacheKey, 3300, function () { // 55 minutos (menos que 1h)
            try {
                return Storage::disk('s3')->temporaryUrl(
                    $this->s3_key,
                    now()->addHour()
                );
            } catch (\Exception $e) {
                \Log::error('Erro ao gerar presigned URL', [
                    'acordao_id' => $this->id,
                    's3_key' => $this->s3_key,
                    'error' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /**
     * Scope para buscar acórdãos de uma tese específica
     */
    public function scopeForTese($query, int $teseId, string $tribunal)
    {
        return $query->where('tese_id', $teseId)
                    ->where('tribunal', $tribunal);
    }

    /**
     * Scope para buscar por tipo
     */
    public function scopeOfType($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Verifica se já existe acórdão com mesmo checksum (duplicata)
     */
    public static function findDuplicate(string $checksum, int $teseId, string $tribunal): ?self
    {
        return self::where('checksum', $checksum)
                   ->where('tese_id', $teseId)
                   ->where('tribunal', $tribunal)
                   ->first();
    }
}
