<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            CourseSeeder::class,
            LessonSeeder::class,
            AssignmentSeeder::class,
            EnrollmentSeeder::class,
            AssignmentSubmissionSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}