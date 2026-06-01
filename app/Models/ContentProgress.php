<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentProgress extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
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