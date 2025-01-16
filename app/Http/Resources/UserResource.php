<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
        'id' => $this->id,
        'email' => $this->email,
        'name' => ucfirst($this->name),
        'role' => $this->role,
        'created_at' => $this->created_at,
        'orders_count' => $this->orders_count,
        'can_edit' => $this->canEditUser($this, $request->user()),
       ]; 
        //return parent::toArray($request);
    }

    /**
     * Logic for determining if the current user can edit the specific user
     */
    private function canEditUser($user, $currentUser = null)
    {   
        if($currentUser === null)
            return false;
        $loggedInCanEdit = match($currentUser->role){
            'admin'=>true,
            'manager'=>($user->role === 'user' || $currentUser->id === $user->id),
            'user'=>$currentUser->id === $user->id,
            default =>false,
        };
        return $loggedInCanEdit;
        
    }
}
