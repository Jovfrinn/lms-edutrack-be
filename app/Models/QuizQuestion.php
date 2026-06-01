<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizQuestion extends Model
{
    protected $fillable = [
        'quiz_id',
        'question_text',
        'media_path',
        'point',
    ];

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'quiz_question_id', 'id');
    }

    /**
     * Sebuah Pertanyaan dimiliki oleh satu Kuis. (Opsional, tapi bagus untuk struktur)
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quizzes::class, 'quiz_id', 'id');
    }
}
