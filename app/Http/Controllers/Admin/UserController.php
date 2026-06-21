<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(10);
        return UserResource::collection($users);
    }

    public function promote($id)
    {
        $user = User::findOrFail($id);

        // منع ترقية نفسه
        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك ترقية نفسك'
            ], 403);
        }

        $user->promoteToAdmin();

        return response()->json([
            'success' => true,
            'message' => 'تم ترقية المستخدم إلى أدمن بنجاح',
            'user' => new UserResource($user)
        ]);
    }

    // تخفيض مستخدم إلى عادي
    public function demote($id)
    {
        $user = User::findOrFail($id);

        // منع تخفيض نفسه
        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك تخفيض صلاحيات نفسك'
            ], 403);
        }

        $user->demoteToUser();

        return response()->json([
            'success' => true,
            'message' => 'تم تخفيض صلاحيات المستخدم بنجاح',
            'user' => new UserResource($user)
        ]);
    }

    // حذف مستخدم
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // منع حذف نفسه
        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك حذف حسابك بنفسك'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المستخدم بنجاح'
        ]);
    }

    // إنشاء مستخدم جديد
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'nullable|in:admin,user'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'user'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء المستخدم بنجاح',
            'user' => new UserResource($user)
        ], 201);
    }
}
