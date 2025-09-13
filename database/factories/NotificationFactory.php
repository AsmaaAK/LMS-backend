<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    public function definition()
    {
        $types = [
            'assignment_graded',
            'new_assignment',
            'deadline_reminder',
            'course_announcement',
            'enrollment_approved',
            'system_alert'
        ];
        
        $read = $this->faker->boolean(40);
        
        return [
            'type' => $this->faker->randomElement($types),
            'data' => [
                'message' => $this->faker->sentence(),
                'related_object' => [
                    'id' => $this->faker->randomNumber(),
                    'type' => $this->faker->word()
                ]
            ],
            'read_at' => $read ? $this->faker->dateTimeBetween('-1 week', 'now') : null,
            'user_id' => \App\Models\User::factory(),
        ];
    }

    public function read()
    {
        return $this->state(function (array $attributes) {
            return [
                'read_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            ];
        });
    }

    public function unread()
    {
        return $this->state(function (array $attributes) {
            return [
                'read_at' => null,
            ];
        });
    }

    public function assignmentGraded()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'assignment_graded',
                'data' => [
                    'message' => 'Your assignment has been graded',
                    'assignment_id' => $this->faker->randomNumber(),
                    'score' => $this->faker->numberBetween(0, 100)
                ],
            ];
        });
    }

    public function newAssignment()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'new_assignment',
                'data' => [
                    'message' => 'New assignment available',
                    'assignment_id' => $this->faker->randomNumber(),
                    'course_id' => $this->faker->randomNumber()
                ],
            ];
        });
    }
}