<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseAssignment extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'assigned_at' => 'datetime',
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function courseQuiz()
    {
        return $this->belongsTo(Course::class)
            ->with([
                'courseContents' => function ($q) {
                    $q->where('content_type_id', 4);
                }
            ]);
    }


    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
