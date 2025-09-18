<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\UserController;

use Illuminate\Support\Facades\Route;

// Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('http://localhost:8000/api/register', [AuthController::class, 'register']);
    // Route::get('./api/register', [AuthController::class, 'register']);

    Route::post('api/login', [AuthController::class, 'login']);
    Route::middleware('can:viewAny,App\Models\User')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/statistics', [UserController::class, 'statistics']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    }); 
    // Protected routes
    // Route::middleware('auth:sanctum')->group(function () {
    //     Route::post('/logout', [AuthController::class, 'logout']);
    //     Route::get('/user', [AuthController::class, 'user']);
        
        // Admin only routes
        // Route::middleware('role:admin')->group(function () {
        //     Route::get('/users', [UserController::class, 'index']);
        //     Route::post('/users', [UserController::class, 'store']);
        //     Route::delete('/users/{user}', [UserController::class, 'destroy']);
        // });
        
        // Instructor routes
        // Route::middleware('role:instructor,admin')->group(function () {
        //     Route::post('/courses', [CourseController::class, 'store']);
        //     Route::put('/courses/{course}', [CourseController::class, 'update']);
        //     Route::delete('/courses/{course}', [CourseController::class, 'destroy']);
        // });
        
        // Student routes
        // Route::middleware('role:student')->group(function () {
        //     Route::get('/my-courses', [CourseController::class, 'myCourses']);
        //     Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll']);
        // });
    // });
// });