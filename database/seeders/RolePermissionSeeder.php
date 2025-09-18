<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // تعطيل فحص المفاتيح الأجنبية مؤقتاً
        Schema::disableForeignKeyConstraints();
        
        // مسح البيانات الحالية
        DB::table('permission_role')->truncate();
        DB::table('role_user')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        
        // تمكين فحص المفاتيح الأجنبية مرة أخرى
        Schema::enableForeignKeyConstraints();

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
            ['name' => 'view_dashboard', 'description' => 'View dashboard'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // إنشاء الأدوار
        $adminRole = Role::create(['name' => 'admin', 'description' => 'Administrator']);
        $instructorRole = Role::create(['name' => 'instructor', 'description' => 'Instructor']);
        $studentRole = Role::create(['name' => 'student', 'description' => 'Student']);

        // تعيين الصلاحيات للأدوار باستخدام sync
        $adminRole->permissions()->sync(Permission::all()->pluck('id'));

        $instructorPermissions = Permission::whereIn('name', [
            'view_courses', 'create_courses', 'edit_courses', 'delete_courses', 'view_dashboard'
        ])->get();
        
        $instructorRole->permissions()->sync($instructorPermissions->pluck('id'));

        $studentPermissions = Permission::whereIn('name', [
            'view_courses', 'enroll_courses', 'view_dashboard'
        ])->get();
        
        $studentRole->permissions()->sync($studentPermissions->pluck('id'));

        // تعيين دور admin للمستخدم الأول إذا existed
        if ($user = \App\Models\User::first()) {
            $user->roles()->sync([$adminRole->id]);
        }

        $this->command->info('Roles and permissions seeded successfully!');
    }
}