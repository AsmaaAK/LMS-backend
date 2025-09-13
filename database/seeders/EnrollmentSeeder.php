<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Course;

class EnrollmentSeeder extends Seeder
{
    public function run()
    {
        // الحصول على الطلاب والدورات المنشورة
        $students = User::where('role', 'student')->get();
        $courses = Course::where('is_published', true)->get();

        foreach ($students as $student) {
            // تسجيل كل طالب في 2-4 دورات
            $enrollmentCount = rand(2, 4);
            $randomCourses = $courses->random($enrollmentCount);
            
            foreach ($randomCourses as $course) {
                Enrollment::factory()->create([
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                ]);
            }
        }

        // إضافة تسجيلات إضافية
        Enrollment::factory()->count(30)->create();
    }
}