<?php

namespace App\Models;

use Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'explanation',
        'category_id',
        'difficulty',
        'times_answered',
        'times_correct',
    ];

    protected $casts = [
        'times_answered' => 'integer',
        'times_correct' => 'integer',
    ];

    /**
     * Get the category of this question.
     */
    public function category()
    {
        return $this->belongsTo(QuizCategory::class, 'category_id');
    }

    /**
     * Get the options for this question.
     */
    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('letter');
    }

    /**
     * Get the correct option for this question.
     */
    public function correctOption()
    {
        return $this->hasOne(QuestionOption::class)->where('is_correct', true);
    }

    /**
     * Get the quizzes that include this question.
     */
    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class, 'quiz_question')
            ->withPivot('order', 'created_at');
    }

    /**
     * Get the tags for this question.
     */
    public function tags()
    {
        return $this->belongsToMany(QuestionTag::class, 'question_tag', 'question_id', 'tag_id');
    }

    /**
     * Get the related teses (pesquisas).
     */
    public function teses()
    {
        return $this->belongsToMany('pesquisas', 'question_tese', 'question_id', 'pesquisa_id');
    }

    /**
     * Get the answers for this question.
     */
    public function answers()
    {
        return $this->hasMany(QuizAnswer::class);
    }

    /**
     * Get the success rate for this question.
     */
    public function getSuccessRateAttribute()
    {
        if ($this->times_answered === 0) return 0;
        return round(($this->times_correct / $this->times_answered) * 100, 1);
    }

    /**
     * Get difficulty label in Portuguese.
     */
    public function getDifficultyLabelAttribute()
    {
        return match($this->difficulty) {
            'easy' => 'Fácil',
            'medium' => 'Intermediário',
            'hard' => 'Difícil',
            default => 'Intermediário',
        };
    }

    /**
     * Increment statistics after an answer.
     */
    public function recordAnswer(bool $isCorrect)
    {
        $this->increment('times_answered');
        if ($isCorrect) {
            $this->increment('times_correct');
        }
    }

    /**
     * Scope a query to search by text.
     */
    public function scopeSearch($query, $search)
    {
        if (empty($search)) return $query;
        
        return $query->where('text', 'like', "%{$search}%");
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        if (empty($categoryId)) return $query;
        
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to filter by difficulty.
     */
    public function scopeByDifficulty($query, $difficulty)
    {
        if (empty($difficulty)) return $query;
        
        return $query->where('difficulty', $difficulty);
    }

    /**
     * Scope a query to filter by tags.
     */
    public function scopeByTags($query, array $tagIds)
    {
        if (empty($tagIds)) return $query;
        
        return $query->whereHas('tags', function ($q) use ($tagIds) {
            $q->whereIn('question_tags.id', $tagIds);
        });
    }

    /**
     * Get a short preview of the question text.
     */
    public function getPreviewAttribute()
    {
        return Str::limit($this->text, 100);
    }
}
