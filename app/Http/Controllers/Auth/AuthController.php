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
    public function store(Request $request,User $user)
    {
        Gate::authorize('addEmployee',$user);
        $validated=$request->validate([
            'name'=>'required|max:255',
            'email'=>'required|email|unique:users',
            'password'=>'required|confirmed',
        ]);
        // if($validated->fails()){
        //     return response()->json([
        //         'errors'=>$validated->messages(),
        //     ]);
        // }
        $user=User::create($validated);

        $token=$user->createToken($request->name)->plainTextToken;

        return response()->json([
            'user'=>$user,
            'token'=>$token,
        ]);

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
    public function update(Request $request, User $user)
    {
        $validated=$request->validate([
            'name'=>'required|max:255',
            'email'=>'required|email',
            'password'=>'required|confirmed',
        ]);

        $user->updateOrFail($validated);

        $token=$user->createToken($request->name)->plainTextToken;

        return response()->json([
            'user'=>$user,
            'token'=>$token,
        ]);
    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json([
            'message'=>'Logged out!!',
        ]);
    }

    
}
