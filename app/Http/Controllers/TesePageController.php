<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Log;

class TesePageController extends Controller
{
    public function index()
    {
        $route = request()->route()->getName();

        $tribunal = '';
        $tribunal_nome_completo = '';
        $table = '';
        $alltesesroute = '';
        if ($route == 'stftesepage') {
            $tribunal = 'STF';
            $tribunal_nome_completo = 'Supremo Tribunal Federal';
            $table = 'stf_teses';
            $alltesesroute = 'stfalltesespage';
        } elseif ($route == 'stjtesepage') {
            $tribunal = 'STJ';
            $tribunal_nome_completo = 'Superior Tribunal de Justiça';
            $table = 'stj_teses';
            $alltesesroute = 'stjalltesespage';
        } else {
            return redirect()->route('searchpage');
        }

        $tese_id = intval(request()->route('tese'));
        // if no tese id, redirect to all teses page
        if (! $tese_id) {
            return redirect()->route($alltesesroute);
        }

        $tese = DB::table($table)
            // select all fields
            ->select('*')
            ->where('id', $tese_id)
            ->first();

        if (! $tese) {
            return redirect()->route($alltesesroute);
        }

        $tese_isCancelada = 0;
        $have_tese = ! empty($tese->tese_texto);

        // dd($tese);
        if ($tribunal == 'STF') {

            if (! empty($tese->tema_texto)) {
                if (! Str::endsWith($tese->tema_texto, '.')) {
                    $tese->tema_texto = $tese->tema_texto.'.';
                }
            }
            if ($have_tese) {
                if (! Str::endsWith($tese->tese_texto, '.')) {
                    $tese->tese_texto = $tese->tese_texto.'.';
                }
            } else {
                $tese->tese_texto = '[aguarda julgamento]';
            }
        } elseif ($tribunal == 'STJ') {
            $tese->tema_texto = $tese->numero.' - '.$tese->tema;
        }

        $text = "$tribunal, Tema {$tese->tema_texto}";
        if (! empty($tese->tese_texto)) {
            $text .= " TESE: {$tese->tese_texto}";
        } else {
            $text .= ' TESE: [aguarda julgamento]';
        }
        $text = trim($text);
        // remove double spaces inside
        $text = preg_replace('/\s+/', ' ', $text);

        if ($tribunal == 'STF') {
            // add to_be_copied property
            $text .= ' '.$tese->relator.', '.$tese->acordao;
            if (! empty($tese->situacao)) {
                $text .= " ($tese->situacao). ";
            } else {
                $text .= '. ';
            }
            $tese->tempo = '';
            if (isset($tese->aprovadaEm) && $tese->aprovadaEm) {
                $tese->tempo = "Aprovada em {$tese->aprovadaEm}";
            }

            if ($tese->tempo) {
                $text .= $tese->tempo;
            }

            $tese->titulo = "TEMA {$tese->numero}";
            $tese->questao = 'QUESTÃO: '.preg_replace('/^\d+ - /', '', $tese->tema_texto);
            $tese->texto = $tese->tese_texto;
            $tese->text_muted = "$tese->relator, $tese->acordao ($tese->situacao). $tese->tempo.";

            // Gerar link de fallback se estiver vazio, nulo ou com hífen
            if ((empty($tese->link) || $tese->link == '-') && ! empty($tese->acordao)) {
                // Usar o mesmo formato dos links válidos: busca por acordão
                $tese->link = 'https://jurisprudencia.stf.jus.br/pages/search?base=acordaos&sinonimo=true&plural=true&page=1&&pageSize=10&sort=_score&sortBy=desc&isAdvance=true&classeNumeroIncidente='.urlencode($tese->acordao);
            }
        } elseif ($tribunal == 'STJ') {

            // add to_be_copied property
            $text .= ' '.$tese->orgao.', situação: '.$tese->situacao.'. ';
            $tese->tempo = '';
            if (isset($tese->atualizadaEm) && $tese->atualizadaEm) {
                $tese->tempo = "Última atualização: {$tese->atualizadaEm}";
            }
            if ($tese->tempo) {
                $text .= $tese->tempo;
            }
            $tese->titulo = "TEMA {$tese->numero}";
            $tese->questao = $tese->tema;
            $tese->texto = $tese->tese_texto;
            $tese->text_muted = "{$tese->orgao}. Situação: {$tese->situacao} (última atualização em {$tese->atualizadaEm}).";

            if ($tese->situacao == 'Cancelado' || $tese->situacao == 'Cancelada') {
                $tese_isCancelada = 1;
            }
            if (empty($tese->link)) {
                $tese->link = "https://processo.stj.jus.br/repetitivos/temas_repetitivos/pesquisa.jsp?novaConsulta=true&tipo_pesquisa=T&cod_tema_inicial={$tese->numero}&cod_tema_final={$tese->numero}";
            }
        }

        // if there is no "." at the end of the text, add it
        $text = trim($text);
        if (! Str::endsWith($text, '.')) {
            $text = $text.'.';
        }

        if ($have_tese) {
            $tese->to_be_copied = $text;
        } else {
            $tese->to_be_copied = $text;
        }

        $display_pdf = false;
        $label = "TEMA {$tese->numero} do $tribunal_nome_completo - $tribunal";

        // Gerar meta description otimizada
        $description = $this->generateMetaDescription($tribunal, $tese->numero, $tese->tema_texto, $tese->tese_texto);

        // Breadcrumb
        $breadcrumb = [
            ['name' => 'Início', 'url' => url('/')],
            ['name' => 'Índice', 'url' => url('/index')],
            ['name' => "Teses $tribunal", 'url' => route($alltesesroute)],
            ['name' => "Tema {$tese->numero}", 'url' => null],
        ];

        $admin = false;
        if (auth()->check()) {
            // check the email
            $useremail = auth()->user()->email;
            if (in_array($useremail, ['mauluis@gmail.com', 'trator70@gmail.com', 'ivanaredler@gmail.com'])) {
                $admin = true;
            }
        }

        // Buscar temas relacionados baseados em palavras-chave similares
        $related_themes = $this->getRelatedThemes($tese->tema_texto ?? $tese->tese_texto ?? '', $tese->numero);

        // Buscar quizzes relacionados (por tribunal ou categoria)
        $related_quizzes = $this->getRelatedQuizzes($tribunal, $tese->tema_texto ?? $tese->tese_texto ?? '');

        // dd($teses);
        return view('front.tese', compact('tribunal', 'tribunal_nome_completo', 'tese', 'label', 'description', 'admin', 'display_pdf', 'alltesesroute', 'breadcrumb', 'related_themes', 'related_quizzes'));
    } // end public function

    /**
     * Gera meta description otimizada para teses específicas
     * Trunca inteligentemente a tese para 155 caracteres
     */
    private function generateMetaDescription($tribunal, $numero, $tema_texto, $tese_texto)
    {
        try {
            // Começar com "Tema X [Tribunal]:"
            $description = "Tema {$numero} {$tribunal}: ";

            // Usar a tese se existir, senão usar o tema
            $conteudo = ! empty($tese_texto) && $tese_texto !== '[aguarda julgamento]'
                ? $tese_texto
                : $tema_texto;

            // Limpar texto
            $conteudo = trim($conteudo);
            $conteudo = preg_replace('/\s+/', ' ', $conteudo); // Remove espaços duplos
            $conteudo = preg_replace('/^\d+ - /', '', $conteudo); // Remove "número - " do início

            // Calcular espaço disponível (155 total - prefixo)
            $prefixo_length = strlen($description);
            $espaco_disponivel = 155 - $prefixo_length - 3; // -3 para "..."

            // Truncar inteligentemente
            if (strlen($conteudo) > $espaco_disponivel) {
                // Tentar cortar em uma palavra completa
                $conteudo_truncado = substr($conteudo, 0, $espaco_disponivel);
                $ultimo_espaco = strrpos($conteudo_truncado, ' ');

                if ($ultimo_espaco !== false && $ultimo_espaco > ($espaco_disponivel * 0.8)) {
                    // Cortar na última palavra se não perder muito conteúdo
                    $conteudo = substr($conteudo, 0, $ultimo_espaco).'...';
                } else {
                    // Cortar direto e adicionar ...
                    $conteudo = $conteudo_truncado.'...';
                }
            }

            $description .= $conteudo;

            return $description;

        } catch (Exception $e) {
            // Fallback em caso de erro
            Log::error('Erro ao gerar meta description de tese: '.$e->getMessage());

            return "Tema {$numero} {$tribunal} - Jurisprudência atualizada dos tribunais superiores.";
        }
    }

    /**
     * Busca temas relacionados baseados em palavras-chave similares
     */
    private function getRelatedThemes($texto_tese, $numero_atual)
    {
        try {
            // Extrair palavras-chave do texto da tese
            $palavras = explode(' ', strtolower($texto_tese));

            // Filtrar palavras relevantes (mais de 4 caracteres, não comuns)
            $palavras_comuns = ['sobre', 'para', 'pela', 'pelo', 'pelos', 'pelas', 'entre', 'desde', 'quando', 'sendo', 'tese', 'tema', 'questão'];
            $keywords = array_filter($palavras, function ($palavra) use ($palavras_comuns) {
                return strlen($palavra) > 4 && ! in_array($palavra, $palavras_comuns);
            });

            // Pegar as 5 primeiras palavras relevantes
            $keywords = array_slice(array_values($keywords), 0, 5);

            if (empty($keywords)) {
                return collect([]);
            }

            // Buscar na tabela pesquisas (temas)
            $query = DB::table('pesquisas')
                ->select('id', 'keyword', 'label', 'slug')
                ->whereNotNull('slug')
                ->where(function ($q) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $q->orWhere('keyword', 'LIKE', "%{$keyword}%");
                    }
                });

            $related = $query->limit(6)->get();

            return $related;

        } catch (Exception $e) {
            Log::error('Erro ao buscar temas relacionados: '.$e->getMessage());

            return collect([]);
        }
    }

    /**
     * Busca quizzes relacionados ao tema/tese
     */
    private function getRelatedQuizzes($tribunal, $texto_tese)
    {
        try {
            // Buscar quizzes publicados do mesmo tribunal
            $quizzes = Quiz::published()
                ->withCount('questions')
                ->having('questions_count', '>', 0)
                ->where(function ($query) use ($tribunal, $texto_tese) {
                    // Por tribunal
                    $query->where('tribunal', strtoupper($tribunal));

                    // Ou por palavras-chave no título/descrição
                    if (! empty($texto_tese)) {
                        $palavras = explode(' ', strtolower($texto_tese));
                        $keywords = array_filter($palavras, function ($p) {
                            return strlen($p) > 5;
                        });
                        $keywords = array_slice(array_values($keywords), 0, 3);

                        foreach ($keywords as $keyword) {
                            $query->orWhere('title', 'LIKE', "%{$keyword}%")
                                ->orWhere('description', 'LIKE', "%{$keyword}%")
                                ->orWhere('meta_keywords', 'LIKE', "%{$keyword}%");
                        }
                    }
                })
                ->orderBy('views_count', 'desc')
                ->limit(3)
                ->get();

            return $quizzes;

        } catch (Exception $e) {
            Log::error('Erro ao buscar quizzes relacionados: '.$e->getMessage());

            return collect([]);
        }
    }
}
