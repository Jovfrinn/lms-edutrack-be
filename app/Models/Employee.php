<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function courseAssignments()
    {
        return $this->hasMany(CourseAssignment::class);
    }

    public function activityPoints()
    {
        return $this->hasMany(ActivityPoint::class);
    }

    public function contentSubmissions()
    {
        return $this->hasMany(ContentSubmission::class);
    }

    public function contentProgress()
    {
        return $this->hasMany(ContentProgress::class);
    }

    public function questionAnswers()
    {
        return $this->hasMany(QuestionAnswer::class);
    }

    protected static function booted()
    {
        static::deleting(function ($post) {
            $post->user()->delete();
        });
    }
    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (!$this->profile_photo) {
            return null;
        }

        return asset('storage/' . $this->profile_photo);
    }
}
