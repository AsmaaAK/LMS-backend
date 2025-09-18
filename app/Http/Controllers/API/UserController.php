<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    
    public function index()
    {
        // التحقق من الصلاحية باستخدام Policy
        if (!Gate::allows('viewAny', User::class)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Insufficient permissions.'
            ], 403);
        }

        $users = User::with('roles')->get();

        return response()->json([
            'success' => true,
            'data' => $users,
            'message' => 'Users retrieved successfully.'
        ]);
    }

    /**
     * إنشاء مستخدم جديد (للمشرفين فقط)
     */
    public function store(Request $request)
    {
        if (!Gate::allows('create', User::class)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Insufficient permissions.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // تعيين الدور للمستخدم
        $user->roles()->attach($request->role_id);

        return response()->json([
            'success' => true,
            'data' => $user->load('roles'),
            'message' => 'User created successfully.'
        ], 201);
    }

    /**
     * عرض مستخدم محدد
     */
    public function show(User $user)
    {
        if (!Gate::allows('view', $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Insufficient permissions.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $user->load('roles'),
            'message' => 'User retrieved successfully.'
        ]);
    }

    /**
     * تحديث بيانات المستخدم
     */
    public function update(Request $request, User $user)
    {
        if (!Gate::allows('update', $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Insufficient permissions.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'role_id' => 'sometimes|exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['name', 'email']);
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // تحديث الدور إذا تم提供
        if ($request->has('role_id')) {
            $user->roles()->sync([$request->role_id]);
        }

        return response()->json([
            'success' => true,
            'data' => $user->load('roles'),
            'message' => 'User updated successfully.'
        ]);
    }

    /**
     * حذف المستخدم (للمشرفين فقط)
     */
    public function destroy(User $user)
    {
        if (!Gate::allows('delete', $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Insufficient permissions.'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.'
        ]);
    }

    /**
     * الحصول على إحصائيات المستخدمين
     */
    public function statistics()
    {
        if (!Gate::allows('viewAny', User::class)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Insufficient permissions.'
            ], 403);
        }

        $totalUsers = User::count();
        $adminUsers = User::whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->count();
        
        $instructorUsers = User::whereHas('roles', function($query) {
            $query->where('name', 'instructor');
        })->count();
        
        $studentUsers = User::whereHas('roles', function($query) {
            $query->where('name', 'student');
        })->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => $totalUsers,
                'admin_users' => $adminUsers,
                'instructor_users' => $instructorUsers,
                'student_users' => $studentUsers,
            ],
            'message' => 'User statistics retrieved successfully.'
        ]);
    }
}