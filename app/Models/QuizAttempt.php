<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'user_id',
        'session_id',
        'started_at',
        'finished_at',
        'score',
        'total_questions',
        'time_spent_seconds',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'score' => 'integer',
        'total_questions' => 'integer',
        'time_spent_seconds' => 'integer',
    ];

    /**
     * Get the quiz for this attempt.
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the user who made this attempt.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the answers for this attempt.
     */
    public function answers()
    {
        return $this->hasMany(QuizAnswer::class, 'attempt_id');
    }

    /**
     * Start a new attempt.
     */
    public static function start(Quiz $quiz, ?int $userId = null, ?string $sessionId = null)
    {
        return static::create([
            'quiz_id' => $quiz->id,
            'user_id' => $userId,
            'session_id' => $sessionId,
            'started_at' => now(),
            'total_questions' => $quiz->questions()->count(),
            'status' => 'in_progress',
        ]);
    }

    /**
     * Complete the attempt.
     */
    public function complete()
    {
        $this->score = $this->answers()->where('is_correct', true)->count();
        $this->finished_at = now();
        $this->time_spent_seconds = $this->started_at->diffInSeconds($this->finished_at);
        $this->status = 'completed';
        $this->save();

        return $this;
    }

    /**
     * Mark as abandoned.
     */
    public function abandon()
    {
        $this->status = 'abandoned';
        $this->save();

        return $this;
    }

    /**
     * Get the score as percentage.
     */
    public function getScorePercentageAttribute()
    {
        if ($this->total_questions === 0) {
            return 0;
        }

        return round(($this->score / $this->total_questions) * 100, 1);
    }

    /**
     * Get formatted time spent.
     */
    public function getFormattedTimeAttribute()
    {
        if (! $this->time_spent_seconds) {
            return '--';
        }

        $minutes = floor($this->time_spent_seconds / 60);
        $seconds = $this->time_spent_seconds % 60;

        if ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }

        return "{$seconds}s";
    }

    /**
     * Check if attempt is completed.
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if attempt is in progress.
     */
    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    /**
     * Scope a query to only include completed attempts.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include in progress attempts.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }
}
