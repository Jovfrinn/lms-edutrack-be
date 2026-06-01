<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ChoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'choice_text' => $this->faker->sentence(3),
            'is_correct' => false, // Kita set default false
        ];
    }
}