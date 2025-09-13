<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentSubmissionFactory extends Factory
{
    public function definition()
    {
        $graded = $this->faker->boolean(60);
        
        return [
            'user_id' => \App\Models\User::factory()->student(),
            'assignment_id' => \App\Models\Assignment::factory(),
            'file_path' => 'submissions/' . $this->faker->uuid() . '.pdf',
            'comment' => $this->faker->optional(0.4)->paragraph(),
            'score' => $graded ? $this->faker->numberBetween(0, 100) : null,
            'submitted_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function graded()
    {
        return $this->state(function (array $attributes) {
            return [
                'score' => $this->faker->numberBetween(0, 100),
            ];
        });
    }

    public function ungraded()
    {
        return $this->state(function (array $attributes) {
            return [
                'score' => null,
            ];
        });
    }

    public function highScore()
    {
        return $this->state(function (array $attributes) {
            return [
                'score' => $this->faker->numberBetween(80, 100),
            ];
        });
    }

    public function lowScore()
    {
        return $this->state(function (array $attributes) {
            return [
                'score' => $this->faker->numberBetween(0, 59),
            ];
        });
    }
}