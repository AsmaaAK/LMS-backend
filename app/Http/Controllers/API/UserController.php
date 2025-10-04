<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    
    public function index(Request $request)
    {
    // التحقق من الصلاحية
    if (!Gate::allows('viewAny', User::class)) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized. Insufficient permissions.'
        ], 403);
    }

    // 🔹 بناء query مع الفلترة
    $query = User::query();

   
    if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%");
        });
    }

    // الفلترة بالدور
        if ($request->has('role') && !empty($request->role)) {
            if ($request->role === 'teacher') {
                $query->where(function($q) {
                    $q->where('role', 'teacher')
                      ->orWhere('role', 'instructor');
                });
            } else {
                $query->where('role', $request->role);
            }
        }

    //  إضافة الفلترة بالحالة إذا كان الحقل موجوداً
    if ($request->has('status') && !empty($request->status)) {
        $query->where('status', $request->status);
    }

    $users = $query->get();

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
    try {
        Log::info('Updating user:', ['user_id' => $user->id, 'data' => $request->all()]);

        //مؤقتاً: تعطيل الصلاحيات
        if (!Gate::allows('update', $user)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'role' => 'sometimes|string|in:admin,teacher,instructor,student', // 🔹 استخدام role بدلاً من role_id
            'status' => 'sometimes|string|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['name', 'email', 'role', 'status']);

        // 🔹 تحديث كلمة المرور فقط إذا تم تقديمها
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        Log::info('User updated successfully:', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User updated successfully.'
        ]);

    } catch (\Exception $e) {
        Log::error('Error updating user: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Error updating user: ' . $e->getMessage()
        ], 500);
    }
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

    // استخدام حقل role المباشر بدلاً من العلاقة
    $totalUsers = User::count();
    $adminUsers = User::where('role', 'admin')->count();
    $instructorUsers = User::where('role', 'teacher')->count(); // أو 'instructor'
    $studentUsers = User::where('role', 'student')->count();

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