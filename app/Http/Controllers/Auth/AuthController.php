<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Shift;
use PharIo\Manifest\Email;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\DropboxService;
use App\Http\Controllers\Controller;
use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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
        return ['user' => $user];
    }
    public function add_employee(Request $request)
    {
        Gate::authorize('addEmployee', User::class);
    
        $validated = $request->validate([
            'name' => 'required|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'shift_id' => 'required',
            'profile_image' => 'file|required',
            'upload_type' => 'required|in:google,dropbox',
        ]);
    
        $storageService = $validated['upload_type'];
        $file = $request->file('profile_image');

        $userName=$validated['name'];
    
        if ($storageService === 'google') {
            $response = app(GoogleDriveService::class)->upload($file,$userName);
        } elseif ($storageService === 'dropbox') {
            $response = app(DropboxService::class)->upload($file,$userName);
        }
    
        if ($response['success']) {
            $validated['profile_image'] = $response['file_id'];
            $user = User::create($validated);
    
            return response()->json([
                'user' => $user,
                'employee_profile_image' => $response['file_name'],
            ]);
        }
    
        return response('Failed to upload: ' . $response['error'], 500);
    }

    public function update_employee(Request $request, $userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'message' => "Employee not found!"
            ], 404);
        }

        if (Gate::allows('updateEmployee', $user)) {
            $validated = $request->validate([
                'email' => 'required|email|unique:users,email,' . $user->id,
                'name' => 'required',
                'password' => 'required',
                'profile_image' => 'file|required',
                'upload_type' => 'required|in:google,dropbox',
            ]);

            $storageService = $validated['upload_type'];
            $file = $request->file('profile_image');
    
            $userName=$validated['name'];

    
            if ($storageService === 'google') {
                $response = app(GoogleDriveService::class)->upload($file,$userName);
            } elseif ($storageService === 'dropbox') {
                $response = app(DropboxService::class)->upload($file,$userName);
            }

            if ($response['success']) {
                $validated['profile_image'] = $response['file_id'];
                $user->update($validated);
        
                return response()->json([
                    'user' => $user,
                    'employee_profile_image' => $response['file_name'],
                ]);
            }
        
            return response('Failed to upload: ' . $response['error'], 500);
        }
        return response()->json([
            'message' => "You're not allowed to update an employee."
        ], Response::HTTP_FORBIDDEN);
    }

    public function delete_employee($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'message' => "Employee not found!"
            ], 404);
        }

        if (Gate::allows('deleteEmployee', $user)) {
            $user->delete();
            return response()->json([
                "message" => "employee deleted successfully!!",
            ]);
        }
        return response()->json([
            'message' => "U have not access to employee list"
        ], Response::HTTP_FORBIDDEN);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logged out!!',
        ]);
    }
}
