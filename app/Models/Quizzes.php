<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quizzes extends Model
{
    protected $fillable = [
        'quiz_title',
        'description',
        'total_questions',
        'total_point',
    ];
    public function questions()
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id')->orderBy('created_at', 'asc');
    }
}
