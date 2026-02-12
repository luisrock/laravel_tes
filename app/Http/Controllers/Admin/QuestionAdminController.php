<?php

namespace App\Http\Controllers\Admin;

use Str;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTag;
use App\Models\QuizCategory;
use Illuminate\Http\Request;

class QuestionAdminController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of questions.
     */
    public function index(Request $request)
    {
        $query = Question::with(['category', 'tags', 'options'])->withCount('quizzes');

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }
        if ($request->filled('search')) {
            $query->where('text', 'like', "%{$request->search}%");
        }
        if ($request->filled('tag_id')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('question_tags.id', $request->tag_id);
            });
        }
        if ($request->filled('unused')) {
            $query->doesntHave('quizzes');
        }

        $questions = $query->orderBy('created_at', 'desc')->paginate(20);
        $categories = QuizCategory::orderBy('name')->get();
        $tags = QuestionTag::orderBy('name')->get();

        return view('admin.questions.index', compact('questions', 'categories', 'tags'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create()
    {
        $categories = QuizCategory::orderBy('name')->get();
        $tags = QuestionTag::orderBy('name')->get();

        return view('admin.questions.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created question.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'explanation' => 'nullable|string',
            'category_id' => 'nullable|exists:quiz_categories,id',
            'difficulty' => 'required|in:easy,medium,hard',
            'options' => 'required|array|min:2|max:6',
            'options.*.letter' => 'required|string|max:1',
            'options.*.text' => 'required|string',
            'correct_option' => 'required|string|max:1',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:question_tags,id',
        ]);

        // Create question
        $question = Question::create([
            'text' => $validated['text'],
            'explanation' => $validated['explanation'],
            'category_id' => $validated['category_id'],
            'difficulty' => $validated['difficulty'],
        ]);

        // Create options
        foreach ($validated['options'] as $optionData) {
            $question->options()->create([
                'letter' => strtoupper($optionData['letter']),
                'text' => $optionData['text'],
                'is_correct' => strtoupper($optionData['letter']) === strtoupper($validated['correct_option']),
            ]);
        }

        // Attach tags
        if (!empty($validated['tags'])) {
            $question->tags()->sync($validated['tags']);
        }

        if ($request->has('redirect_to_create')) {
            return redirect()
                ->route('admin.questions.create')
                ->with('success', 'Pergunta criada com sucesso! Crie outra.');
        }

        return redirect()
            ->route('admin.questions.index')
            ->with('success', 'Pergunta criada com sucesso!');
    }

    /**
     * Show the form for editing a question.
     */
    public function edit(Question $question)
    {
        $question->load(['options', 'tags', 'quizzes']);
        $categories = QuizCategory::orderBy('name')->get();
        $tags = QuestionTag::orderBy('name')->get();

        return view('admin.questions.edit', compact('question', 'categories', 'tags'));
    }

    /**
     * Update the specified question.
     */
    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'explanation' => 'nullable|string',
            'category_id' => 'nullable|exists:quiz_categories,id',
            'difficulty' => 'required|in:easy,medium,hard',
            'options' => 'required|array|min:2|max:6',
            'options.*.letter' => 'required|string|max:1',
            'options.*.text' => 'required|string',
            'correct_option' => 'required|string|max:1',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:question_tags,id',
        ]);

        // Update question
        $question->update([
            'text' => $validated['text'],
            'explanation' => $validated['explanation'],
            'category_id' => $validated['category_id'],
            'difficulty' => $validated['difficulty'],
        ]);

        // Update options - delete and recreate
        $question->options()->delete();
        foreach ($validated['options'] as $optionData) {
            $question->options()->create([
                'letter' => strtoupper($optionData['letter']),
                'text' => $optionData['text'],
                'is_correct' => strtoupper($optionData['letter']) === strtoupper($validated['correct_option']),
            ]);
        }

        // Sync tags
        $question->tags()->sync($validated['tags'] ?? []);

        return redirect()
            ->route('admin.questions.index')
            ->with('success', 'Pergunta atualizada com sucesso!');
    }

    /**
     * Remove the specified question.
     */
    public function destroy(Question $question)
    {
        // Check if used in quizzes
        if ($question->quizzes()->count() > 0) {
            return redirect()
                ->route('admin.questions.index')
                ->with('error', 'Esta pergunta está sendo usada em quizzes. Remova-a dos quizzes antes de excluir.');
        }

        $question->delete();

        return redirect()
            ->route('admin.questions.index')
            ->with('success', 'Pergunta excluída com sucesso!');
    }

    /**
     * Store a question inline (AJAX) - used in quiz editor.
     */
    public function storeInline(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'explanation' => 'nullable|string',
            'category_id' => 'nullable|exists:quiz_categories,id',
            'difficulty' => 'required|in:easy,medium,hard',
            'options' => 'required|array|min:2|max:6',
            'options.*.letter' => 'required|string|max:1',
            'options.*.text' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:question_tags,id',
        ]);

        // Validate exactly one correct option
        $correctCount = collect($validated['options'])->where('is_correct', true)->count();
        if ($correctCount !== 1) {
            return response()->json([
                'success' => false,
                'error' => 'Exatamente uma alternativa deve ser marcada como correta.',
            ], 422);
        }

        // Create question
        $question = Question::create([
            'text' => $validated['text'],
            'explanation' => $validated['explanation'],
            'category_id' => $validated['category_id'],
            'difficulty' => $validated['difficulty'],
        ]);

        // Create options
        foreach ($validated['options'] as $optionData) {
            $question->options()->create([
                'letter' => strtoupper($optionData['letter']),
                'text' => $optionData['text'],
                'is_correct' => $optionData['is_correct'],
            ]);
        }

        // Attach tags
        if (!empty($validated['tags'])) {
            $question->tags()->sync($validated['tags']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pergunta criada com sucesso!',
            'question' => $question->load(['category', 'options', 'tags']),
        ]);
    }

    /**
     * Duplicate a question.
     */
    public function duplicate(Question $question)
    {
        $newQuestion = $question->replicate();
        $newQuestion->times_answered = 0;
        $newQuestion->times_correct = 0;
        $newQuestion->save();

        // Copy options
        foreach ($question->options as $option) {
            $newQuestion->options()->create([
                'letter' => $option->letter,
                'text' => $option->text,
                'is_correct' => $option->is_correct,
            ]);
        }

        // Copy tags
        $newQuestion->tags()->sync($question->tags->pluck('id'));

        return redirect()
            ->route('admin.questions.edit', $newQuestion)
            ->with('success', 'Pergunta duplicada com sucesso!');
    }

    /**
     * Manage tags.
     */
    public function tags(Request $request)
    {
        $tags = QuestionTag::withCount('questions')->orderBy('name')->get();

        return view('admin.questions.tags', compact('tags'));
    }

    /**
     * Store a new tag.
     */
    public function storeTag(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:question_tags,name',
        ]);

        $tag = QuestionTag::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'tag' => $tag,
            ]);
        }

        return redirect()
            ->route('admin.questions.tags')
            ->with('success', 'Tag criada com sucesso!');
    }

    /**
     * Delete a tag.
     */
    public function destroyTag(QuestionTag $tag)
    {
        $tag->delete();

        return redirect()
            ->route('admin.questions.tags')
            ->with('success', 'Tag excluída com sucesso!');
    }
}
