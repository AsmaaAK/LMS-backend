<?php
// app/Policies/CoursePolicy.php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['teacher', 'admin', 'student']); // أضف student
    }

    public function view(User $user, Course $course): bool
    {
        return $user->id === $course->user_id || 
               $user->role === 'admin' || 
               $user->role === 'manager' ||
               $user->enrollments()->where('course_id', $course->id)->exists();
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['teacher', 'admin']);
    }

    public function update(User $user, Course $course): bool
    {
        return $user->id === $course->user_id || $user->role === 'admin';
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->id === $course->user_id || $user->role === 'admin';
    }

    public function restore(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }
}