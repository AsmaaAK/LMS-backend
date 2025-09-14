<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Http\Resources\CourseResource;
use App\Http\Resources\CourseCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\UserResource; 



class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with(['instructor', 'category', 'lessons'])
            ->withCount(['lessons', 'enrollments']);

        // التصفية حسب التصنيف
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // التصفية حسب المستوى
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        // التصفية حسب النشر
        if ($request->has('is_published')) {
            $query->where('is_published', $request->is_published);
        }

        // البحث
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // للمعلمين: عرض دوراتهم فقط
        if ($request->user()->role === 'teacher') {
            $query->where('user_id', $request->user()->id);
        }

        // الترتيب
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_dir', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 10);
        $courses = $query->paginate($perPage);

        return new CourseCollection($courses);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'level' => 'required|in:beginner,intermediate,advanced',
            'price' => 'required|numeric|min:0',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_published' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['user_id'] = $request->user()->id;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('courses', 'public');
            $data['image'] = $path;
        }

        $course = Course::create($data);

        return response()->json([
            'message' => 'Course created successfully',
            'course' => new CourseResource($course->load(['instructor', 'category']))
        ], 201);
    }

    public function show(Course $course)
    {
        // $this->authorize('view', $course);
        
        $course->load(['instructor', 'category', 'lessons', 'assignments']);
        return new CourseResource($course);
    }

    public function update(Request $request, Course $course)
    {
        // $this->authorize('update', $course);

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
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        if ($request->hasFile('image')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($course->image && Storage::disk('public')->exists($course->image)) {
                Storage::disk('public')->delete($course->image);
            }
            
            $path = $request->file('image')->store('courses', 'public');
            $data['image'] = $path;
        }

        $course->update($data);

        return response()->json([
            'message' => 'Course updated successfully',
            'course' => new CourseResource($course->load(['instructor', 'category']))
        ]);
    }

    public function destroy(Course $course)
    {
        // $this->authorize('delete', $course);

        // حذف الصورة إذا كانت موجودة
        if ($course->image && Storage::disk('public')->exists($course->image)) {
            Storage::disk('public')->delete($course->image);
        }

        $course->delete();

        return response()->json([
            'message' => 'Course deleted successfully'
        ]);
    }

    public function enrolledCourses(Request $request)
    {
        $user = $request->user();
        $enrollments = $user->enrollments()->with('course.instructor')->get();
        
        return response()->json([
            'enrollments' => $enrollments
        ]);
    }

    public function instructorCourses(Request $request)
    {
        $user = $request->user();
        $courses = Course::where('user_id', $user->id)
            ->with(['category', 'lessons'])
            ->withCount(['lessons', 'enrollments'])
            ->get();
            
        return response()->json([
            'courses' => $courses
        ]);
    }
}