<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

/* public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:2048', // تحقق من الصورة
        ]);

        try {
            // رفع الصورة باستخدام التابع في BaseController
            $imagePath = $this->uploadImage($request, 'image');

            // يمكنك تخزين مسار الصورة في قاعدة البيانات
            // Product::create(['image' => $imagePath, ...]);

            return response()->json(['message' => 'تم رفع الصورة بنجاح', 'path' => $imagePath], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }*/

class AuthController extends Controller
{
    // Register New User
    public function register(Request $request)
    {

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|unique:users,phone_number|digits:10',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/'
        ]);

        $user = User::create([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'phone_number' => $request->input('phone_number'),
            'password' => Hash::make($request->input('password')),
        ]);
        session()->forget("cart{$user->id}");
        return response([
            'user' => $user,
            'token' => $user->createToken($user->phone_number)->plainTextToken
        ], 201);


    }

    // Login
    public function login(Request $request)
    {
        $val = $request->validate([
            'phone_number' => 'required|digits:10',
            'password' => 'required|min:8'
        ]);

        if (!Auth::attempt($val)) {
            return response([
                'message' => 'invalid credentials.'
            ], 403);
        }
        $id = Auth::user()->id;
        session()->forget("cart{$id}");

        return response([
            'user' => auth()->user(),
            'token' => auth()->user()->createToken(auth()->user()->phone_number)->plainTextToken
        ], 200);
    }

    // Logout
    public function logout()
    {
        // Delete The Current Token Only
        $id = auth()->user()->id;
        auth()->user()->currentAccessToken()->delete();


        session()->forget("cart{$id}");
        return response(status: 204);

    }

    public function logoutAll()
    {
        // Delete The Current Token Only
        $id = auth()->user()->id;
        auth()->user()->tokens()->delete();
        auth()->user()->deviceTokens()->delete();
        Session::forget("cart{$id}");
        return response(status: 204);

    }


    // make User admin only admin role can do that


    // Return User Info
    public function get_profile()
    {
        return response([
            'user' => auth()->user()
        ], 200);
    }


    // Update User Info
    public function update_profile(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,ico,jpg,gif,svg|max:2048',
            'location' => 'sometimes|string|max:255',

            // 'email' => 'sometimes|email|unique:users,email,' . auth()->id(),
            // 'phone_number' => 'sometimes|digits:10|unique:users,phone_number,' . auth()->id(),
            // 'password' => 'sometimes|string|min:8|confirmed|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/',
        ]);

        if (auth()->user()->image != null && Storage::disk('public')->exists(auth()->user()->image)) {
            Storage::disk('public')->delete(auth()->user()->image);
        }
        auth()->user()->update(
            [
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'location' => $request->input('location'),
                'image' => $this->uploadImage($request, 'profiles'),
            ]
        );

        return response([
            'user' => auth()->user(),
            'message' => 'User Updated Successfully'
        ], 200);
    }


}
