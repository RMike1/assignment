<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function accessAttendance(User $user): Response
    {
        return $user->userType===1 ? Response::allow() : Response::deny("U have not access to check attendance list");
    }
    public function accessEmployee(User $user): Response
    {
        return $user->userType===1 ? Response::allow() : Response::deny("U have not access to employee list");
    }
    public function addEmployee(User $user): Response
    {
        return $user->userType===1 ? Response::allow() : Response::deny("U're not allowed to add employee");
    }
    public function updateEmployee(User $user): Response
    {
        return $user->userType===1 ? Response::allow() : Response::deny("U're not allowed to update employee");
    }
    public function deleteEmployee(User $user): Response
    {
        return $user->userType===1 ? Response::allow() : Response::deny("U're not allowed to delete employee");
    }
}
