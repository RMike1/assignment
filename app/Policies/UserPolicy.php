<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function addEmployee(User $user): Response
    {
        return $user->userType===1 ? Response::allow() : Response::deny('not allowed to add employee');

    }
}
