<?php

// ALTER: rename fonaje tables
// ALTER: TST => merged $output['orientacao_jurisprudencia'] and $output['precedente_normativo'] to $output['orientacao_precedente']

namespace App\Http\Controllers;

use App\Jobs\SearchToDbPesquisas;
use App\Models\EditableContent;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class SearchPageController extends Controller
{
    public function index(Request $request)
    {

        $lista_tribunais = Config::get('tes_constants.lista_tribunais');
        $display_pdf = '';

        // Initial view (no search)
        if (empty($request->query()) || (! $request->has('q') && ! $request->has('keyword'))) {
            // Buscar temas mais consultados
            $popular_themes = DB::table('pesquisas')
                ->select('id', 'keyword', 'label', 'slug', 'views_count')
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->where('views_count', '>', 0)
                ->orderBy('views_count', 'desc')
                ->limit(12)
                ->get();

            // Buscar conteúdo editável da home (precedentes)
            $precedentes_home = EditableContent::where('slug', 'precedentes-home')
                ->where('published', true)
                ->first();

            // Verificar se a seção de quizzes deve aparecer na home
            $quizzesHomeVisible = EditableContent::where('slug', 'quizzes-home-visibility')
                ->where('published', true)
                ->exists();

            // Buscar quizzes em destaque apenas se estiver habilitado
            $featured_quizzes = collect();
            if ($quizzesHomeVisible) {
                $featured_quizzes = Quiz::published()
                    ->withCount('questions')
                    ->having('questions_count', '>', 0)
                    ->orderBy('views_count', 'desc')
                    ->limit(3)
                    ->get();
            }

            // Verificar se usuário é admin
            $admin = false;
            if (auth()->check()) {
                if (in_array(auth()->user()->email, config('tes_constants.admins'))) {
                    $admin = true;
                }
            }

            return view('front.search', compact('lista_tribunais', 'display_pdf', 'popular_themes', 'precedentes_home', 'admin', 'featured_quizzes'));
        }

        // User is searching. Validate keyword only (tribunal is optional now)
        $query = $request->validate(
            [
                'q' => 'required_without:keyword|min:3',
                'keyword' => 'required_without:q|min:3',
                'tribunal' => 'nullable|string', // Optional, used for pre-selecting tab
                'print' => 'string|nullable',
            ],
            [
                'q.required' => 'Por favor, defina o(s) termo(s) de busca.',
                'q.min' => 'O termo de busca deve conter ao menos três caracteres.',
            ]
        );

        $keyword = $query['q'] ?? $query['keyword']; // compat with extension
        $preferredTribunal = ! empty($query['tribunal']) ? strtolower($query['tribunal']) : null;
        $pdf = ! empty($query['print']) && $query['print'] == 'pdf';
        $display_pdf = ($pdf) ? 'display:none;' : '';

        // Search all tribunals (unified search)
        $output = $this->searchAllTribunals($keyword, $lista_tribunais);

        // Determine first active tab and sub-tab
        $tribunaisExcluidos = $this->getExcludedTribunals();
        $sem_tese = config('tes_constants.sem_tese', []);
        $firstActiveTab = null;
        $activeSubTab = 'sumulas'; // default

        // If tribunal param provided and valid, use it as preferred tab
        if ($preferredTribunal && isset($output[$preferredTribunal]) && ! in_array(strtoupper($preferredTribunal), $tribunaisExcluidos)) {
            $firstActiveTab = $preferredTribunal;
        }

        // If no preferred or preferred has no results, find first with results
        if (is_null($firstActiveTab)) {
            foreach ($output as $key => $data) {
                if (! is_array($data) || in_array(strtoupper($key), $tribunaisExcluidos)) {
                    continue;
                }
                $count = $data['total_count'] ?? 0;
                if ($count > 0) {
                    $firstActiveTab = $key;
                    break;
                }
            }
        }

        // Fallback: first tribunal in list
        if (is_null($firstActiveTab)) {
            foreach ($output as $key => $data) {
                if (is_array($data) && ! in_array(strtoupper($key), $tribunaisExcluidos)) {
                    $firstActiveTab = $key;
                    break;
                }
            }
        }

        // Determine sub-tab: if active tribunal has sumulas, show sumulas; else teses
        if ($firstActiveTab && isset($output[$firstActiveTab])) {
            $data = $output[$firstActiveTab];
            $hasSumulas = ($data['sumula']['total'] ?? 0) > 0;
            if (! $hasSumulas) {
                $activeSubTab = 'teses';
            }
        }

        // Check if any tribunal has results (for global "no results" message)
        $hasAnyResults = false;
        foreach ($output as $key => $data) {
            if (is_array($data) && ! in_array(strtoupper($key), $tribunaisExcluidos) && ($data['total_count'] ?? 0) > 0) {
                $hasAnyResults = true;
                break;
            }
        }

        // If search is fruitful, save to db for SEO
        if ($hasAnyResults) {
            SearchToDbPesquisas::dispatch($keyword);
        }

        $canonical_url = $hasAnyResults ? url('/').'/tema/'.slugify($keyword) : '';

        // Admin check
        $admin = false;
        if (auth()->check()) {
            if (in_array(auth()->user()->email, config('tes_constants.admins'))) {
                $admin = true;
            }
        }

        $html = view('front.unified-results', compact(
            'lista_tribunais', 'keyword', 'output', 'display_pdf',
            'canonical_url', 'firstActiveTab', 'activeSubTab',
            'hasAnyResults', 'admin', 'sem_tese', 'tribunaisExcluidos'
        ));

        if (! $pdf) {
            return $html;
        }

        // render PDF
        $url_request = url()->full();
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'CSSselectMedia' => 'screen',
            'showWatermarkText' => true,
            'useSubstitutions' => false,
        ]);

        $mpdf->setBasePath($url_request);
        $mpdf->SetWatermarkText('T&S', 0.05);
        $mpdf->SetHeader("$keyword|{DATE d/m/Y}|{PAGENO}");
        $mpdf->SetFooter('|'.url()->current().'|');
        $mpdf->WriteHTML($html->render());
        $mpdf->Output('tes_todos_'.$keyword.'.pdf', 'D');

    }

    /**
     * Search all tribunals (db-based only, excluding TCU).
     * Returns output in the same format as TemaPageController.
     */
    private function searchAllTribunals(string $keyword, array $lista_tribunais): array
    {
        $output = [];
        $tribunaisExcluidos = $this->getExcludedTribunals();
        $tribunais = array_keys($lista_tribunais);

        foreach ($tribunais as $tribunal) {
            // Skip excluded tribunals (e.g. TCU)
            if (in_array($tribunal, $tribunaisExcluidos)) {
                continue;
            }

            // Skip non-db tribunals
            if ($lista_tribunais[$tribunal]['db'] === false) {
                continue;
            }

            $tribunal_lower = strtolower($tribunal);
            $tribunal_upper = strtoupper($tribunal);
            $tribunal_array = $lista_tribunais[$tribunal_upper];
            $output_tribunal = tes_search_db($keyword, $tribunal_lower, $tribunal_array);
            $output[$tribunal_lower] = $output_tribunal;
        }

        return $output;
    }

    /**
     * Returns list of tribunals to exclude from unified search.
     */
    private function getExcludedTribunals(): array
    {
        return ['TCU'];
    }
}
