<?php

namespace Database\Factories;

use App\Models\CourseContent;
use App\Models\ContentType;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseContentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CourseContent::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Ambil 1 tipe konten secara acak dari database
        $contentType = ContentType::inRandomOrder()->first();

        $filePath = null;
        if ($contentType->code === 'video') {
            $filePath = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'; // Link dummy
        } elseif ($contentType->code === 'ebook') {
            $filePath = '/storage/ebooks/dummy-' . $this->faker->word . '.pdf';
        }

        return [
            'content_type_id' => $contentType->id,
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(2),
            'file_path' => $filePath,
            'order_index' => 0, // Akan di-override oleh CourseFactory
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (CourseContent $content) {
            // Jika tipe kontennya adalah Kuis, buatkan pertanyaan
            if ($content->contentType->code === 'quiz-pg') {
                Question::factory(rand(3, 5))->create([ // Buat 3-5 pertanyaan
                    'course_content_id' => $content->id,
                ]);
            }
            // Jika tipe lain (video, ebook, assignment), tidak perlu buat apa-apa
        });
    }
}