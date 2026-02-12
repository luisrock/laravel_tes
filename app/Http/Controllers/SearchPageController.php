<?php
//ALTER: rename fonaje tables
//ALTER: TST => merged $output['orientacao_jurisprudencia'] and $output['precedente_normativo'] to $output['orientacao_precedente']

namespace App\Http\Controllers;

use Mpdf\Mpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Jobs\SearchToDbPesquisas;
use App\Models\EditableContent;
use App\Models\Quiz;

class SearchPageController extends Controller
{

    public function index(Request $request)
    {

        $lista_tribunais = Config::get('tes_constants.lista_tribunais');
        $lista_tribunais_string = implode(",", array_keys($lista_tribunais));
        $display_pdf = '';
        //Initial view (no search)
        if (empty($request->query())) {
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

        //User is searching. Prepare and return results
        $query = $request->validate(
            [
                //compat with extension
                'q' => 'required_without:keyword|min:3',
                'keyword' => 'required_without:q|min:3',
                'tribunal' => 'required|in:' . $lista_tribunais_string,
                'print' => 'string|nullable'
            ],
            [
                'q.required' => 'Por favor, defina o(s) termo(s) de busca.',
                'q.min' => 'O termo de busca deve conter ao menos três caracteres.',
                'tribunal.required' => 'Por favor, indique o tribunal/órgão para a sua pesquisa.',
                'tribunal.in' => 'Por favor, indique um tribunal/órgão válido para a sua pesquisa.',
            ]
        );

        $keyword = $query['q'] ?? $query['keyword']; //compat with extension
        $tribunal = $query['tribunal'];
        $pdf = !empty($query['print']) && 'pdf' == $query['print'];
        $display_pdf = ($pdf) ? 'display:none;' : '';
        $tribunal_lower = strtolower($tribunal);
        $tribunal_upper = strtoupper($tribunal);
        $tribunal_array = $lista_tribunais[$tribunal_upper];
        $results_view = 'front.results.' . $tribunal_lower;
        $output = [];

        //search in db (not through tribunal API)
        if ($lista_tribunais[$tribunal]['db']) {
            //Getting the results by querying tes db
            $output = tes_search_db($keyword, $tribunal_lower, $tribunal_array);
        } else {
            //Getting the results by calling the tribunal API
            //tratando keyword
            $keyword = buildFinalSearchStringForApi($keyword, $tribunal_upper);
            $output = call_request_api($tribunal_lower, $keyword);

        }

        // dd($output);

        //dd($output);
        $canonical_url = '';

        //If search is fruitful, save it to db in order to generate page (SEO purposes)
        if (!empty($output['total_count']) && $output['total_count'] > 0) {
            SearchToDbPesquisas::dispatch($keyword);
            $canonical_url = url('/') . '/tema/' . slugify($keyword);
        }

        //obs: when searching by calling the tribunal API and getting 500 error, output will be a string...
        $html = view($results_view, compact('lista_tribunais', 'keyword', 'tribunal', 'output', 'display_pdf', 'canonical_url'));
        if (!$pdf) {
            return $html;
        }

        // render PDF

        $url_request = url()->full();
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'CSSselectMedia' => 'screen',
            'showWatermarkText' => true,
            'useSubstitutions' => false
        ]);

        $mpdf->setBasePath($url_request);
        $mpdf->SetWatermarkText('T&S', 0.05);
        $mpdf->SetHeader("$keyword|{DATE d/m/Y}|{PAGENO}");
        $mpdf->SetFooter('|' . url()->current() . '|');
        $mpdf->WriteHTML($html->render());
        $mpdf->Output('tes_' . $tribunal . '_' . $keyword . '.pdf', 'D');

    } //end public function
}