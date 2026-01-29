<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeseAcordao;
use App\Services\AcordaoUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class AcordaoAdminController extends Controller
{
    protected $uploadService;

    public function __construct(AcordaoUploadService $uploadService)
    {
        $this->middleware('auth');
        $this->uploadService = $uploadService;
    }

    /**
     * Lista teses com/sem acórdãos
     * Filtros: tribunal, busca por tema, apenas sem acórdão
     */
    public function index(Request $request)
    {
        $tribunal = $request->get('tribunal', 'STF');
        $search = $request->get('search');
        $onlyWithout = $request->boolean('only_without');
        
        // Itens por página (validar contra valores permitidos)
        $allowedPerPage = [10, 20, 50, 100, 200, 500, 1000];
        $perPage = (int) $request->get('per_page', 50);
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 50;
        }
        // Filtro pré-marcado: apenas temas com tese divulgada (tese_texto não nulo)
        // Se não vier na requisição (primeira vez), assume true (pré-marcado)
        // Se vier como '0' ou não vier após primeira vez, mostra todos (com e sem tese)
        if ($request->has('only_with_tese')) {
            $onlyWithTese = $request->get('only_with_tese') === '1';
        } else {
            // Primeira vez: pré-marcado (true)
            $onlyWithTese = true;
        }

        $table = $tribunal === 'STF' ? 'stf_teses' : 'stj_teses';
        
        // Nomes das colunas variam por tribunal
        // STF: tema_texto, acordao, link
        // STJ: tema (sem acordao nem link)
        $temaColumn = $tribunal === 'STF' ? 'tema_texto' : 'tema';
        
        // Colunas base comuns
        $selectColumns = [
            "{$table}.id as tese_id",
            "{$table}.numero",
            "{$table}.{$temaColumn} as tema",
            "{$table}.tese_texto",
        ];
        
        // Colunas específicas por tribunal
        if ($tribunal === 'STF') {
            $selectColumns[] = "{$table}.acordao";
            $selectColumns[] = "{$table}.link";
        } else {
            // STJ não tem essas colunas, usar NULL
            $selectColumns[] = DB::raw('NULL as acordao');
            $selectColumns[] = DB::raw('NULL as link');
        }
        
        $selectColumns[] = DB::raw('COUNT(tese_acordaos.id) as acordaos_count');
        
        // Colunas para GROUP BY (sem acordao/link para STJ)
        $groupByColumns = [
            "{$table}.id",
            "{$table}.numero",
            "{$table}.{$temaColumn}",
            "{$table}.tese_texto",
        ];
        
        if ($tribunal === 'STF') {
            $groupByColumns[] = "{$table}.acordao";
            $groupByColumns[] = "{$table}.link";
        }

        $query = DB::table($table)
            ->select($selectColumns)
            ->leftJoin('tese_acordaos', function ($join) use ($table, $tribunal) {
                $join->on('tese_acordaos.tese_id', '=', "{$table}.id")
                     ->on('tese_acordaos.tribunal', '=', DB::raw("'{$tribunal}'"))
                     ->whereNull('tese_acordaos.deleted_at');
            })
            ->groupBy($groupByColumns);

        // Filtro: apenas temas com tese divulgada (pré-marcado por padrão)
        if ($onlyWithTese) {
            $query->whereNotNull("{$table}.tese_texto")
                  ->where("{$table}.tese_texto", '!=', '');
        }

        if ($search) {
            $query->where(function ($q) use ($search, $table, $temaColumn) {
                $q->where("{$table}.{$temaColumn}", 'LIKE', "%{$search}%")
                  ->orWhere("{$table}.numero", 'LIKE', "%{$search}%");
            });
        }

        if ($onlyWithout) {
            $query->having('acordaos_count', '=', 0);
        }

        $teses = $query->orderBy("{$table}.numero", 'desc')
                      ->paginate($perPage);

        // Buscar acórdãos de cada tese e gerar link "Ver Original" para STF
        foreach ($teses as $tese) {
            $tese->acordaos = TeseAcordao::forTese($tese->tese_id, $tribunal)
                ->orderBy('version', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Gerar link "Ver Original" (mesma lógica do TesePageController)
            if ($tribunal === 'STF') {
                if ((empty($tese->link) || $tese->link == '-') && !empty($tese->acordao)) {
                    $tese->link = "https://jurisprudencia.stf.jus.br/pages/search?base=acordaos&sinonimo=true&plural=true&page=1&&pageSize=10&sort=_score&sortBy=desc&isAdvance=true&classeNumeroIncidente=" . urlencode($tese->acordao);
                }
            } else if ($tribunal === 'STJ') {
                // STJ: link para o portal de temas repetitivos
                $tese->link = "https://processo.stj.jus.br/repetitivos/temas_repetitivos/pesquisa.jsp?novaConsulta=true&tipo_pesquisa=T&cod_tema_inicial={$tese->numero}&cod_tema_final={$tese->numero}";
            }
        }

        return view('admin.acordaos.index', compact('teses', 'tribunal', 'onlyWithTese', 'perPage'));
    }

    /**
     * Upload de novo acórdão
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tese_id' => 'required|integer',
            'tribunal' => 'required|in:STF,STJ',
            'numero_acordao' => 'required|string|max:100',
            'tipo' => 'required|in:Principal,Embargos de Declaração,Modulação de Efeitos,Recurso Extraordinário,Recurso Especial,Outros',
            'label' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:pdf|max:10240', // 10MB
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $acordao = $this->uploadService->upload(
                $request->file('file'),
                $request->only(['tese_id', 'tribunal', 'numero_acordao', 'tipo', 'label']),
                auth()->user()
            );

            return back()->with('success', 'Acórdão enviado com sucesso!');

        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Remove acórdão (soft delete)
     */
    public function destroy(TeseAcordao $acordao)
    {
        try {
            $this->uploadService->delete($acordao, auth()->user());

            return back()->with('success', 'Acórdão removido com sucesso!');

        } catch (Exception $e) {
            return back()->with('error', 'Erro ao remover acórdão: ' . $e->getMessage());
        }
    }
}
