<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentType extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function courseModules()
    {
        return $this->hasMany(CourseModule::class);
    }

    public function courseContents()
    {
        return $this->hasMany(CourseContent::class);
    }
}
