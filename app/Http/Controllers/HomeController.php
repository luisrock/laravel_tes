<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizCategory;
use App\Models\QuizAttempt;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Estatísticas de Temas/Pesquisas
        $stats = [
            'total' => DB::table('pesquisas')->count(),
            'created' => DB::table('pesquisas')->whereNotNull('created_at')->count(),
            'checked' => DB::table('pesquisas')->whereNotNull('checked_at')->count(),
            'pending' => DB::table('pesquisas')->whereNull('created_at')->whereNull('checked_at')->count(),
        ];

        // Estatísticas de Quizzes
        $quizStats = [
            'total' => Quiz::count(),
            'published' => Quiz::where('status', 'published')->count(),
            'questions' => Question::count(),
            'categories' => QuizCategory::count(),
            'attempts' => QuizAttempt::count(),
            'completed' => QuizAttempt::where('status', 'completed')->count(),
        ];

        return view('admin.dashboard', compact('stats', 'quizStats'));
    }

    /**
     * Show the temas management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function temas()
    {
        Artisan::call('queue:work --stop-when-empty');

        // Carregar apenas estatísticas iniciais - dados serão carregados via AJAX
        $stats = [
            'total' => DB::table('pesquisas')->count(),
            'created' => DB::table('pesquisas')->whereNotNull('created_at')->count(),
            'checked' => DB::table('pesquisas')->whereNotNull('checked_at')->count(),
            'pending' => DB::table('pesquisas')->whereNull('created_at')->whereNull('checked_at')->count(),
        ];

        return view('admin.temas', compact('stats'));
    }

    /**
     * Get temas via AJAX with pagination and filters
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTemas(Request $request)
    {
        $perPage = $request->input('per_page', 30);
        $page = $request->input('page', 1);
        $filterStatus = $request->input('filter_status', 'all');
        $orderBy = $request->input('order_by', 'keyword');
        $orderDirection = $request->input('order_direction', 'asc');
        $search = $request->input('search', '');

        // Build query
        $query = DB::table('pesquisas')->select('*');

        // Apply status filter
        switch ($filterStatus) {
            case 'not_created':
                $query->whereNull('created_at');
                break;
            case 'created':
                $query->whereNotNull('created_at');
                break;
            case 'checked':
                $query->whereNotNull('checked_at');
                break;
            case 'pending':
                $query->whereNull('created_at')->whereNull('checked_at');
                break;
            // 'all' - no filter
        }

        // Apply search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('keyword', 'LIKE', "%{$search}%")
                  ->orWhere('label', 'LIKE', "%{$search}%");
            });
        }

        // Apply ordering
        if ($orderBy === 'keyword') {
            $query->orderBy(DB::raw("REPLACE(keyword, '\"', '')"), $orderDirection);
        } elseif ($orderBy === 'results') {
            $query->orderBy('results', $orderDirection);
        } elseif ($orderBy === 'created_at') {
            $query->orderBy('created_at', $orderDirection);
        }

        // Get total count
        $total = $query->count();

        // Apply pagination
        $offset = ($page - 1) * $perPage;
        $temas = $query->skip($offset)->take($perPage)->get();

        return response()->json([
            'success' => true,
            'data' => $temas,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total),
            ]
        ]);
    }
}
