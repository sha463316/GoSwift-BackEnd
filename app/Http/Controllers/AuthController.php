<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Register New User
    public function register(Request $request)
    {

        $val = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|unique:users,phone_number|digits:10',
            'password' => 'required|min:8|confirmed'
        ]);

        $user = User::create([
            'first_name' => $val['first_name'],
            'last_name' => $val['last_name'],
            'email' => $val['email'],
            'phone_number' => $val['phone_number'],
            'password' => bcrypt($val['password'])
        ]);


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
        return response([
            'user' => auth()->user(),
            'token' => auth()->user()->createToken(auth()->user()->phone_number)->plainTextToken
        ], 200);
    }

    // Logout

    public function logout(){

        auth()->user()->tokens()->delete();
        return response(status: 204);

    }
}
