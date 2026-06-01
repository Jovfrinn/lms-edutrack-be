<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentSubmission extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function courseContent()
    {
        return $this->belongsTo(CourseContent::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}