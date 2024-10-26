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
use Illuminate\Routing\Controllers\HasMiddleware;

class AuthController extends Controller implements HasMiddleware
{

    public static function middleware(){
        return [
            new Middleware('auth:sanctum',except:['index','login','update','show']),
        ];
    }

    public function index()
    {
        return User::all();
    }
    public function login(Request $request)
    {
        $validated=$request->validate([
            'email'=>'required|email',
            'password'=>'required',
        ]);
        
        $user=User::where('email',$validated['email'])->first();

        if(!$user || !Hash::check($validated['password'],$user->password)){
            return "Credentials are not matched!!";
        }

        $token=$user->createToken($validated['email'])->plainTextToken;

        return response()->json([
            'user'=>$user,
            'token'=>$token,
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
            'name'=>'required|max:255',
            'email'=>'required|email',
        ]);

        $user->updateOrFail($validated);

        return response()->json([
            'user'=>$user,
        ]);


    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json([
            'message'=>'Logged out!!',
        ]);
    }

    
}
