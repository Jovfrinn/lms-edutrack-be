<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Course;
use App\Models\ContentType;
use App\Models\User;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    /**
     * Define model's default (fallback) state.
     */
    public function definition()
    {
        $title = $this->faker->sentence(4);
        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . $this->faker->unique()->randomNumber(5),
            'description' => $this->faker->paragraph(3),
            'program_type' => 'E-Learning',
            'level' => $this->faker->randomElement(['Level 1', 'Level 2', 'Level 3']),
            'created_by' => User::first()->id ?? User::factory(),
            'is_published' => false,
            'published_at' => now(),
        ];
    }

    /**
     * State baru untuk membuat kursus dengan data realistis dari Bank Data
     */
    public function withSpecificData(array $data): self
    {
        // Ambil ID tipe konten dari database
        $types = ContentType::pluck('id', 'code');
        
        // Ambil User Admin
        $creator = User::first() ?? User::factory(['email' => 'admin@example.com']);

        // Return state yang meng-override 'definition'
        return $this->state([
            'title' => $data['title'],
            'thumbnail' => $data['thumbnail'],
            'slug' => Str::slug($data['title']) . '-' . Str::random(5), // Slug unik
            'level' => $data['level'],
            'created_by' => $creator->id,
        ])
        ->afterCreating(function (Course $course) use ($data, $types) {
            
            foreach ($data['contents'] as $index => $content) {
                
                // 1. Buat Konten Kursus
                $courseContent = $course->courseContents()->create([
                    'content_type_id' => $types[$content['type']],
                    'title' => $content['title'],
                    'order_index' => $index + 1,
                    'description' => $content['desc'] ?? null,
                    'file_path' => $content['path'] ?? null,
                ]);

                // 2. Jika tipenya Kuis, buat Pertanyaan dan Jawabannya
                if ($content['type'] === 'quiz-pg' && isset($content['questions'])) {
                    foreach ($content['questions'] as $qData) {
                        
                        $question = $courseContent->questions()->create([
                            'question_text' => $qData['text'],
                            'points' => 50, // atau buat acak
                        ]);

                        // 3. Buat Pilihan Jawabannya
                        foreach ($qData['choices'] as $cData) {
                            $question->choices()->create([
                                'choice_text' => $cData['text'],
                                'is_correct' => $cData['correct'],
                            ]);
                        }
                    }
                }
            }
        });
    }
}