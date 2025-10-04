<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    /**
     * الحصول على كورسات الطالب
     */
    public function courses(Request $request)
    {
        try {
            $user = $request->user();
            
            // الحصول على الكورسات المسجلة مع تقدم الطالب
            $courses = $user->courses()->with(['instructor', 'category'])->get()->map(function($course) use ($user) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'image' => $course->image,
                    'price' => $course->price,
                    'level' => $course->level,
                    'instructor' => $course->instructor ? [
                        'id' => $course->instructor->id,
                        'name' => $course->instructor->name,
                        'email' => $course->instructor->email
                    ] : null,
                    'category' => $course->category ? [
                        'id' => $course->category->id,
                        'name' => $course->category->name
                    ] : null,
                    'progress' => $this->calculateCourseProgress($user, $course),
                    'lessons_count' => $course->lessons_count ?? 0,
                    'completed_lessons' => $user->completedLessons()->where('course_id', $course->id)->count(),
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $courses,
                'message' => 'Student courses retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student courses.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * التسجيل في كورس
     */
    public function enroll(Request $request, $courseId)
    {
        try {
            $user = $request->user();
            $course = Course::findOrFail($courseId);

            // التحقق إذا كان الطالب مسجل بالفعل
            if ($user->courses()->where('course_id', $courseId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is already enrolled in this course.'
                ], 403);
            }

            $user->courses()->attach($courseId, [
                'enrolled_at' => now(),
                'progress' => 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully enrolled in the course.',
                'data' => [
                    'course_id' => $courseId,
                    'enrolled_at' => now()
                ]
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to enroll in course.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * حساب تقدم الطالب في الكورس
     */
    private function calculateCourseProgress($user, $course)
    {
        $totalLessons = $course->lessons()->count();
        if ($totalLessons === 0) return 0;

        $completedLessons = $user->completedLessons()->where('course_id', $course->id)->count();
        return round(($completedLessons / $totalLessons) * 100);
    }

    /**
     * الحصول على تقدم الطالب في كورس معين
     */
    public function courseProgress(Request $request, $courseId)
    {
        try {
            $user = $request->user();
            $course = Course::with('lessons')->findOrFail($courseId);

            $progress = $this->calculateCourseProgress($user, $course);

            return response()->json([
                'success' => true,
                'data' => [
                    'course_id' => $courseId,
                    'progress' => $progress,
                    'total_lessons' => $course->lessons->count(),
                    'completed_lessons' => $user->completedLessons()->where('course_id', $courseId)->count()
                ],
                'message' => 'Course progress retrieved successfully.'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve course progress.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}