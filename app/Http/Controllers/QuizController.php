<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\QuizCategory;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * Display a listing of published quizzes.
     */
    public function index(Request $request)
    {
        $query = Quiz::published()->with(['category'])->withCount('questions');

        // Filter by category
        if ($request->filled('categoria')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->categoria);
            });
        }

        // Filter by tribunal
        if ($request->filled('tribunal')) {
            $query->where('tribunal', strtoupper($request->tribunal));
        }

        // Filter by difficulty
        if ($request->filled('dificuldade')) {
            $query->where('difficulty', $request->dificuldade);
        }

        $quizzes = $query->orderBy('created_at', 'desc')->paginate(12);
        $categories = QuizCategory::withCount(['quizzes' => function ($q) {
            $q->where('status', 'published');
        }])->having('quizzes_count', '>', 0)->orderBy('name')->get();

        $display_pdf = 'display:none;';
        
        return view('front.quizzes', compact('quizzes', 'categories', 'display_pdf'));
    }

    /**
     * Display a quiz.
     */
    public function show(Request $request, $slug)
    {
        $quiz = Quiz::where('slug', $slug)
            ->published()
            ->with(['category', 'questions.options'])
            ->firstOrFail();

        // Increment views
        $quiz->incrementViews();

        // Get questions in order (or random if configured)
        $questions = $quiz->questions;
        if ($quiz->random_order) {
            $questions = $questions->shuffle();
        }

        // Start or resume attempt
        $sessionId = $request->session()->getId();
        $attempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('session_id', $sessionId)
            ->where('status', 'in_progress')
            ->first();

        if (!$attempt) {
            $attempt = QuizAttempt::start($quiz, auth()->id(), $sessionId);
        }

        // Get answered questions
        $answeredIds = $attempt->answers->pluck('question_id')->toArray();

        // Prepare for SEO
        $description = $quiz->description ?? "Teste seus conhecimentos sobre {$quiz->title}. Quiz jurídico com {$quiz->questions->count()} questões.";

        $display_pdf = 'display:none;';

        return view('front.quiz', compact('quiz', 'questions', 'attempt', 'answeredIds', 'description', 'display_pdf'));
    }

    /**
     * Submit an answer (AJAX).
     */
    public function submitAnswer(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'attempt_id' => 'required|exists:quiz_attempts,id',
            'question_id' => 'required|exists:questions,id',
            'option_id' => 'nullable|exists:question_options,id',
            'time_spent' => 'nullable|integer|min:0',
        ]);

        $attempt = QuizAttempt::findOrFail($validated['attempt_id']);

        // Check if already answered
        if ($attempt->answers()->where('question_id', $validated['question_id'])->exists()) {
            return response()->json([
                'success' => false,
                'error' => 'Esta pergunta já foi respondida.',
            ], 422);
        }

        $question = Question::with('options')->findOrFail($validated['question_id']);
        $selectedOption = $validated['option_id'] ? $question->options->find($validated['option_id']) : null;

        // Record answer
        $answer = QuizAnswer::record(
            $attempt,
            $question,
            $selectedOption,
            $validated['time_spent'] ?? null
        );

        // Get correct option for feedback
        $correctOption = $question->correctOption;

        // Check if quiz is complete
        $totalQuestions = $quiz->questions()->count();
        $answeredCount = $attempt->answers()->count();
        $isComplete = $answeredCount >= $totalQuestions;

        if ($isComplete) {
            $attempt->complete();
        }

        return response()->json([
            'success' => true,
            'is_correct' => $answer->is_correct,
            'correct_option' => $correctOption ? [
                'id' => $correctOption->id,
                'letter' => $correctOption->letter,
                'text' => $correctOption->text,
            ] : null,
            'explanation' => $question->explanation,
            'is_complete' => $isComplete,
            'score' => $isComplete ? $attempt->fresh()->score : null,
            'total' => $totalQuestions,
            'answered' => $answeredCount,
        ]);
    }

    /**
     * Complete quiz and show results.
     */
    public function results(Request $request, $slug)
    {
        $quiz = Quiz::where('slug', $slug)
            ->published()
            ->with(['category', 'questions.options'])
            ->firstOrFail();

        $sessionId = $request->session()->getId();
        $attempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('session_id', $sessionId)
            ->where('status', 'completed')
            ->with(['answers.question.options', 'answers.selectedOption'])
            ->latest()
            ->firstOrFail();

        $display_pdf = 'display:none;';

        return view('front.quiz-result', compact('quiz', 'attempt', 'display_pdf'));
    }

    /**
     * Restart a quiz.
     */
    public function restart(Request $request, $slug)
    {
        $quiz = Quiz::where('slug', $slug)->published()->firstOrFail();
        
        $sessionId = $request->session()->getId();
        
        // Abandon any in-progress attempts
        QuizAttempt::where('quiz_id', $quiz->id)
            ->where('session_id', $sessionId)
            ->where('status', 'in_progress')
            ->update(['status' => 'abandoned']);

        return redirect()->route('quiz.show', $slug);
    }

    /**
     * List quizzes by category.
     */
    public function byCategory($categorySlug)
    {
        $category = QuizCategory::where('slug', $categorySlug)->firstOrFail();
        
        $quizzes = Quiz::published()
            ->where('category_id', $category->id)
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $categories = QuizCategory::withCount(['quizzes' => function ($q) {
            $q->where('status', 'published');
        }])->having('quizzes_count', '>', 0)->orderBy('name')->get();

        $display_pdf = 'display:none;';

        return view('front.quizzes', compact('quizzes', 'categories', 'category', 'display_pdf'));
    }
}
