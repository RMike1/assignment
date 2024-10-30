<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use PharIo\Manifest\Email;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\Contracts\HasApiTokens;
use Illuminate\Routing\Controllers\Middleware;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Auth\Access\AuthorizationException;

class AuthController extends Controller
{

    public function all_employess(Request $request)
{
    if (Gate::allows('accessEmployee', User::class)) {
        return User::all();
    }
    return response()->json([
        'message' => "U have not access to employee list"
    ], Response::HTTP_FORBIDDEN);
}

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        $user = User::where('email', $validated['email'])->first();
    
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
    
        $token = $user->createToken($validated['email'])->plainTextToken;
    
        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }
    
    

    public function show(User $user)
    {
        return ['user'=>$user];
    }          



    
    public function add_employee(Request $request)
    {
        Gate::authorize('addEmployee',User::class);
        $validated=$request->validate([
            'name'=>'required|max:255',
            'email'=>'required|email|unique:users',
            'password'=>'required|confirmed',
        ]);
        $user=User::create($validated);

        return response()->json([
            'user'=>$user,
        ]);
    }

    public function update_employee(Request $request, User $user)
    {
        Gate::authorize('updateEmployee',$user);
        $validated=$request->validate([
            'email'=>'required|email|unique:users',
            'password'=>'required',
        ]);

        $user->updateOrFail($validated);

        return response()->json([
            'user'=>$user,
        ]);
    }

    public function delete_employee(User $user){

        Gate::authorize('deleteEmployee',$user);
        $user->delete();
        return response()->json([
            "message"=>"employee deleted successfully!!",
        ]);
    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json([
            'message'=>'Logged out!!',
        ]);
    }

    
}
