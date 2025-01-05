<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /** Login functionality */
    public function login(Request $request)
    {
        /** Validates input */
        $validator = Validator::make($request->all(), [
            'email'=>'required|email',
            'password'=>'required'
        ]);
        if($validator->fails()){
            return response()->json(['message'=>$validator->messages()], 400);
        }
        $user = User::where('active', '1')
            ->where('email' , $request->email)
            //->where('password', Hash::make($request->password))
            ->first();
        if(!$user || !Hash::check($request->password, $user->password)){
            return response()->json(['message'=>'Invalid Credentials'], 500);
        }
        $token = $user->createToken($user->email);
        return [
            'user' => $user,
            'token' => $token->plainTextToken
        ];

        
        
    }

    /** Logout functionality */
    public function logout(Request $request)
    {
        // Revoke the token that was used for the request
        $request->user()->tokens->each(function ($token) {
            $token->delete();
        });

        // Return a response indicating that the user has been logged out
        return response()->json(['message' => 'Successfully logged out.'], 200);
    }
}
