<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Lesson;
use Faker\Factory as faker;
use Illuminate\Support\Testing\Fakes\Fake;

class AssignmentSeeder extends Seeder
{
    public function run()
    {
        $faker=Faker::create();
        // الحصول على بعض الدورات والدروس
        $courses = Course::where('is_published', true)->take(10)->get();
        $lessons = Lesson::take(30)->get();

        foreach ($courses as $course) {
            // إنشاء 2-4 واجب لكل دورة
            $assignmentCount = rand(2, 4);
            
            for ($i = 1; $i <= $assignmentCount; $i++) {
                Assignment::factory()->create([
                    'course_id' => $course->id,
                    'lesson_id' => $lessons->random()->id,
                    'title' => "Assignment $i: " . $faker->words(3, true),
                ]);
            }
        }

        // إضافة واجبات إضافية
        Assignment::factory()->count(15)->create();
    }
}