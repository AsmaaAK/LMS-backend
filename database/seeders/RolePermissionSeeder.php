<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // إنشاء الصلاحيات
        $permissions = [
            ['name' => 'view_users', 'description' => 'View users'],
            ['name' => 'create_users', 'description' => 'Create users'],
            ['name' => 'edit_users', 'description' => 'Edit users'],
            ['name' => 'delete_users', 'description' => 'Delete users'],
            ['name' => 'view_courses', 'description' => 'View courses'],
            ['name' => 'create_courses', 'description' => 'Create courses'],
            ['name' => 'edit_courses', 'description' => 'Edit courses'],
            ['name' => 'delete_courses', 'description' => 'Delete courses'],
            ['name' => 'enroll_courses', 'description' => 'Enroll in courses'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // إنشاء الأدوار وتعيين الصلاحيات
        $adminRole = Role::create(['name' => 'admin', 'description' => 'Administrator']);
        $adminRole->permissions()->attach(Permission::all());

        $instructorRole = Role::create(['name' => 'instructor', 'description' => 'Instructor']);
        $instructorRole->permissions()->attach(Permission::whereIn('name', [
            'view_courses', 'create_courses', 'edit_courses', 'delete_courses'
        ])->get());

        $studentRole = Role::create(['name' => 'student', 'description' => 'Student']);
        $studentRole->permissions()->attach(Permission::whereIn('name', [
            'view_courses', 'enroll_courses'
        ])->get());
    }
}