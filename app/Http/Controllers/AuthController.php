<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
     $validate = $request->validate([
        'name' => 'required|string|max:255',
        'email'=> 'required|string|email|unique:users,email',
        'password'=> 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'=> $validate['name'],
            'email'=> $validate['email'],
            'password'=> Hash::make($validate['password']),
            'role' => 'user',
            ]);

            //generate the token (athorize)
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'user' => new UserResource($user),
                'token'=> $token,
                'role' => $user->role
            ]);
        }

        public function login(Request $request)
        {
             $request->validate([
                'email'=> 'required|string|email',
                'password'=> 'required|string|min:8',
            ]);

            $user = User::where('email', $request->email)->first();

            if(!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Theses credentials do not match our records.'
                ], 404);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'user' => new UserResource($user),
                'token'=> $token,
                'role' => $user->role
            ]);
        }

        public function user(Request $request)
        {
            return new UserResource($request->user());
        }

        public function logout(Request $request)
        {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        }
}
