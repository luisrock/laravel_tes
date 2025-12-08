<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizCategory;
use App\Models\EditableContent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class QuizAdminController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of quizzes.
     */
    public function index(Request $request)
    {
        $query = Quiz::with('category')->withCount('questions');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tribunal')) {
            $query->where('tribunal', $request->tribunal);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $quizzes = $query->orderBy('created_at', 'desc')->paginate(20);
        $categories = QuizCategory::orderBy('name')->get();
        
        // Verificar se a seção de quizzes está visível na home
        $homeVisibility = EditableContent::where('slug', 'quizzes-home-visibility')->first();
        $isVisibleOnHome = $homeVisibility ? $homeVisibility->published : false;

        return view('admin.quiz.index', compact('quizzes', 'categories', 'isVisibleOnHome'));
    }

    /**
     * Show the form for creating a new quiz.
     */
    public function create()
    {
        $categories = QuizCategory::orderBy('name')->get();
        $colors = ['#912F56', '#0D090A', '#F0C808', '#6BAA75', '#A53860', '#D5B0AC'];

        return view('admin.quiz.create', compact('categories', 'colors'));
    }

    /**
     * Store a newly created quiz.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:quizzes,slug',
            'description' => 'nullable|string|max:65535',
            'tribunal' => 'nullable|string|max:10',
            'tema_number' => 'nullable|integer',
            'category_id' => 'nullable|exists:quiz_categories,id',
            'difficulty' => 'required|in:easy,medium,hard',
            'estimated_time' => 'required|integer|min:1|max:120',
            'color' => 'required|string|max:7',
            'show_ads' => 'nullable|boolean',
            'show_share' => 'nullable|boolean',
            'show_progress' => 'nullable|boolean',
            'random_order' => 'nullable|boolean',
            'show_feedback_immediately' => 'nullable|boolean',
            'meta_keywords' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived',
        ]);

        // Handle checkboxes
        $validated['show_ads'] = $request->has('show_ads');
        $validated['show_share'] = $request->has('show_share');
        $validated['show_progress'] = $request->has('show_progress');
        $validated['random_order'] = $request->has('random_order');
        $validated['show_feedback_immediately'] = $request->has('show_feedback_immediately');

        $quiz = Quiz::create($validated);

        return redirect()
            ->route('admin.quizzes.questions', $quiz)
            ->with('success', 'Quiz criado com sucesso! Agora adicione as perguntas.');
    }

    /**
     * Show the form for editing a quiz.
     */
    public function edit(Quiz $quiz)
    {
        $categories = QuizCategory::orderBy('name')->get();
        $colors = ['#912F56', '#0D090A', '#F0C808', '#6BAA75', '#A53860', '#D5B0AC'];

        return view('admin.quiz.edit', compact('quiz', 'categories', 'colors'));
    }

    /**
     * Update the specified quiz.
     */
    public function update(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('quizzes')->ignore($quiz->id)],
            'description' => 'nullable|string|max:65535',
            'tribunal' => 'nullable|string|max:10',
            'tema_number' => 'nullable|integer',
            'category_id' => 'nullable|exists:quiz_categories,id',
            'difficulty' => 'required|in:easy,medium,hard',
            'estimated_time' => 'required|integer|min:1|max:120',
            'color' => 'required|string|max:7',
            'show_ads' => 'nullable|boolean',
            'show_share' => 'nullable|boolean',
            'show_progress' => 'nullable|boolean',
            'random_order' => 'nullable|boolean',
            'show_feedback_immediately' => 'nullable|boolean',
            'meta_keywords' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived',
        ]);

        // Handle checkboxes
        $validated['show_ads'] = $request->has('show_ads');
        $validated['show_share'] = $request->has('show_share');
        $validated['show_progress'] = $request->has('show_progress');
        $validated['random_order'] = $request->has('random_order');
        $validated['show_feedback_immediately'] = $request->has('show_feedback_immediately');

        // Generate slug if title changed and no custom slug
        if ($quiz->title !== $validated['title'] && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $quiz->update($validated);

        return redirect()
            ->route('admin.quizzes.index')
            ->with('success', 'Quiz atualizado com sucesso!');
    }

    /**
     * Remove the specified quiz.
     */
    public function destroy(Quiz $quiz)
    {
        $quiz->delete();

        return redirect()
            ->route('admin.quizzes.index')
            ->with('success', 'Quiz excluído com sucesso!');
    }

    /**
     * Manage questions in a quiz.
     */
    public function questions(Quiz $quiz)
    {
        $quiz->load(['questions.options', 'questions.category', 'questions.tags']);
        $categories = QuizCategory::orderBy('name')->get();

        return view('admin.quiz.questions', compact('quiz', 'categories'));
    }

    /**
     * Add a question to the quiz (AJAX).
     */
    public function addQuestion(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
        ]);

        // Check if already attached
        if ($quiz->questions()->where('questions.id', $validated['question_id'])->exists()) {
            return response()->json([
                'success' => false,
                'error' => 'Esta pergunta já está no quiz.',
            ], 422);
        }

        $maxOrder = $quiz->questions()->max('quiz_question.order') ?? 0;
        $quiz->questions()->attach($validated['question_id'], ['order' => $maxOrder + 1]);

        $question = Question::with(['options', 'category', 'tags'])->find($validated['question_id']);

        return response()->json([
            'success' => true,
            'message' => 'Pergunta adicionada ao quiz.',
            'question' => $question,
            'order' => $maxOrder + 1,
        ]);
    }

    /**
     * Remove a question from the quiz (AJAX).
     */
    public function removeQuestion(Quiz $quiz, Question $question)
    {
        $quiz->questions()->detach($question->id);

        return response()->json([
            'success' => true,
            'message' => 'Pergunta removida do quiz.',
        ]);
    }

    /**
     * Reorder questions in the quiz (AJAX).
     */
    public function reorderQuestions(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:questions,id',
        ]);

        foreach ($validated['order'] as $index => $questionId) {
            $quiz->questions()->updateExistingPivot($questionId, ['order' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ordem atualizada.',
        ]);
    }

    /**
     * Search questions (AJAX).
     */
    public function searchQuestions(Request $request, Quiz $quiz)
    {
        $search = $request->get('q', '');
        $categoryId = $request->get('category_id');

        $query = Question::with(['category', 'options'])
            ->whereDoesntHave('quizzes', function ($q) use ($quiz) {
                $q->where('quizzes.id', $quiz->id);
            });

        if (!empty($search)) {
            $query->where('text', 'like', "%{$search}%");
        }

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        $questions = $query->limit(20)->get();

        return response()->json([
            'success' => true,
            'questions' => $questions,
        ]);
    }

    /**
     * Duplicate a quiz.
     */
    public function duplicate(Quiz $quiz)
    {
        $newQuiz = $quiz->replicate();
        $newQuiz->title = $quiz->title . ' (Cópia)';
        $newQuiz->slug = Str::slug($newQuiz->title);
        $newQuiz->status = 'draft';
        $newQuiz->views_count = 0;
        $newQuiz->save();

        // Copy questions
        foreach ($quiz->questions as $question) {
            $newQuiz->questions()->attach($question->id, ['order' => $question->pivot->order]);
        }

        return redirect()
            ->route('admin.quizzes.edit', $newQuiz)
            ->with('success', 'Quiz duplicado com sucesso!');
    }

    /**
     * Toggle visibility of quizzes section on home page.
     */
    public function toggleHomeVisibility()
    {
        $setting = EditableContent::firstOrCreate(
            ['slug' => 'quizzes-home-visibility'],
            [
                'title' => 'Exibir Quizzes na Home',
                'meta_description' => 'Controla se a seção de quizzes aparece na página inicial',
                'content' => '',
                'published' => false,
            ]
        );

        $setting->published = !$setting->published;
        $setting->save();

        $status = $setting->published ? 'visível' : 'oculta';
        
        return redirect()
            ->route('admin.quizzes.index')
            ->with('success', "Seção de Quizzes na Home agora está {$status}!");
    }
}
