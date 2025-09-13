<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentFactory extends Factory
{
    public function definition()
    {
        $hasDeadline = $this->faker->boolean(70);
        
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraphs(2, true),
            'course_id' => \App\Models\Course::factory(),
            'lesson_id' => $this->faker->optional(0.3)->randomElement(\App\Models\Lesson::pluck('id')->toArray()),
            'deadline' => $hasDeadline ? $this->faker->dateTimeBetween('+1 week', '+1 month') : null,
            'max_score' => $this->faker->randomElement([100, 50, 20]),
        ];
    }

    public function withDeadline()
    {
        return $this->state(function (array $attributes) {
            return [
                'deadline' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            ];
        });
    }

    public function withoutDeadline()
    {
        return $this->state(function (array $attributes) {
            return [
                'deadline' => null,
            ];
        });
    }
}