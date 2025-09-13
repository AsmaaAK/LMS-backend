<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
       
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@lms.com',
            'password' => bcrypt('password'),
        ]);

        User::factory()->manager()->create([
            'name' => 'Manager User',
            'email' => 'manager@lms.com',
            'password' => bcrypt('password'),
        ]);

        User::factory()->teacher()->create([
            'name' => 'Teacher User',
            'email' => 'teacher@lms.com',
            'password' => bcrypt('password'),
        ]);

        User::factory()->student()->create([
            'name' => 'Student User',
            'email' => 'student@lms.com',
            'password' => bcrypt('password'),
        ]);

       
        User::factory()->teacher()->count(5)->create();
        User::factory()->student()->count(20)->create();
    }
}