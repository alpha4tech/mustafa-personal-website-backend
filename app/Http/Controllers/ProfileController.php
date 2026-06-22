<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Services\ActivityLogger;

class ProfileController extends Controller
{
    /**
     * Get authenticated user
     */
    public function getUser(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
{
    $user = Auth::user();

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        'phone' => 'nullable|string|max:20',
        'password' => 'nullable|string|min:8',
    ]);

    $data = [
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
    ];

    if ($request->filled('password')) {
        $data['password'] = Hash::make($request->password);
    }

    $user->update($data);

    return response()->json([
        'success' => true,
        'message' => 'Profile updated successfully',
        'data' => $user
    ]);
}
    /**
     * Update user avatar
     */
    // public function updateAvatar(Request $request)
    // {
    //     $user = Auth::user();

    //     $request->validate([
    //         'avatar' => 'required|image|mimes:jpeg,png,gif|max:2048'
    //     ]);

    //     // Delete old avatar if exists
    //     if ($user->avatar && Storage::exists('public/avatars/' . $user->avatar)) {
    //         Storage::delete('public/avatars/' . $user->avatar);
    //     }

    //     // Upload new avatar
    //     $avatar = $request->file('avatar');
    //     $filename = time() . '_' . uniqid() . '.' . $avatar->getClientOriginalExtension();
    //     $path = $avatar->storeAs('public/avatars', $filename);

    //     $user->update([
    //         'avatar' => $filename
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Avatar updated successfully',
    //         'avatar_url' => asset('storage/avatars/' . $filename)
    //     ]);
    // }

    /**
     * Change user password
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed'
        ]);

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.']
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }
}
