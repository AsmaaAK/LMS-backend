<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run()
    {
        // إنشاء دورات رئيسية
        Course::factory()->create([
            'title' => 'Introduction to Web Development',
            'description' => 'Learn the basics of HTML, CSS, and JavaScript',
            'level' => 'beginner',
            'price' => 0,
            'is_published' => true,
        ]);

        Course::factory()->create([
            'title' => 'Advanced JavaScript Programming',
            'description' => 'Master advanced JavaScript concepts and frameworks',
            'level' => 'advanced',
            'price' => 99.99,
            'is_published' => true,
        ]);

        Course::factory()->create([
            'title' => 'UI/UX Design Fundamentals',
            'description' => 'Learn the principles of user interface and experience design',
            'level' => 'intermediate',
            'price' => 49.99,
            'is_published' => true,
        ]);

        // إنشاء دورات إضافية
        Course::factory()->published()->count(15)->create();
        Course::factory()->unpublished()->count(5)->create();
    }
}