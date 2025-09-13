<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LessonFactory extends Factory
{
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->paragraphs(5, true),
            'video_url' => $this->faker->randomElement([
                'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'https://vimeo.com/123456789',
                null
            ]),
            'course_id' => \App\Models\Course::factory(),
            'order' => $this->faker->numberBetween(1, 20),
        ];
    }
}