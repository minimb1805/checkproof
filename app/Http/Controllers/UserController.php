<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Mail\CreateUserEmail;
use App\Mail\CreateUserAdminEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\QueryException;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
       /** validates input, if any */
        $validator = Validator::make($request->all(), [
            'search'=>'string|max:100',
          //  'page'=>'integer', // not needed, in case of any string, it defaults to page 1
            'sort_by'=>'in:name,email,created_at',
            'sort_order' => 'in:asc,desc'
        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->messages()], 400);
        }

        /** default sorting, if not requested */
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'asc';
        
        $users = User::where('active', '1')
                ->withCount('orders');
        if($request->search){
            $users = $users->whereAny(['name', 'email'], 'like', '%'.$request->search.'%');
        }
        $users = $users->orderBy($sortBy , $sortOrder);
        try {
            $users = $users->paginate(RECORDS_PER_PAGE);
        }
        catch(QueryException $e){
            return response()->json(['error' => 'Database error occurred!'], 500);
        }
        
        $currentUser = $request->user();
        if($currentUser && $users) {
            $users = $users->map(function ($user) use ($currentUser) {
                    $user->can_edit = $this->canEditUser($currentUser, $user);
                    return $user;
                });
        }
        return UserResource::collection($users);
        return response()->json(['message'=>'No user found matching critera.'], 200);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        /** Validates input */
        $validator = Validator::make($request->all(), [
                    'name'=>'required|string|min:3|max:50',
                    'email'=>'required|email|unique:users,email',
                    'password'=>'required|string|min:8'
                ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->messages()], 400);
        }

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
            Mail::to(APP_ADMIN_EMAIL)->queue(new CreateUserAdminEmail($request->name, $request->email));
        }
        catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while sending emails, you might not receive a confirmation email.'], 500);
        }
        /** Mails to the user and admin queued */
        

        return response()->json([
            'data'=> new UserResource($user)
        ], 200);
        return response()->json(['error'=>'An error occurred while creating user. Please try again later!'], 500);
    }

    /**
     * Logic for determining if the current user can edit the specific user
     */
    private function canEditUser($currentUser, $user)
    {   
        switch ($currentUser->role) {
            case 'admin':
                return true;
            case 'manager':
                return ($user->role === 'user' || $currentUser->id === $user->id);
            case 'user':
                return $currentUser->id === $user->id;
            default:
                return false;
        }
    }

}
