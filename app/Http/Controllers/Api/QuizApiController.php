<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class QuizApiController extends Controller
{
    /**
     * List all quizzes with optional filters.
     */
    public function index(Request $request)
    {
        $query = Quiz::with(['category', 'questions']);

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('tribunal')) {
            $query->where('tribunal', strtoupper($request->tribunal));
        }
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $quizzes = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $quizzes,
        ]);
    }

    /**
     * Get a single quiz by ID or slug.
     */
    public function show($identifier)
    {
        $quiz = is_numeric($identifier)
            ? Quiz::with(['category', 'questions.options', 'questions.tags'])->find($identifier)
            : Quiz::with(['category', 'questions.options', 'questions.tags'])->where('slug', $identifier)->first();

        if (! $quiz) {
            return response()->json([
                'success' => false,
                'error' => 'Quiz não encontrado.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $quiz,
        ]);
    }

    /**
     * Create a new quiz.
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
            'difficulty' => 'nullable|in:easy,medium,hard',
            'estimated_time' => 'nullable|integer|min:1|max:120',
            'color' => 'nullable|string|max:7',
            'show_ads' => 'nullable|boolean',
            'show_share' => 'nullable|boolean',
            'show_progress' => 'nullable|boolean',
            'random_order' => 'nullable|boolean',
            'show_feedback_immediately' => 'nullable|boolean',
            'meta_keywords' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,published,archived',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $quiz = Quiz::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Quiz criado com sucesso.',
            'data' => $quiz->load('category'),
        ], 201);
    }

    /**
     * Update a quiz.
     */
    public function update(Request $request, $id)
    {
        $quiz = Quiz::find($id);

        if (! $quiz) {
            return response()->json([
                'success' => false,
                'error' => 'Quiz não encontrado.',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('quizzes')->ignore($quiz->id)],
            'description' => 'nullable|string|max:65535',
            'tribunal' => 'nullable|string|max:10',
            'tema_number' => 'nullable|integer',
            'category_id' => 'nullable|exists:quiz_categories,id',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'estimated_time' => 'nullable|integer|min:1|max:120',
            'color' => 'nullable|string|max:7',
            'show_ads' => 'nullable|boolean',
            'show_share' => 'nullable|boolean',
            'show_progress' => 'nullable|boolean',
            'random_order' => 'nullable|boolean',
            'show_feedback_immediately' => 'nullable|boolean',
            'meta_keywords' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,published,archived',
        ]);

        $quiz->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Quiz atualizado com sucesso.',
            'data' => $quiz->fresh()->load('category'),
        ]);
    }

    /**
     * Delete a quiz.
     */
    public function destroy($id)
    {
        $quiz = Quiz::find($id);

        if (! $quiz) {
            return response()->json([
                'success' => false,
                'error' => 'Quiz não encontrado.',
            ], 404);
        }

        $quiz->delete();

        return response()->json([
            'success' => true,
            'message' => 'Quiz excluído com sucesso.',
        ]);
    }

    /**
     * Add a question to a quiz.
     */
    public function addQuestion(Request $request, $quizId)
    {
        $quiz = Quiz::find($quizId);

        if (! $quiz) {
            return response()->json([
                'success' => false,
                'error' => 'Quiz não encontrado.',
            ], 404);
        }

        $validated = $request->validate([
            'question_id' => 'required_without:question|exists:questions,id',
            'question' => 'required_without:question_id|array',
            'question.text' => 'required_with:question|string',
            'question.explanation' => 'nullable|string',
            'question.category_id' => 'nullable|exists:quiz_categories,id',
            'question.difficulty' => 'nullable|in:easy,medium,hard',
            'question.options' => 'required_with:question|array|min:2',
            'question.options.*.letter' => 'required_with:question|string|max:1',
            'question.options.*.text' => 'required_with:question|string',
            'question.options.*.is_correct' => 'required_with:question|boolean',
            'question.tags' => 'nullable|array',
            'question.tags.*' => 'exists:question_tags,id',
            'order' => 'nullable|integer|min:0',
        ]);

        // If question_id is provided, use existing question
        if (! empty($validated['question_id'])) {
            $questionId = $validated['question_id'];

            // Check if already attached
            if ($quiz->questions()->where('questions.id', $questionId)->exists()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Esta pergunta já está no quiz.',
                ], 422);
            }
        } else {
            // Create new question
            $questionData = $validated['question'];
            $question = Question::create([
                'text' => $questionData['text'],
                'explanation' => $questionData['explanation'] ?? null,
                'category_id' => $questionData['category_id'] ?? $quiz->category_id,
                'difficulty' => $questionData['difficulty'] ?? 'medium',
            ]);

            // Create options
            foreach ($questionData['options'] as $optionData) {
                $question->options()->create([
                    'letter' => strtoupper($optionData['letter']),
                    'text' => $optionData['text'],
                    'is_correct' => $optionData['is_correct'],
                ]);
            }

            // Attach tags
            if (! empty($questionData['tags'])) {
                $question->tags()->sync($questionData['tags']);
            }

            $questionId = $question->id;
        }

        // Determine order
        $order = $validated['order'] ?? ($quiz->questions()->max('quiz_question.order') ?? 0) + 1;

        // Attach question to quiz
        $quiz->questions()->attach($questionId, ['order' => $order]);

        return response()->json([
            'success' => true,
            'message' => 'Pergunta adicionada ao quiz com sucesso.',
            'data' => $quiz->fresh()->load(['questions.options']),
        ]);
    }

    /**
     * Remove a question from a quiz.
     */
    public function removeQuestion($quizId, $questionId)
    {
        $quiz = Quiz::find($quizId);

        if (! $quiz) {
            return response()->json([
                'success' => false,
                'error' => 'Quiz não encontrado.',
            ], 404);
        }

        if (! $quiz->questions()->where('questions.id', $questionId)->exists()) {
            return response()->json([
                'success' => false,
                'error' => 'Pergunta não encontrada neste quiz.',
            ], 404);
        }

        $quiz->questions()->detach($questionId);

        return response()->json([
            'success' => true,
            'message' => 'Pergunta removida do quiz com sucesso.',
        ]);
    }

    /**
     * Reorder questions in a quiz.
     */
    public function reorderQuestions(Request $request, $quizId)
    {
        $quiz = Quiz::find($quizId);

        if (! $quiz) {
            return response()->json([
                'success' => false,
                'error' => 'Quiz não encontrado.',
            ], 404);
        }

        $validated = $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:questions,id',
            'questions.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['questions'] as $item) {
            $quiz->questions()->updateExistingPivot($item['id'], ['order' => $item['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ordem das perguntas atualizada com sucesso.',
        ]);
    }

    /**
     * List all categories.
     */
    public function categories()
    {
        $categories = QuizCategory::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}
