<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    public function definition()
    {
        $completed = $this->faker->boolean(30);
        
        return [
            'user_id' => \App\Models\User::factory()->student(),
            'course_id' => \App\Models\Course::factory()->published(),
            'progress' => $completed ? 100 : $this->faker->numberBetween(0, 99),
            'enrolled_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'completed_at' => $completed ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
        ];
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'progress' => 100,
                'completed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }

    public function inProgress()
    {
        return $this->state(function (array $attributes) {
            return [
                'progress' => $this->faker->numberBetween(1, 99),
                'completed_at' => null,
            ];
        });
    }

    public function notStarted()
    {
        return $this->state(function (array $attributes) {
            return [
                'progress' => 0,
                'completed_at' => null,
            ];
        });
    }
}