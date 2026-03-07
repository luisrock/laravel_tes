<?php

// ALTER: rename fonaje tables
// ALTER: TST => merged $output['orientacao_jurisprudencia'] and $output['precedente_normativo'] to $output['orientacao_precedente']

namespace App\Http\Controllers;

use App\Jobs\SearchToDbPesquisas;
use App\Models\EditableContent;
use App\Models\Quiz;
use App\Services\SearchDatabaseService;
use App\Services\SearchTribunalRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class SearchPageController extends Controller
{
    public function __construct(
        private SearchDatabaseService $searchDatabaseService,
        private SearchTribunalRegistry $searchTribunalRegistry,
    ) {}

    public function index(Request $request)
    {

        $lista_tribunais = $this->searchTribunalRegistry->allRaw();
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
                $admin = auth()->user()->hasRole('admin');
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
        $firstActiveTab = $this->firstActiveTab($output, $tribunaisExcluidos, $preferredTribunal);
        $activeSubTab = $this->activeSubTab($output, $firstActiveTab);
        $hasAnyResults = $this->hasAnyResults($output, $tribunaisExcluidos);

        // If search is fruitful, save to db for SEO
        if ($hasAnyResults) {
            SearchToDbPesquisas::dispatch($keyword);
        }

        $canonical_url = $hasAnyResults ? url('/').'/tema/'.slugify($keyword) : '';

        // Admin check
        $admin = false;
        if (auth()->check()) {
            $admin = auth()->user()->hasRole('admin');
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
        $url_request = request()->fullUrl();
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
            if (! $this->searchTribunalRegistry->get($tribunal)->usesDatabase()) {
                continue;
            }

            $tribunal_lower = strtolower($tribunal);
            $tribunal_array = $this->searchTribunalRegistry->get($tribunal);
            $output_tribunal = $this->searchDatabaseService->search($keyword, $tribunal_lower, $tribunal_array);
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

    private function firstActiveTab(array $output, array $excludedTribunals = [], ?string $preferred = null): ?string
    {
        if ($preferred && isset($output[$preferred]) && is_array($output[$preferred])) {
            if (! in_array(strtoupper($preferred), $excludedTribunals, true)) {
                return $preferred;
            }
        }

        foreach ($output as $tribunalLower => $data) {
            if (! is_array($data) || in_array(strtoupper($tribunalLower), $excludedTribunals, true)) {
                continue;
            }

            if (($data['total_count'] ?? 0) > 0) {
                return $tribunalLower;
            }
        }

        foreach ($output as $tribunalLower => $data) {
            if (is_array($data) && ! in_array(strtoupper($tribunalLower), $excludedTribunals, true)) {
                return $tribunalLower;
            }
        }

        return null;
    }

    private function activeSubTab(array $output, ?string $activeTab): string
    {
        if (! $activeTab || ! isset($output[$activeTab]) || ! is_array($output[$activeTab])) {
            return 'sumulas';
        }

        return ($output[$activeTab]['sumula']['total'] ?? 0) > 0 ? 'sumulas' : 'teses';
    }

    private function hasAnyResults(array $output, array $excludedTribunals = []): bool
    {
        foreach ($output as $tribunalLower => $data) {
            if (! is_array($data) || in_array(strtoupper($tribunalLower), $excludedTribunals, true)) {
                continue;
            }

            if (($data['total_count'] ?? 0) > 0) {
                return true;
            }
        }

        return false;
    }
}
