<?php

namespace App\Http\Controllers;

use App\Services\TribunalContentReader;
use Illuminate\Http\JsonResponse;

/**
 * Leitura pública (sem token) do teor de súmulas/teses para a extensão Chrome.
 * Resposta enxuta e estável: { success, data: { tribunal, tipo, numero, texto, url } }.
 * Protegido pelo rate limit do grupo 'api' (60 req/min por IP).
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
                'error' => $tipo === 'sumula' ? 'Súmula não encontrada.' : 'Tese não encontrada.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
