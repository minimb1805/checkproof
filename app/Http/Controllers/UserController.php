<?php

namespace App\Http\Controllers;

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
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;

class UserController extends Controller
{
    use ResponseTrait;

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
        
        try {            
            $users = $users->paginate(config('app.record_per_page'));
        }
        catch(QueryException $e){
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, 'Database error occurred.', null,  true, $e->getMessage());
        }

        return UserResource::collection($users);
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
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, 'Database error occurred.', null,  true, $e->getMessage());
        }
        /** Mails to the user and admin queued */
        try {
            Mail::to($request->email)->queue(new CreateUserEmail($request->name));
            Mail::to(config('mail.from.address'))->queue(new CreateUserAdminEmail($request->name, $request->email));
        }
        catch (Exception $e) {
            return $this->responseSuccess(Response::HTTP_CREATED, 'User created successfully, but you might not receive a confirmation email.', [
                'data'=> new UserResource($user),
            ]);
        }     

        return $this->responseSuccess(Response::HTTP_CREATED, 'User created successfully, you will receive a confirmation email in some time.', [
            'data'=> new UserResource($user),
        ]);

    }
}
