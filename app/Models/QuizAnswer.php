<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'selected_option_id',
        'is_correct',
        'time_spent_seconds',
        'answered_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'time_spent_seconds' => 'integer',
        'answered_at' => 'datetime',
    ];

    /**
     * Get the attempt that owns this answer.
     */
    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    /**
     * Get the question for this answer.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the selected option.
     */
    public function selectedOption()
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id');
    }

    /**
     * Record an answer.
     */
    public static function record(
        QuizAttempt $attempt,
        Question $question,
        ?QuestionOption $selectedOption,
        ?int $timeSpentSeconds = null
    ) {
        $isCorrect = $selectedOption && $selectedOption->is_correct;

        $answer = static::create([
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'selected_option_id' => $selectedOption?->id,
            'is_correct' => $isCorrect,
            'time_spent_seconds' => $timeSpentSeconds,
            'answered_at' => now(),
        ]);

        // Update question statistics
        $question->recordAnswer($isCorrect);

        return $answer;
    }
}
