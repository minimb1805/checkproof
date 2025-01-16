<?php

namespace App\Http\Controllers;

//use Illuminate\Http\Request;
use App\Http\Requests\UserListRequest;
use App\Http\Requests\UserCreateRequest;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Mail\CreateUserEmail;
use App\Mail\CreateUserAdminEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;
class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(UserListRequest $request)
    {
        /** default sorting, if not requested */
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'asc';
        
        $users = User::active()
                ->withCount('orders');
        if($request->search){
            $users = $users->whereAny(['name', 'email'], 'like', '%'.$request->search.'%');
        }
        $users = $users->orderBy($sortBy , $sortOrder);
        //Log::debug('A message.'.$sortBy);
        //Log::error('An error message.'.$sortBy);
        
        try {
            
            $users = $users->paginate(config('app.record_per_page'));
        }
        catch(QueryException $e){
            return response()->json(['error' => 'Database error occurred!'], 500);
        }
        
        return UserResource::collection($users);
        //return response()->json(['message'=>'No user found matching critera.'], 200);
    }

    /**
     * Store a newly created user.
     */
    public function store(UserCreateRequest $request)
    {
        /** DB call for user creation */
        try{
            $user = User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>Hash::make($request->password)
            ]);
        }
        catch (QueryException $e) {
            return response()->json(['error' => 'Database error occurred!'], 500);
        }
        /** Mails to the user and admin queued */
        try {
            Mail::to($request->email)->queue(new CreateUserEmail($request->name));
            Mail::to(config('mail.from.address'))->queue(new CreateUserAdminEmail($request->name, $request->email));
        }
        catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while sending emails, you might not receive a confirmation email.'], 500);
        }
        /** Mails to the user and admin queued */
        

        return response()->json([
            'data'=> new UserResource($user)
        ], 201);
        // return response()->json(['error'=>'An error occurred while creating user. Please try again later!'], 500);
    }
}
