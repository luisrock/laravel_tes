<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Get the quizzes in this category.
     */
    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'category_id');
    }

    /**
     * Get the questions in this category.
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'category_id');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
