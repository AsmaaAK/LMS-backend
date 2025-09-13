<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lesson;
use App\Models\Course;
use Faker\Factory as faker;


class LessonSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        // الحصول على جميع الدورات المنشورة
        $courses = Course::where('is_published', true)->get();

        foreach ($courses as $course) {
            // إنشاء 5-10 درس لكل دورة
            $lessonCount = rand(5, 10);
            
            for ($i = 1; $i <= $lessonCount; $i++) {
                Lesson::factory()->create([
                    'course_id' => $course->id,
                    'order' => $i,
                    'title' => "Lesson $i: " .$faker->words(3, true),
                ]);
            }
        }

        // إضافة بعض الدروس الإضافية
        Lesson::factory()->count(20)->create();
    }
}