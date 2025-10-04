<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\StudentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/csrf-token', function (Request $request) {
    return response()->json([
        'csrf_token' => csrf_token()
    ]);
});

// Routes المصادقة
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
// Routes المصادقة
// Route::post('/login', [AuthController::class, 'login']);
// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
// Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

// Debug route to echo back body and headers
Route::match(['GET','POST','PUT','PATCH'],'/debug-echo', function(Request $request) {
    return response()->json([
        'method' => $request->method(),
        'headers' => $request->headers->all(),
        'content_type' => $request->header('Content-Type'),
        'accept' => $request->header('Accept'),
        'query' => $request->query(),
        'json' => $request->json()->all(),
        'request_all' => $request->all(),
        'raw' => $request->getContent(),
    ]);
});

// Protected User CRUD routes (require auth and policy)
Route::middleware('auth:sanctum')->group(function () {
    // Course routes
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::put('/courses/{course}', [CourseController::class, 'update']);
    Route::delete('/courses/{course}', [CourseController::class, 'destroy']);
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::get('/users/statistics', [UserController::class, 'statistics']);
    Route::get('/courses/statistics', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'total_courses' => 12,
                'active_courses' => 8,
                'total_students' => 356,
                'average_rating' => 4.3
            ]
        ]);
    });

});

// Minimal instructor/student testing routes (auth only)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/instructor/courses', [CourseController::class, 'instructorCourses']);
    Route::get('/student/enrollments', [CourseController::class, 'enrolledCourses']);
    Route::post('/courses/{course}/enroll', [StudentController::class, 'enroll']);
});

// Route أساسي للاختبار
Route::get('/', function () {
    return response()->json([
        'message' => 'API routes are ready',
        'timestamp' => now(),
        'status' => 'success'
    ]);
});
Route::get('/api', function () {
    return response()->json(['message' => 'API routes are ready']);
});
