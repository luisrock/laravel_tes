<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'tribunal',
        'tema_number',
        'category_id',
        'difficulty',
        'estimated_time',
        'color',
        'show_ads',
        'show_share',
        'show_progress',
        'random_order',
        'show_feedback_immediately',
        'meta_keywords',
        'status',
        'views_count',
    ];

    protected $casts = [
        'show_ads' => 'boolean',
        'show_share' => 'boolean',
        'show_progress' => 'boolean',
        'random_order' => 'boolean',
        'show_feedback_immediately' => 'boolean',
        'views_count' => 'integer',
        'estimated_time' => 'integer',
        'tema_number' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quiz) {
            if (empty($quiz->slug)) {
                $quiz->slug = Str::slug($quiz->title);
            }
            // Garantir slug único
            $originalSlug = $quiz->slug;
            $count = 1;
            while (static::where('slug', $quiz->slug)->exists()) {
                $quiz->slug = $originalSlug.'-'.$count++;
            }
        });
    }

    /**
     * Get the category of this quiz.
     */
    public function category()
    {
        return $this->belongsTo(QuizCategory::class, 'category_id');
    }

    /**
     * Get the questions in this quiz.
     */
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'quiz_question')
            ->withPivot('order', 'created_at')
            ->orderBy('pivot_order');
    }

    /**
     * Get the related teses (pesquisas).
     */
    public function teses()
    {
        return $this->belongsToMany('pesquisas', 'quiz_tese', 'quiz_id', 'pesquisa_id');
    }

    /**
     * Get the attempts for this quiz.
     */
    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Scope a query to only include published quizzes.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include draft quizzes.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to filter by tribunal.
     */
    public function scopeByTribunal($query, $tribunal)
    {
        return $query->where('tribunal', strtoupper($tribunal));
    }

    /**
     * Get the number of questions in this quiz.
     */
    public function getQuestionsCountAttribute()
    {
        return $this->questions()->count();
    }

    /**
     * Get the completion rate for this quiz.
     */
    public function getCompletionRateAttribute()
    {
        $total = $this->attempts()->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->attempts()->where('status', 'completed')->count();

        return round(($completed / $total) * 100, 1);
    }

    /**
     * Get the average score for this quiz.
     */
    public function getAverageScoreAttribute()
    {
        return $this->attempts()
            ->where('status', 'completed')
            ->avg('score') ?? 0;
    }

    /**
     * Increment views count.
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Get difficulty label in Portuguese.
     */
    public function getDifficultyLabelAttribute()
    {
        return match ($this->difficulty) {
            'easy' => 'Fácil',
            'medium' => 'Intermediário',
            'hard' => 'Difícil',
            default => 'Intermediário',
        };
    }

    /**
     * Get status label in Portuguese.
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'draft' => 'Rascunho',
            'published' => 'Publicado',
            'archived' => 'Arquivado',
            default => 'Rascunho',
        };
    }
}
