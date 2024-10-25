<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return User::all();
    }

    public function store(Request $request)
    {
        $validated=Validator::make($request->all(),[
        // $validated=$request->validate([
            'name'=>'required',
            'email'=>'required',
            'password'=>'required',
        ]);
        if($validated->fails()){
            $message=$validated->messages();
            return response()->json([
                'message'=>$message
            ]);
        }

        User::create($validated);
        return response()->json([
           $validated
        ]);
    }
    public function show(User $employee)
    {
        return $employee;
    }
    public function update(Request $request, string $id)
    {
        //
    }
    public function destroy(string $id)
    {
        //
    }
}
