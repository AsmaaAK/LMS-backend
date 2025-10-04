<?php
// app/Http/Controllers/API/CourseController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Http\Resources\CourseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{



    public function index(Request $request)
    {
        try {
            Log::info('Fetching courses...');

            // جلب كل الكورسات بدون فلترة أو شروط
            $courses = Course::latest()->get();

            // تجهيز الـ Response (مع بعض البيانات الافتراضية)
            $courses = $courses->map(function ($course) use ($request) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'level' => $course->level,
                    'price' => $course->price,
                    'is_published' => $course->is_published,
                    'image' => $course->image,
                    'instructor' => [
                        'name' => $course->user?->name ?? 'مدرس النظام',
                        'id'   => $course->user_id
                    ],
                    'category' => [
    'name' => 'عام',
    'id'   => 1
],
                    'lessons_count' => $course->lessons()->count() ?? 0,
                    'enrollments_count' => $course->enrollments()->count() ?? 0,
                    'can_edit' => $request->user() &&
                        ($request->user()->role === 'admin' ||
                         $course->user_id === $request->user()->id),
                    'is_enrolled' => $request->user()
                        ? $course->enrollments()->where('user_id', $request->user()->id)->exists()
                        : false,
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data'    => $courses,
                'meta'    => [
                    'total'        => $courses->count(),
                    'current_page' => 1,
                    'last_page'    => 1,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('CourseController Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب الكورسات',
                'error'   => $e->getMessage()
            ], 500);
        }
    }




    public function show(Request $request, $id)
    {
        try {

            // $course = Course::with([
            //     'instructor',
            //     'category',
            //     'lessons',
            //     'assignments',
            //     'enrollments'
            // ])->withCount(['lessons', 'enrollments'])->find($id);

            $course = Course::find($id);
            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'الكورس غير موجود'
                ], 404);
            }

            // إضافة بيانات إضافية
            $course->can_edit = $request->user() &&
                ($request->user()->role === 'admin' ||
                 $course->user_id === $request->user()->id);

            if ($request->user() && $request->user()->role === 'student') {
                $course->is_enrolled = $course->enrollments()
                    ->where('user_id', $request->user()->id)
                    ->exists();

                // حساب التقدم إذا كان مسجلاً
                if ($course->is_enrolled) {
                    $enrollment = $course->enrollments()
                        ->where('user_id', $request->user()->id)
                        ->first();
                    $course->progress = $enrollment->progress ?? 0;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $course
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching course: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب بيانات الكورس'
            ], 500);
        }
    }

    /**
     * إنشاء كورس جديد
     */
    public function store(Request $request)
    {
        // التحقق من الصلاحية
        if (!in_array($request->user()->role, ['teacher', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بإنشاء كورسات'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'level' => 'required|in:beginner,intermediate,advanced',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_published' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            $data['user_id'] = $request->user()->id;

            // معالجة رفع الصورة
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('courses', 'public');
                $data['image'] = $path;
            }

            $course = Course::create($data);

            // // تحميل العلاقات
            // $course->load(['instructor', 'category']);
            // $course->can_edit = true;

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الكورس بنجاح',
                'data' => $course
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating course: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'فشل في إنشاء الكورس'
            ], 500);
        }
    }

    /**
     * تحديث كورس
     */
    public function update(Request $request, $id)
    {
        try {
            $course = Course::find($id);

            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'الكورس غير موجود'
                ], 404);
            }

            // التحقق من الصلاحية
            if ($request->user()->role !== 'admin' && $course->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بتعديل هذا الكورس'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'category_id' => 'sometimes|exists:categories,id',
                'level' => 'sometimes|in:beginner,intermediate,advanced',
                'price' => 'sometimes|numeric|min:0',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
                'is_published' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();

            // معالجة رفع الصورة
            if ($request->hasFile('image')) {
                // حذف الصورة القديمة إذا كانت موجودة
                if ($course->image && Storage::disk('public')->exists($course->image)) {
                    Storage::disk('public')->delete($course->image);
                }

                $path = $request->file('image')->store('courses', 'public');
                $data['image'] = $path;
            }

            $course->update($data);

            // تحميل العلاقات
            $course->load(['instructor', 'category']);
            $course->can_edit = true;

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الكورس بنجاح',
                'data' => $course
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating course: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'فشل في تحديث الكورس'
            ], 500);
        }
    }

    /**
     * حذف كورس
     */
    public function destroy(Request $request, $id)
    {
        try {
            $course = Course::find($id);

            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'الكورس غير موجود'
                ], 404);
            }

            // التحقق من الصلاحية
            if ($request->user()->role !== 'admin' && $course->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بحذف هذا الكورس'
                ], 403);
            }

            // حذف الصورة إذا كانت موجودة
            if ($course->image && Storage::disk('public')->exists($course->image)) {
                Storage::disk('public')->delete($course->image);
            }

            $course->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الكورس بنجاح'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting course: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'فشل في حذف الكورس'
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
            $course = Course::find($courseId);

            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'الكورس غير موجود'
                ], 404);
            }

            // التحقق إذا كان الطالب مسجل بالفعل
            if ($user->enrollments()->where('course_id', $courseId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'أنت مسجل بالفعل في هذا الكورس'
                ], 400);
            }

            // التسجيل في الكورس
            $user->enrollments()->create([
                'course_id' => $courseId,
                'enrolled_at' => now(),
                'progress' => 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم التسجيل في الكورس بنجاح',
                'data' => [
                    'course_id' => $courseId,
                    'enrolled_at' => now()
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error enrolling in course: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'فشل في التسجيل في الكورس'
            ], 500);
        }
    }

    /**
     * جلب كورسات المدرس
     */
    public function instructorCourses(Request $request)
    {
        try {
            $courses = Course::where('user_id', $request->user()->id)
                ->with(['category', 'lessons'])
                ->withCount(['lessons', 'enrollments'])
                ->latest()
                ->get();

            // إضافة can_edit
            $courses->each(function ($course) {
                $course->can_edit = true;
            });

            return response()->json([
                'success' => true,
                'data' => $courses
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching instructor courses: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب كورساتي'
            ], 500);
        }
    }

    /**
     * جلب الكورسات المسجلة للطالب
     */
    public function enrolledCourses(Request $request)
    {
        try {
            $enrollments = $request->user()
                ->enrollments()
                ->with(['course.instructor', 'course.category', 'course.lessons'])
                ->get()
                ->map(function ($enrollment) {
                    $course = $enrollment->course;
                    $course->is_enrolled = true;
                    $course->progress = $enrollment->progress;
                    $course->enrolled_at = $enrollment->enrolled_at;
                    return $course;
                });

            return response()->json([
                'success' => true,
                'data' => $enrollments
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching enrolled courses: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب الكورسات المسجلة'
            ], 500);
        }
    }
}
