<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Exception;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    use ResponseTrait;

    /** Login functionality */
    public function login(LoginRequest $request)
    {
        $user = User::active()
            ->where('email' , $request->email);
        try {
            $user = $user->first();
        }
        catch(QueryException $e){
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, 'Database error occurred.', null,  true, $e->getMessage());
        }
        
        if(!$user || !Hash::check($request->password, $user->password)){
            return $this->responseError(Response::HTTP_UNAUTHORIZED, 'Invalid Credentials');
        }
        $token = $user->createToken($user->email);
        return $this->responseSuccess(Response::HTTP_OK, 'Successful login', [
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ]);    
        
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
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, 'An error occurred!');
        }        
        return $this->responseSuccess(Response::HTTP_OK, 'Successfully logged out');
    }
}
