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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|unique:users,phone_number|digits:10',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/'
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
        // Delete The Current Token Only
        auth()->user()->currentAccessToken()->delete();
        return response(status: 204);

    }
    // make User admin only admin role can do that
    public function makeUserAdmin(Request $request){
        $user = User::where('phone_number' , $request->phone_number) ->first();
        if (!$user){
            return response([
                'message' => 'user not found.'
            ],404);
        }
        $user->update([
            'role' => 'admin'
        ]);
        return response([
            'message' => 'User with phone number '.$user->phone_number.' is now admin'
            ],200);
    }
    // Return User Info
    public function user(){
        return response([
            'user' => auth()->user()
        ],200);
    }
    // Update User Info
    public function updateUser(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . auth()->id(),
            'phone_number' => 'sometimes|digits:10|unique:users,phone_number,' . auth()->id(),
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'location' => 'sometimes|string|max:255',
            'password' => 'sometimes|string|min:8|confirmed|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/',
        ]);

        if ($request->filled('password')) {
            $validatedData['password'] = bcrypt($request->password);
        }

        auth()->user()->update($validatedData);

        return response([
            'user' => auth()->user(),
            'message' => 'User Updated Successfully'
            ],200);
    }


}
