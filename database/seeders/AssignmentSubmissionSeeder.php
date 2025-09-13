<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssignmentSubmission;
use App\Models\Assignment;
use App\Models\User;

class AssignmentSubmissionSeeder extends Seeder
{
    public function run()
    {
        // الحصول على بعض الواجبات والطلاب
        $assignments = Assignment::take(20)->get();
        $students = User::where('role', 'student')->get();

        foreach ($assignments as $assignment) {
            // 60% من الطلاب المسجلين في الدورة يقدمون الواجب
            $courseStudents = $assignment->course->students;
            $submissionCount = ceil($courseStudents->count() * 0.6);
            $randomStudents = $courseStudents->random($submissionCount);
            
            foreach ($randomStudents as $student) {
                AssignmentSubmission::factory()->create([
                    'user_id' => $student->id,
                    'assignment_id' => $assignment->id,
                ]);
            }
        }

        // إضافة تقديمات إضافية
        AssignmentSubmission::factory()->count(50)->create();
    }
}