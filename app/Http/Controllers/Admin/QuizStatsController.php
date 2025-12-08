<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuizStatsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the stats dashboard.
     */
    public function index()
    {
        // General Stats
        $stats = [
            'total_quizzes' => Quiz::count(),
            'published_quizzes' => Quiz::where('status', 'published')->count(),
            'total_questions' => Question::count(),
            'total_attempts' => QuizAttempt::count(),
            'completed_attempts' => QuizAttempt::where('status', 'completed')->count(),
            'total_answers' => QuizAnswer::count(),
        ];

        // Calculate average score
        $stats['average_score'] = QuizAttempt::where('status', 'completed')
            ->whereRaw('total_questions > 0')
            ->selectRaw('AVG((score / total_questions) * 100) as avg')
            ->value('avg') ?? 0;

        // Calculate completion rate
        $stats['completion_rate'] = $stats['total_attempts'] > 0 
            ? round(($stats['completed_attempts'] / $stats['total_attempts']) * 100, 1)
            : 0;

        // Most popular quizzes
        $popularQuizzes = Quiz::withCount(['attempts' => function ($q) {
            $q->where('status', 'completed');
        }])
        ->having('attempts_count', '>', 0)
        ->orderBy('attempts_count', 'desc')
        ->limit(10)
        ->get();

        // Hardest questions (lowest success rate)
        $hardestQuestions = Question::where('times_answered', '>=', 5)
            ->selectRaw('*, (times_correct / times_answered * 100) as success_rate')
            ->orderBy('success_rate', 'asc')
            ->limit(10)
            ->get();

        // Easiest questions (highest success rate)
        $easiestQuestions = Question::where('times_answered', '>=', 5)
            ->selectRaw('*, (times_correct / times_answered * 100) as success_rate')
            ->orderBy('success_rate', 'desc')
            ->limit(10)
            ->get();

        // Recent attempts
        $recentAttempts = QuizAttempt::with(['quiz', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Attempts over time (last 30 days)
        $attemptsOverTime = QuizAttempt::where('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Fill in missing dates
        $chartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartData[$date] = $attemptsOverTime[$date] ?? 0;
        }

        return view('admin.quiz.stats', compact(
            'stats',
            'popularQuizzes',
            'hardestQuestions',
            'easiestQuestions',
            'recentAttempts',
            'chartData'
        ));
    }

    /**
     * Stats for a specific quiz.
     */
    public function quiz(Quiz $quiz)
    {
        $quiz->load(['questions.options', 'category']);

        // Basic stats
        $stats = [
            'total_attempts' => $quiz->attempts()->count(),
            'completed_attempts' => $quiz->attempts()->where('status', 'completed')->count(),
            'abandoned_attempts' => $quiz->attempts()->where('status', 'abandoned')->count(),
            'average_score' => $quiz->attempts()->where('status', 'completed')->avg('score') ?? 0,
            'average_time' => $quiz->attempts()->where('status', 'completed')->avg('time_spent_seconds') ?? 0,
        ];

        $stats['completion_rate'] = $stats['total_attempts'] > 0 
            ? round(($stats['completed_attempts'] / $stats['total_attempts']) * 100, 1)
            : 0;

        $stats['average_percentage'] = $quiz->questions()->count() > 0 
            ? round(($stats['average_score'] / $quiz->questions()->count()) * 100, 1)
            : 0;

        // Question performance
        $questionStats = [];
        foreach ($quiz->questions as $question) {
            $answers = QuizAnswer::where('question_id', $question->id)
                ->whereHas('attempt', function ($q) use ($quiz) {
                    $q->where('quiz_id', $quiz->id);
                })
                ->get();

            $total = $answers->count();
            $correct = $answers->where('is_correct', true)->count();

            // Option distribution
            $optionDistribution = [];
            foreach ($question->options as $option) {
                $optionDistribution[$option->letter] = $answers->where('selected_option_id', $option->id)->count();
            }

            $questionStats[] = [
                'question' => $question,
                'total_answers' => $total,
                'correct_answers' => $correct,
                'success_rate' => $total > 0 ? round(($correct / $total) * 100, 1) : 0,
                'option_distribution' => $optionDistribution,
            ];
        }

        // Recent attempts for this quiz
        $recentAttempts = $quiz->attempts()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.quiz.stats-quiz', compact('quiz', 'stats', 'questionStats', 'recentAttempts'));
    }

    /**
     * Export stats as JSON.
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'summary');

        if ($type === 'summary') {
            $data = [
                'generated_at' => now()->toIso8601String(),
                'quizzes' => Quiz::withCount(['attempts', 'questions'])->get(),
                'total_attempts' => QuizAttempt::count(),
                'total_answers' => QuizAnswer::count(),
            ];
        } elseif ($type === 'questions') {
            $data = Question::with(['category', 'tags'])
                ->select('*')
                ->selectRaw('(times_correct / NULLIF(times_answered, 0) * 100) as success_rate')
                ->get();
        } else {
            $data = QuizAttempt::with(['quiz', 'answers'])
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->limit(1000)
                ->get();
        }

        return response()->json($data);
    }
}
