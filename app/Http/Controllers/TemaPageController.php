<?php

namespace App\Http\Controllers;

use App\Services\SearchDatabaseService;
use App\Services\SearchTribunalRegistry;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Log;

class TemaPageController extends Controller
{
    public function __construct(
        private SearchDatabaseService $searchDatabaseService,
        private SearchTribunalRegistry $searchTribunalRegistry,
    ) {}

    public function index($slug = null)
    {

        // Back to the search page, if we do not have a tema
        if (! $slug) {
            return redirect()->route('searchpage');
        }

        $get_keyword = DB::table('pesquisas')->select('id', 'keyword', 'label', 'concept', 'concept_validated_at')->where('slug', '=', $slug)->get();

        if (empty($get_keyword[0]) || empty($get_keyword[0]->keyword)) {
            return redirect()->route('searchpage');
        }

        $id = $get_keyword[0]->id;

        $keyword = $get_keyword[0]->keyword;

        if (! empty($get_keyword[0]->label)) {
            $label = $get_keyword[0]->label;
        } else {
            $label = $keyword;
        }

        $concept = $get_keyword[0]->concept;
        $concept_validated_at = $get_keyword[0]->concept_validated_at;
        if (empty($concept)) {
            $concept = '';
        }
        if (empty($concept_validated_at)) {
            $concept_validated_at = '';
        }

        // views_count é atualizado exclusivamente pelo cron matomo:sync

        // Buscar temas relacionados para internal linking
        $related_themes = $this->getRelatedThemes($id, $keyword);

        $lista_tribunais = $this->searchTribunalRegistry->allRaw();
        $tribunais = array_keys($lista_tribunais);
        $display_pdf = '';

        // dd($tribunais);

        $output = [];

        // Getting the results by querying tes db for all tribunais (except the ones with API, excluding STF)
        // TODO: db for all tribunais
        foreach ($tribunais as $tribunal) {
            if (! $this->searchTribunalRegistry->get($tribunal)->usesDatabase()) {
                continue;
            }

            $output_tribunal = [];

            $tribunal_lower = strtolower($tribunal);
            $tribunal_array = $this->searchTribunalRegistry->get($tribunal);
            $output_tribunal = $this->searchDatabaseService->search($keyword, $tribunal_lower, $tribunal_array);
            $output[$tribunal_lower] = $output_tribunal;
        } // END foreach

        $output = $this->withLegacyResultsAliases($output);

        // Redirect 301 para home se nenhum resultado real (SEO)
        $total_results = $this->totalResults($output, ['sumula', 'tese', 'repercussao', 'repetitivos', 'orientacao_precedente']);
        if ($total_results === 0) {
            return redirect('/', 301);
        }

        // Gerar meta description dinâmica otimizada para SEO
        $description = $this->generateMetaDescription($label, $output);

        // Prepare collections for view
        $sumulas = $this->collectLegacyItems($output, ['sumula']);
        $teses = $this->collectLegacyItems($output, ['tese', 'repercussao', 'repetitivos']);

        $related_temas = $related_themes; // Alias to match view variable name

        $admin = false;
        if (auth()->check()) {
            $useremail = auth()->user()->email;
            if (in_array($useremail, ['mauluis@gmail.com', 'trator70@gmail.com', 'ivanaredler@gmail.com'])) {
                $admin = true;
            }
        }

        $concept_validated = ! empty($concept_validated_at);

        $html = view('front.tema', compact('id', 'keyword', 'label', 'output', 'display_pdf', 'description', 'concept', 'concept_validated_at', 'concept_validated', 'related_themes', 'admin', 'sumulas', 'teses', 'related_temas'));

        return $html;

    } // end public function

    /**
     * Busca temas relacionados para internal linking
     * Usa palavras-chave similares para encontrar temas relacionados
     */
    private function getRelatedThemes($current_id, $keyword, $limit = 6)
    {
        try {
            // Extrair palavras significativas (mais de 3 caracteres)
            $words = explode(' ', strtolower($keyword));
            $main_words = array_filter($words, function ($w) {
                return strlen(trim($w)) > 3;
            });

            // Se não houver palavras significativas, retornar vazio
            if (empty($main_words)) {
                return collect([]);
            }

            // Pegar as 3 primeiras palavras mais significativas
            $main_words = array_slice($main_words, 0, 3);

            // Construir query
            $query = DB::table('pesquisas')
                ->select('id', 'keyword', 'label', 'slug')
                ->where('id', '!=', $current_id)
                ->whereNotNull('slug')
                ->where('slug', '!=', '');

            // Adicionar condições OR para cada palavra
            $query->where(function ($q) use ($main_words) {
                foreach ($main_words as $word) {
                    $word = trim($word);
                    if (! empty($word)) {
                        $q->orWhere('keyword', 'LIKE', "%{$word}%");
                        $q->orWhere('label', 'LIKE', "%{$word}%");
                    }
                }
            });

            return $query->limit($limit)->get();

        } catch (Exception $e) {
            // Em caso de erro, retornar coleção vazia para não quebrar a página
            Log::error('Erro ao buscar temas relacionados: '.$e->getMessage());

            return collect([]);
        }
    }

    /**
     * Gera meta description otimizada baseada nos resultados reais
     * Focada em aumentar CTR no Google Search
     */
    private function generateMetaDescription($label, $output)
    {
        try {
            // Contar resultados por tipo
            $total_sumulas = 0;
            $total_teses = 0;
            $tribunais_com_resultado = [];

            foreach ($output as $tribunal => $data) {
                // Ignorar 'total_count' e outras keys que não são tribunais
                if (! is_array($data) || $tribunal === 'total_count') {
                    continue;
                }

                $tribunal_upper = strtoupper($tribunal);

                // Contar súmulas - estrutura correta: $data['sumula']['total']
                if (isset($data['sumula']['total']) && $data['sumula']['total'] > 0) {
                    $total_sumulas += $data['sumula']['total'];
                    $tribunais_com_resultado[] = $tribunal_upper;
                }

                // Contar teses/repercussão/repetitivos - estrutura correta: $data['tese']['total']
                if (isset($data['tese']['total']) && $data['tese']['total'] > 0) {
                    $total_teses += $data['tese']['total'];
                    if (! in_array($tribunal_upper, $tribunais_com_resultado)) {
                        $tribunais_com_resultado[] = $tribunal_upper;
                    }
                }

                // Para STF: $data['repercussao']['total']
                if (isset($data['repercussao']['total']) && $data['repercussao']['total'] > 0) {
                    $total_teses += $data['repercussao']['total'];
                    if (! in_array($tribunal_upper, $tribunais_com_resultado)) {
                        $tribunais_com_resultado[] = $tribunal_upper;
                    }
                }

                // Para STJ: $data['repetitivos']['total']
                if (isset($data['repetitivos']['total']) && $data['repetitivos']['total'] > 0) {
                    $total_teses += $data['repetitivos']['total'];
                    if (! in_array($tribunal_upper, $tribunais_com_resultado)) {
                        $tribunais_com_resultado[] = $tribunal_upper;
                    }
                }
            }

            $total_resultados = $total_sumulas + $total_teses;
            $tribunais_com_resultado = array_unique($tribunais_com_resultado);

            // Construir description otimizada
            if ($total_resultados > 0) {
                $description = $label.': ';

                // Adicionar contagem de resultados com singular/plural correto
                if ($total_teses > 0 && $total_sumulas > 0) {
                    $teses_texto = $total_teses === 1 ? 'tese' : 'teses';
                    $sumulas_texto = $total_sumulas === 1 ? 'súmula' : 'súmulas';
                    $description .= "Encontre {$total_teses} {$teses_texto} e {$total_sumulas} {$sumulas_texto}";
                } elseif ($total_teses > 0) {
                    $teses_texto = $total_teses === 1 ? 'tese jurisprudencial' : 'teses jurisprudenciais';
                    $description .= "Encontre {$total_teses} {$teses_texto}";
                } else {
                    $sumulas_texto = $total_sumulas === 1 ? 'súmula' : 'súmulas';
                    $description .= "Encontre {$total_sumulas} {$sumulas_texto}";
                }

                // Adicionar tribunais
                if (count($tribunais_com_resultado) > 0) {
                    $description .= ' de '.implode(', ', $tribunais_com_resultado);
                }

                // Adicionar data de atualização
                $description .= '. Atualizado em '.date('d/m/Y').'.';

            } else {
                // Fallback para quando não há resultados
                $description = $label.' - Pesquise súmulas e teses jurisprudenciais nos tribunais superiores (STF, STJ, TST, TNU). Jurisprudência atualizada.';
            }

            // Garantir que não ultrapasse 160 caracteres (limite ideal para Google)
            if (strlen($description) > 160) {
                $description = substr($description, 0, 157).'...';
            }

            return $description;

        } catch (Exception $e) {
            // Fallback em caso de erro
            Log::error('Erro ao gerar meta description: '.$e->getMessage());

            return $label.' - Teses e Súmulas dos tribunais superiores. Atualizado em '.date('d/m/Y').'.';
        }
    }

    private function withLegacyResultsAliases(array $output): array
    {
        foreach ($output as $tribunalLower => &$data) {
            if (! is_array($data)) {
                continue;
            }

            foreach ($data as $sectionName => &$section) {
                if ($sectionName === 'total_count' || ! is_array($section)) {
                    continue;
                }

                if (! array_key_exists('results', $section)) {
                    $section['results'] = $section['hits'] ?? [];
                }
            }
            unset($section);
        }
        unset($data);

        return $output;
    }

    private function totalResults(array $output, array $sectionNames): int
    {
        $total = 0;

        foreach ($output as $data) {
            if (! is_array($data)) {
                continue;
            }

            foreach ($sectionNames as $sectionName) {
                $total += $data[$sectionName]['total'] ?? 0;
            }
        }

        return $total;
    }

    private function collectLegacyItems(array $output, array $sectionNames): Collection
    {
        $items = collect();

        foreach ($output as $tribunalLower => $data) {
            if (! is_array($data)) {
                continue;
            }

            $tribunalUpper = strtoupper($tribunalLower);

            foreach ($sectionNames as $sectionName) {
                $section = $data[$sectionName] ?? null;
                $hits = is_array($section) ? ($section['hits'] ?? $section['results'] ?? []) : [];

                foreach ($hits as $item) {
                    $legacyItem = is_array($item) ? (object) $item : $item;
                    $legacyItem->tribunal = $tribunalUpper;
                    $items->push($legacyItem);
                }
            }
        }

        return $items;
    }
}
