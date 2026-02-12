<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionTag;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Str;

class QuestionApiController extends Controller
{
    /**
     * List all questions with optional filters.
     */
    public function index(Request $request)
    {
        $query = Question::with(['category', 'options', 'tags']);

        // Filters
        if ($request->has('search')) {
            $query->search($request->search);
        }
        if ($request->has('category_id')) {
            $query->byCategory($request->category_id);
        }
        if ($request->has('difficulty')) {
            $query->byDifficulty($request->difficulty);
        }
        if ($request->has('tags')) {
            $tagIds = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
            $query->byTags($tagIds);
        }

        // Exclude questions already in a quiz
        if ($request->has('exclude_quiz_id')) {
            $query->whereDoesntHave('quizzes', function ($q) use ($request) {
                $q->where('quizzes.id', $request->exclude_quiz_id);
            });
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $questions = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $questions,
        ]);
    }

    /**
     * Get a single question by ID.
     */
    public function show($id)
    {
        $question = Question::with(['category', 'options', 'tags', 'quizzes'])->find($id);

        if (! $question) {
            return response()->json([
                'success' => false,
                'error' => 'Pergunta não encontrada.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $question,
        ]);
    }

    /**
     * Create a new question with options.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'explanation' => 'nullable|string',
            'category_id' => 'nullable|exists:quiz_categories,id',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'options' => 'required|array|min:2|max:6',
            'options.*.letter' => 'required|string|max:1',
            'options.*.text' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:question_tags,id',
            'tese_ids' => 'nullable|array',
            'tese_ids.*' => 'integer',
        ]);

        // Validate that exactly one option is correct
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
            'explanation' => $validated['explanation'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'difficulty' => $validated['difficulty'] ?? 'medium',
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
        if (! empty($validated['tags'])) {
            $question->tags()->sync($validated['tags']);
        }

        // Attach teses
        if (! empty($validated['tese_ids'])) {
            $question->teses()->sync($validated['tese_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pergunta criada com sucesso.',
            'data' => $question->load(['category', 'options', 'tags']),
        ], 201);
    }

    /**
     * Update a question.
     */
    public function update(Request $request, $id)
    {
        $question = Question::find($id);

        if (! $question) {
            return response()->json([
                'success' => false,
                'error' => 'Pergunta não encontrada.',
            ], 404);
        }

        $validated = $request->validate([
            'text' => 'sometimes|required|string',
            'explanation' => 'nullable|string',
            'category_id' => 'nullable|exists:quiz_categories,id',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'options' => 'sometimes|required|array|min:2|max:6',
            'options.*.id' => 'nullable|exists:question_options,id',
            'options.*.letter' => 'required_with:options|string|max:1',
            'options.*.text' => 'required_with:options|string',
            'options.*.is_correct' => 'required_with:options|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:question_tags,id',
            'tese_ids' => 'nullable|array',
            'tese_ids.*' => 'integer',
        ]);

        // Validate that exactly one option is correct if options are being updated
        if (isset($validated['options'])) {
            $correctCount = collect($validated['options'])->where('is_correct', true)->count();
            if ($correctCount !== 1) {
                return response()->json([
                    'success' => false,
                    'error' => 'Exatamente uma alternativa deve ser marcada como correta.',
                ], 422);
            }
        }

        // Update question fields
        $question->update([
            'text' => $validated['text'] ?? $question->text,
            'explanation' => $validated['explanation'] ?? $question->explanation,
            'category_id' => $validated['category_id'] ?? $question->category_id,
            'difficulty' => $validated['difficulty'] ?? $question->difficulty,
        ]);

        // Update options if provided
        if (isset($validated['options'])) {
            // Delete existing options and recreate
            $question->options()->delete();

            foreach ($validated['options'] as $optionData) {
                $question->options()->create([
                    'letter' => strtoupper($optionData['letter']),
                    'text' => $optionData['text'],
                    'is_correct' => $optionData['is_correct'],
                ]);
            }
        }

        // Sync tags if provided
        if (array_key_exists('tags', $validated)) {
            $question->tags()->sync($validated['tags'] ?? []);
        }

        // Sync teses if provided
        if (array_key_exists('tese_ids', $validated)) {
            $question->teses()->sync($validated['tese_ids'] ?? []);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pergunta atualizada com sucesso.',
            'data' => $question->fresh()->load(['category', 'options', 'tags']),
        ]);
    }

    /**
     * Delete a question.
     */
    public function destroy($id)
    {
        $question = Question::find($id);

        if (! $question) {
            return response()->json([
                'success' => false,
                'error' => 'Pergunta não encontrada.',
            ], 404);
        }

        // Check if question is used in any quiz
        $quizCount = $question->quizzes()->count();
        if ($quizCount > 0) {
            return response()->json([
                'success' => false,
                'error' => "Esta pergunta está sendo usada em {$quizCount} quiz(zes). Remova-a dos quizzes antes de excluir.",
            ], 422);
        }

        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pergunta excluída com sucesso.',
        ]);
    }

    /**
     * List all tags.
     */
    public function tags(Request $request)
    {
        $query = QuestionTag::query();

        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $tags = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $tags,
        ]);
    }

    /**
     * Create a new tag.
     */
    public function createTag(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:question_tags,name',
        ]);

        $tag = QuestionTag::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tag criada com sucesso.',
            'data' => $tag,
        ], 201);
    }

    /**
     * Search questions for autocomplete.
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:3',
            'limit' => 'nullable|integer|min:1|max:50',
            'exclude_quiz_id' => 'nullable|exists:quizzes,id',
        ]);

        $query = Question::with(['category', 'options'])
            ->where('text', 'like', "%{$validated['q']}%");

        if (! empty($validated['exclude_quiz_id'])) {
            $query->whereDoesntHave('quizzes', function ($q) use ($validated) {
                $q->where('quizzes.id', $validated['exclude_quiz_id']);
            });
        }

        $questions = $query->limit($validated['limit'] ?? 10)->get();

        return response()->json([
            'success' => true,
            'data' => $questions,
        ]);
    }

    /**
     * Bulk create questions (for AI generation).
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'questions' => 'required|array|min:1|max:50',
            'questions.*.text' => 'required|string',
            'questions.*.explanation' => 'nullable|string',
            'questions.*.category_id' => 'nullable|exists:quiz_categories,id',
            'questions.*.difficulty' => 'nullable|in:easy,medium,hard',
            'questions.*.options' => 'required|array|min:2|max:6',
            'questions.*.options.*.letter' => 'required|string|max:1',
            'questions.*.options.*.text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
            'questions.*.tags' => 'nullable|array',
            'questions.*.tags.*' => 'exists:question_tags,id',
            'quiz_id' => 'nullable|exists:quizzes,id',
        ]);

        $createdQuestions = [];
        $errors = [];

        foreach ($validated['questions'] as $index => $questionData) {
            // Validate exactly one correct option
            $correctCount = collect($questionData['options'])->where('is_correct', true)->count();
            if ($correctCount !== 1) {
                $errors[] = "Pergunta {$index}: Exatamente uma alternativa deve ser marcada como correta.";

                continue;
            }

            $question = Question::create([
                'text' => $questionData['text'],
                'explanation' => $questionData['explanation'] ?? null,
                'category_id' => $questionData['category_id'] ?? null,
                'difficulty' => $questionData['difficulty'] ?? 'medium',
            ]);

            foreach ($questionData['options'] as $optionData) {
                $question->options()->create([
                    'letter' => strtoupper($optionData['letter']),
                    'text' => $optionData['text'],
                    'is_correct' => $optionData['is_correct'],
                ]);
            }

            if (! empty($questionData['tags'])) {
                $question->tags()->sync($questionData['tags']);
            }

            $createdQuestions[] = $question->load(['options', 'tags']);
        }

        // Optionally add to quiz
        if (! empty($validated['quiz_id']) && ! empty($createdQuestions)) {
            $quiz = Quiz::find($validated['quiz_id']);
            $maxOrder = $quiz->questions()->max('quiz_question.order') ?? 0;

            foreach ($createdQuestions as $index => $question) {
                $quiz->questions()->attach($question->id, ['order' => $maxOrder + $index + 1]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($createdQuestions).' pergunta(s) criada(s) com sucesso.',
            'data' => $createdQuestions,
            'errors' => $errors,
        ], 201);
    }
}
