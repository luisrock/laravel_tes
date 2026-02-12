<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'letter',
        'text',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /**
     * Get the question that owns this option.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the answers that selected this option.
     */
    public function answers()
    {
        return $this->hasMany(QuizAnswer::class, 'selected_option_id');
    }

    /**
     * Get the number of times this option was selected.
     */
    public function getTimesSelectedAttribute()
    {
        return $this->answers()->count();
    }

    /**
     * Get the percentage of times this option was selected.
     */
    public function getSelectionPercentageAttribute()
    {
        $totalAnswers = $this->question->times_answered;
        if ($totalAnswers === 0) {
            return 0;
        }

        return round(($this->times_selected / $totalAnswers) * 100, 1);
    }
}
