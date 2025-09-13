<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    public function definition()
    {
        $levels = ['beginner', 'intermediate', 'advanced'];
        
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraphs(3, true),
            'image' => $this->faker->imageUrl(400, 300, 'education'),
            'category_id' => \App\Models\Category::factory(),
            'user_id' => \App\Models\User::factory()->teacher(),
            'level' => $this->faker->randomElement($levels),
            'price' => $this->faker->randomElement([0, 49.99, 99.99, 149.99]),
            'is_published' => $this->faker->boolean(80), // 80% chance of being published
        ];
    }

    public function published()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_published' => true,
            ];
        });
    }

    public function unpublished()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_published' => false,
            ];
        });
    }

    public function free()
    {
        return $this->state(function (array $attributes) {
            return [
                'price' => 0,
            ];
        });
    }

    public function premium()
    {
        return $this->state(function (array $attributes) {
            return [
                'price' => $this->faker->randomElement([49.99, 99.99, 149.99]),
            ];
        });
    }
}