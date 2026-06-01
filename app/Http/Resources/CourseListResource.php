<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // $assignment = $this->whenLoaded('assignments', $this->assignments->first());

        // $status = 'draft';
        // if ($this->is_published) {
        //     $status = 'ongoing'; 
            
        //     if ($assignment) {
        //         $status = ($assignment->status === 'pending') ? 'ongoing' : $assignment->status;
        //     }
        // }

        $totalLessons = $this->lesson_count ?? 0;
        $progress = 0; 
        
        // if ($assignment && $assignment->status === 'completed') {
        //     $progress = $totalLessons;
        // }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'course_contents' => $this->courseContents,
            'author' => $this->whenLoaded('creator', $this->creator->name),
            'cover' => $this->thumbnail,
            'level' => $this->level,
            'is_published' => $this->is_published,
            
            'date' => $this->updated_at->format('M d'), 
            
            'lessons' => $totalLessons,
            
            'video_count' => $this->video_count ?? 0,
            'audio_count' => $this->audio_count ?? 0,
            'assigment_count' => $this->assigment_count ?? 0,
            'quiz_pg_count' => $this->quiz_pg_count ?? 0,
            'ebook_count' => $this->ebook_count ?? 0,
        ];
    }
}