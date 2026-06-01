<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignments extends Model
{
    protected $guarded = [];

    public function contentAssignments()
    {
        return $this->hasOne(ContentAssignment::class, 'assignment_id');
    }

       public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
