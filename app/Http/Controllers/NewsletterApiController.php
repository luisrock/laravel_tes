<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;

class NewsletterApiController extends Controller
{
    /**
     * Lista newsletters com diversos filtros de busca.
     * 
     * Parâmetros de query:
     * - latest: boolean - retorna apenas a última newsletter
     * - numero: int - busca pelo número da edição (ex: 107)
     * - tema: int - busca newsletters que mencionam o tema (STF ou STJ)
     * - tribunal: string - filtra por tribunal quando usado com tema (STF, STJ)
     * - julgado: string - busca por identificador de julgado (ex: "ADI 7754")
     * - assunto: string - busca por texto no assunto da semana
     * - since_number: int - retorna newsletters com número > since_number (sync incremental)
     * - per_page: int - itens por página (padrão: 10)
     * - page: int - número da página
     */
    public function index(Request $request)
    {
        // 1. Última edição
        if ($request->has('latest') && $request->boolean('latest')) {
            $latest = Newsletter::orderBy('sent_at', 'desc')->first();
            
            if ($latest) {
                return response()->json([
                    'success' => true,
                    'data' => [$this->formatNewsletter($latest)]
                ]);
            }
            
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $query = Newsletter::query();

        // 2. Busca por número da edição (extraído do subject)
        if ($request->has('numero')) {
            $numero = $request->input('numero');
            // Busca "#107" ou "#107 " no subject
            $query->where('subject', 'REGEXP', '#' . $numero . '([^0-9]|$)');
        }

        // 3. Sincronização incremental (since_number)
        // Filtragem será feita após a query para maior precisão
        $sinceNumber = null;
        if ($request->has('since_number')) {
            $sinceNumber = (int) $request->input('since_number');
        }

        // 4. Busca por Tema
        if ($request->has('tema')) {
            $tema = $request->input('tema');
            $tribunal = $request->input('tribunal');
            
            if ($tribunal) {
                // Busca específica por tribunal + tema
                $tribunal = strtoupper($tribunal);
                $query->where('html_content', 'REGEXP', $tribunal . '[,]?[[:space:]]*Tema[^0-9]*' . $tema . '[^0-9]');
            } else {
                // Busca tema em qualquer tribunal
                $query->where('html_content', 'REGEXP', 'Tema[^0-9]*' . $tema . '[^0-9]');
            }
        }

        // 5. Busca por Julgado
        if ($request->has('julgado')) {
            $julgado = $request->input('julgado');
            // Remove espaços e normaliza (ex: "ADI 7754" ou "ADI7754" ou "7754")
            $julgado = preg_replace('/\s+/', '', $julgado);
            
            // Se for apenas número, busca o número
            if (is_numeric($julgado)) {
                $query->where('html_content', 'REGEXP', '(ADI|RE|PET|ARE|RCL|MS|HC|ADPF|ACO|IF)[[:space:]]*' . $julgado . '[^0-9]');
            } else {
                // Se tiver tipo + número (ex: ADI7754), separa e busca
                if (preg_match('/^([A-Za-z]+)([0-9]+)$/', $julgado, $matches)) {
                    $tipo = strtoupper($matches[1]);
                    $num = $matches[2];
                    $query->where('html_content', 'REGEXP', $tipo . '[[:space:]]*' . $num . '[^0-9]');
                } else {
                    // Busca literal
                    $query->where('html_content', 'LIKE', '%' . $julgado . '%');
                }
            }
        }

        // 6. Busca por Assunto da Semana
        if ($request->has('assunto')) {
            $assunto = $request->input('assunto');
            // Busca na seção "ASSUNTO DA SEMANA" ou no subject
            $query->where(function($q) use ($assunto) {
                $q->where('html_content', 'LIKE', '%ASSUNTO DA SEMANA%' . $assunto . '%')
                  ->orWhere('subject', 'LIKE', '%' . $assunto . '%');
            });
        }

        // Ordenação: mais recentes primeiro
        $query->orderBy('sent_at', 'desc');

        // Se since_number está definido, busca todas e filtra em PHP
        if ($sinceNumber !== null) {
            $allNewsletters = $query->get();
            
            $filtered = $allNewsletters->filter(function($n) use ($sinceNumber) {
                $numero = $this->extractNumero($n->subject);
                return $numero !== null && $numero > $sinceNumber;
            });
            
            return response()->json([
                'success' => true,
                'data' => $filtered->values()->map(fn($n) => $this->formatNewsletter($n)),
                'pagination' => [
                    'total' => $filtered->count(),
                    'count' => $filtered->count(),
                ]
            ]);
        }

        // Paginação
        $perPage = min((int) $request->input('per_page', 10), 100); // máximo 100
        $newsletters = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => collect($newsletters->items())->map(fn($n) => $this->formatNewsletter($n)),
            'pagination' => [
                'current_page' => $newsletters->currentPage(),
                'last_page' => $newsletters->lastPage(),
                'per_page' => $newsletters->perPage(),
                'total' => $newsletters->total(),
            ]
        ]);
    }

    /**
     * Formata uma newsletter para a resposta da API.
     */
    private function formatNewsletter(Newsletter $newsletter): array
    {
        $numero = $this->extractNumero($newsletter->subject);

        return [
            'id' => $newsletter->id,
            'numero' => $numero,
            'subject' => $newsletter->subject,
            'slug' => $newsletter->slug,
            'data_envio' => $newsletter->sent_at ? $newsletter->sent_at->format('Y-m-d') : null,
            'html_content' => $newsletter->html_content,
            'plain_text' => $newsletter->plain_text,
            'url' => 'https://tesesesumulas.com.br/newsletter/' . $newsletter->slug,
            'created_at' => $newsletter->created_at->toIso8601String(),
            'updated_at' => $newsletter->updated_at->toIso8601String(),
        ];
    }

    /**
     * Extrai o número da edição do subject.
     */
    private function extractNumero(string $subject): ?int
    {
        if (preg_match('/#(\d+)/', $subject, $matches)) {
            return (int) $matches[1];
        }
        
        // Primeira newsletter não tinha número - é a edição #1
        if (str_starts_with($subject, 'Sequestro de Verbas')) {
            return 1;
        }
        
        return null;
    }
}
