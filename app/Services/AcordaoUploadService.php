<?php

namespace App\Services;

use App\Models\TeseAcordao;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class AcordaoUploadService
{
    private const MAX_FILE_SIZE = 52428800; // 50MB em bytes
    private const MAX_FILES_PER_TESE = 10;
    private const ALLOWED_MIME_TYPES = ['application/pdf'];

    /**
     * Upload de acórdão com validações robustas
     *
     * @param UploadedFile $file
     * @param array $data
     * @param User $user
     * @return TeseAcordao
     * @throws Exception Se validações falharem
     */
    public function upload(UploadedFile $file, array $data, User $user): TeseAcordao
    {
        // 1. Validação de tamanho
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new Exception('Arquivo excede o limite de 50MB');
        }

        // 2. Validação de MIME type real (não apenas extensão)
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new Exception('Apenas arquivos PDF são permitidos');
        }

        // 3. Validação de quantidade por tese
        $existingCount = TeseAcordao::where('tese_id', $data['tese_id'])
            ->where('tribunal', $data['tribunal'])
            ->whereNull('deleted_at')
            ->count();

        if ($existingCount >= self::MAX_FILES_PER_TESE) {
            throw new Exception("Limite de " . self::MAX_FILES_PER_TESE . " arquivos por tese atingido");
        }

        // 4. Calcular checksum (SHA-256)
        $fileContent = file_get_contents($file->getRealPath());
        $checksum = hash('sha256', $fileContent);

        // 5. Verificar duplicata
        $duplicate = TeseAcordao::findDuplicate(
            $checksum,
            $data['tese_id'],
            $data['tribunal']
        );

        if ($duplicate) {
            throw new Exception('Este arquivo já foi enviado anteriormente');
        }

        // 6. Determinar versão (se já existe acórdão com mesmo número e tipo)
        $maxVersion = TeseAcordao::where('tese_id', $data['tese_id'])
            ->where('tribunal', $data['tribunal'])
            ->where('numero_acordao', $data['numero_acordao'])
            ->where('tipo', $data['tipo'])
            ->whereNull('deleted_at')
            ->max('version') ?? 0;

        $version = $maxVersion + 1;

        // 7. Gerar nome do arquivo
        $filename = $this->generateFilename(
            $data['tipo'],
            $data['numero_acordao'],
            $version
        );

        // 8. Gerar S3 key
        $s3Key = sprintf(
            'acordaos/%s/%d/%s',
            strtolower($data['tribunal']),
            $data['tese_id'],
            $filename
        );

        // 9. Upload para S3
        try {
            Storage::disk('s3')->put($s3Key, $fileContent, [
                'ContentType' => 'application/pdf',
                'Metadata' => [
                    'tese_id' => (string)$data['tese_id'],
                    'tribunal' => $data['tribunal'],
                    'uploaded_by' => (string)$user->id,
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao fazer upload para S3', [
                's3_key' => $s3Key,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Erro ao fazer upload do arquivo para o servidor');
        }

        // 10. Salvar no banco
        $acordao = TeseAcordao::create([
            'tese_id' => $data['tese_id'],
            'tribunal' => $data['tribunal'],
            'numero_acordao' => $data['numero_acordao'],
            'tipo' => $data['tipo'],
            'label' => $data['label'] ?? null,
            's3_key' => $s3Key,
            'filename_original' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $mimeType,
            'checksum' => $checksum,
            'version' => $version,
            'uploaded_by' => $user->id,
            'upload_ip' => request()->ip(),
        ]);

        // 11. Log de auditoria
        Log::info('Acórdão enviado', [
            'acordao_id' => $acordao->id,
            'tese_id' => $data['tese_id'],
            'tribunal' => $data['tribunal'],
            'uploaded_by' => $user->id,
            'file_size' => $file->getSize(),
            's3_key' => $s3Key,
        ]);

        return $acordao;
    }

    /**
     * Soft delete com período de retenção de 30 dias
     *
     * @param TeseAcordao $acordao
     * @param User $user
     * @return bool
     */
    public function delete(TeseAcordao $acordao, User $user): bool
    {
        $acordao->deleted_by = $user->id;
        $acordao->save();
        $acordao->delete(); // Soft delete

        Log::info('Acórdão deletado (soft)', [
            'acordao_id' => $acordao->id,
            'deleted_by' => $user->id,
            'tese_id' => $acordao->tese_id,
            'tribunal' => $acordao->tribunal,
        ]);

        return true;
    }

    /**
     * Exclusão definitiva do S3 (após período de retenção)
     * NOTA: Será implementado via job agendado na Fase 2
     *
     * @param TeseAcordao $acordao
     * @return bool
     */
    public function forceDelete(TeseAcordao $acordao): bool
    {
        if (Storage::disk('s3')->exists($acordao->s3_key)) {
            Storage::disk('s3')->delete($acordao->s3_key);
        }

        $acordao->forceDelete(); // Hard delete do banco

        Log::info('Acórdão deletado definitivamente', [
            'acordao_id' => $acordao->id,
            's3_key' => $acordao->s3_key,
        ]);

        return true;
    }

    /**
     * Gera nome do arquivo padronizado
     *
     * @param string $tipo
     * @param string $numeroAcordao
     * @param int $version
     * @return string
     */
    private function generateFilename(string $tipo, string $numeroAcordao, int $version): string
    {
        // Sanitizar tipo e número do acórdão
        $tipoSlug = strtolower(str_replace([' ', 'ç', 'ã', 'õ'], ['-', 'c', 'a', 'o'], $tipo));
        $tipoSlug = preg_replace('/[^a-z0-9-]/', '', $tipoSlug);
        
        $numeroSlug = strtolower(str_replace(' ', '-', $numeroAcordao));
        $numeroSlug = preg_replace('/[^a-z0-9-]/', '', $numeroSlug);

        return sprintf('%s-%s-v%d.pdf', $tipoSlug, $numeroSlug, $version);
    }
}
