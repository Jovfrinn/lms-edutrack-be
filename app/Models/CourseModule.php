<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseModule extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function contentType()
    {
        return $this->belongsTo(ContentType::class);
    }

    public function courseContents()
    {
        return $this->hasMany(CourseContent::class);
    }
}