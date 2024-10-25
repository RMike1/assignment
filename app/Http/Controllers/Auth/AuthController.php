<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PharIo\Manifest\Email;

class AuthController extends Controller
{
    public function index()
    {
        return User::all();
    }
    public function addEmployee(Request $request)
    {
        // $validated=Validator::make($request->all(),[
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
            'name'=>'required|max:255',
            'email'=>'required|email|exists:users',
            'password'=>'required|confirmed',
        ]);
        
        $user=User::where('email',$validated['email'])->first();

        if(!$user || !Hash::check($validated['password'],$user->password)){
            return "Credentials are not matched!!";
        }

        $token=$user->createToken($validated['name'])->plainTextToken;

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
