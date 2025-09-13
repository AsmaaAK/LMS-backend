<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Programming', 'description' => 'Learn programming languages and software development'],
            ['name' => 'Design', 'description' => 'Graphic design, UI/UX, and creative courses'],
            ['name' => 'Business', 'description' => 'Business management, marketing, and entrepreneurship'],
            ['name' => 'Language', 'description' => 'Learn new languages and improve communication skills'],
            ['name' => 'Science', 'description' => 'Mathematics, physics, chemistry and other sciences'],
            ['name' => 'Arts', 'description' => 'Music, painting, photography and other arts'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // إضافة تصنيفات إضافية
        Category::factory()->count(4)->create();
    }
}