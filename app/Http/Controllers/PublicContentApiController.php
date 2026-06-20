<?php

namespace App\Http\Controllers;

use App\Services\TribunalContentReader;
use Illuminate\Http\JsonResponse;

/**
 * Leitura pública (sem token) do teor de súmulas/teses (e súmulas vinculantes do
 * STF) para a extensão Chrome. Resposta enxuta e estável; protegido pelo rate
 * limit do grupo 'api' (60 req/min por IP).
 *
 * - Súmula:            { success, data: { tribunal, tipo, numero, texto, situacao, url } }
 * - Tese:             { success, data: { tribunal, tipo, numero, texto, tema, tese, situacao, url } }
 * - Súmula vinculante: { success, data: { tribunal, tipo, numero, texto, situacao, url } }
 */
class PublicContentApiController extends Controller
{
    public function __construct(private TribunalContentReader $reader) {}

    public function getSumula(string $tribunal, string $numero): JsonResponse
    {
        return $this->respond('sumula', $tribunal, $numero);
    }

    public function getTese(string $tribunal, string $numero): JsonResponse
    {
        return $this->respond('tese', $tribunal, $numero);
    }

    public function getSumulaVinculante(string $tribunal, string $numero): JsonResponse
    {
        return $this->respond('sumula-vinculante', $tribunal, $numero);
    }

    private function respond(string $tipo, string $tribunal, string $numero): JsonResponse
    {
        $tribunalUpper = strtoupper($tribunal);

        if (! $this->reader->supports($tipo, $tribunalUpper)) {
            return response()->json([
                'success' => false,
                'error' => 'Tribunal não suportado para este conteúdo.',
            ], 404);
        }

        if (! is_numeric($numero)) {
            return response()->json([
                'success' => false,
                'error' => 'Número deve ser um valor numérico.',
            ], 400);
        }

        $data = $this->reader->find($tipo, $tribunalUpper, (int) $numero);

        if ($data === null) {
            return response()->json([
                'success' => false,
                'error' => match ($tipo) {
                    'tese' => 'Tese não encontrada.',
                    'sumula-vinculante' => 'Súmula vinculante não encontrada.',
                    default => 'Súmula não encontrada.',
                },
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
