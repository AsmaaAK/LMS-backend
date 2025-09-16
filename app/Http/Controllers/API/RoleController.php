<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', User::class);
        
        return response()->json(Role::with('permissions')->get());
    }

    public function assignRole(Request $request, User $user)
    {
        Gate::authorize('update', $user);
        
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);
        
        $user->roles()->sync([$request->role_id]);
        
        return response()->json([
            'message' => 'Role assigned successfully',
            'user' => $user->load('roles')
        ]);
    }

    public function removeRole(User $user, Role $role)
    {
        Gate::authorize('update', $user);
        
        $user->roles()->detach($role->id);
        
        return response()->json([
            'message' => 'Role removed successfully',
            'user' => $user->load('roles')
        ]);
    }
}