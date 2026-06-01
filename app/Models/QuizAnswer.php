<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class QuizAnswer extends Model
{
    protected $fillable = [
        'quiz_question_id',
        'answer_text',
        'media_path',
        'is_correct',
    ];

    public function question(): BelongsTo
    {
        // Asumsi: foreign key di tabel 'choices' adalah 'question_id'
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id', 'id');
    }

    protected $appends = ['answer_image_url'];

    public function getAnswerImageUrlAttribute()
    {
        if ($this->answer_image) {
            return url('storage/' . $this->answer_image);
        }
        return null;
    }

    protected $casts = [
    'is_correct' => 'boolean',
];
}
