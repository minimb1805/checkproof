<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Exception;

class AuthController extends Controller
{
    /** Login functionality */
    public function login(LoginRequest $request)
    {
        $user = User::active()
            ->where('email' , $request->email);
        try {
            $user = $user->first();
        }
        catch(QueryException $e){
            return response()->json(['error' => 'Database error occurred. Please try again later!'], 500);
        }
        
        if(!$user || !Hash::check($request->password, $user->password)){
            return response()->json(['message'=>'Invalid Credentials'], 401);
        }
        $token = $user->createToken($user->email);
        return response()->json([
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ], 200);

        
        
    }

    /** Logout functionality */
    public function logout(Request $request)
    {
        /** Revoke the token that was used for the request */ 
        try{

            $request->user()->tokens->each(function ($token) {
                $token->delete();
            });
        }
        catch (Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
        
        

        // Return a response indicating that the user has been logged out
        return response()->json(['message' => 'Successfully logged out.'], 200);
    }
}
