<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentAssignment extends Model
{
    protected $guarded = [];

        public function assignment()
    {
        return $this->belongsTo(Assignments::class, 'assignment_id');
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
