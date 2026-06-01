<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Choice;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Question::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'question_text' => $this->faker->sentence(10) . '?', // Teks pertanyaan
            'points' => $this->faker->randomElement([10, 20, 50]),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Question $question) {
            // Buat 4 pilihan jawaban
            $choices = Choice::factory(4)->create([
                'question_id' => $question->id,
                'is_correct' => false, // Awalnya set semua salah
            ]);
            
            // Ambil 1 pilihan acak dan set sebagai 'is_correct' => true
            $choices->random()->update(['is_correct' => true]);
        });
    }
}