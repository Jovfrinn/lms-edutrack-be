<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CourseContent extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function courseModule()
    {
        return $this->belongsTo(CourseModule::class);
    }

    public function contentType()
    {
        return $this->belongsTo(ContentType::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'course_content_id', 'id');
    }

    public function contentSubmissions()
    {
        return $this->hasMany(ContentSubmission::class);
    }

    public function contentProgress()
    {
        return $this->hasMany(ContentProgress::class);
    }

    public function myContentProgress()
    {
        return $this->hasOne(ContentProgress::class);
    }
}
