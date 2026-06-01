<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizResult extends Model
{
    protected $fillable = [
        'user_id',
        'quiz_id',
        'total_point',
    ];

    public function user()
{
    // Pastikan foreign key 'user_id' ada di tabel quiz_results
    return $this->belongsTo(User::class, 'user_id');
}
}
