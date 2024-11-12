<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use PharIo\Manifest\Email;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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


    private function token()
    {
        $client_id = config('services.google.client_id');
        $client_secret = config('services.google.client_secret');
        $refresh_token = config('services.google.refresh_token');

        $response = Http::post('https://oauth2.googleapis.com/token', [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token'
        ]);

        $accessTokenData = json_decode($response->getBody(), true);
        return $accessTokenData['access_token'] ?? null;
    }

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
    private function getFileNameFromDrive($fileId, $accessToken)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get('https://www.googleapis.com/drive/v3/files/' . $fileId . '?fields=name');

        if ($response->successful()) {
            $fileMetadata = json_decode($response->body(), true);
            return $fileMetadata['name'] ?? null;
        }

        return null;
    }

    public function add_employee(Request $request)
    {
        Gate::authorize('addEmployee', User::class);
        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'profile_image' => 'file|required',
            'upload_type' => 'required|in:google,dropbox',
        ]);
    
        $storageService = $validated['upload_type'];
        $file = $request->file('profile_image');
    
        if ($storageService === 'google') {
            $response = $this->uploadToGoogleDrive($file);
        } elseif ($storageService === 'dropbox') {
            $fileName = $file->getClientOriginalName();
            $filePath = 'profile_images/' . $fileName;
    
            $stored = Storage::disk('dropbox')->putFileAs('profile_images', $file, $fileName);
    
            if ($stored) {
                $response = ['success' => true, 'file_id' => $filePath, 'file_name' => $fileName];
            } else {
                $response = ['success' => false, 'error' => 'Failed to upload to Dropbox'];
            }
        }
    
        if ($response['success']) {
            $validated['profile_image'] = $response['file_id'];
            $user = User::create($validated);
    
            return response()->json([
                'user' => $user,
                'employee_profile_image' => $response['file_name'],
            ]);
        } else {
            return response('Failed to upload: ' . $response['error'], 500);
        }
    }
    
    private function uploadToGoogleDrive($file)
    {
        $accessToken = $this->token();
        $folderId = config('services.google.folder_id');
        $filePath = $file->getPathname();

        $metadata = [
            'name' => $file->getClientOriginalName(),
            'parents' => [$folderId],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->attach(
            'metadata',
            json_encode($metadata),
            'metadata.json'
        )->attach(
            'file',
            fopen($filePath, 'r'),
            $file->getClientOriginalName()
        )->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');

        if ($response->successful()) {
            $file_id = json_decode($response->body())->id;
            $fileName = $this->getFileNameFromDrive($file_id, $accessToken);

            if (!$fileName) {
                return ['success' => false, 'error' => 'Failed to fetch file name from Google Drive'];
            }

            return ['success' => true, 'file_name' => $fileName, 'file_id' => $file_id];
        } else {
            return ['success' => false, 'error' => $response->body()];
        }
    }


//     private function uploadToDropbox($file)
// {
//     // Store the file using the 'dropbox' disk defined in filesystems.php
//     $fileName = $file->getClientOriginalName();
//     $path = Storage::disk('dropbox')->putFileAs('/profile_images', $file, $fileName);

//     if ($path) {
//         return ['success' => true, 'file_name' => $fileName, 'path' => $path];
//     }

//     return ['success' => false, 'error' => 'Failed to upload to Dropbox'];
// }

    // Dropbox upload method
    // private function uploadToDropbox($file)
    // {
    //     $accessToken = config('services.dropbox.access_token');
    //     $filePath = $file->getPathname();
    //     $fileName = $file->getClientOriginalName();

    //     // Prepare the file for upload to Dropbox
    //     $fileContents = fopen($filePath, 'r');
    //     $uploadUrl = 'https://content.dropboxapi.com/2/files/upload';

    //     $headers = [
    //         'Authorization' => 'Bearer ' . $accessToken,
    //         'Dropbox-API-Arg' => json_encode([
    //             'path' => '/profile_images/' . $fileName,
    //             'mode' => 'add',
    //             'autorename' => true,
    //             'mute' => false,
    //         ]),
    //         'Content-Type' => 'application/octet-stream',
    //     ];

    //     $response = Http::withHeaders($headers)
    //         ->attach('file', $fileContents, $fileName)
    //         ->post($uploadUrl);

    //     if ($response->successful()) {
    //         // Return Dropbox file path or URL if needed
    //         return ['success' => true, 'file_name' => $fileName];  // You can return a URL or the file name
    //     } else {
    //         return ['success' => false, 'error' => $response->body()];
    //     }
    // }






























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
                'password' => 'required',
                'name' => 'required',
                'profile_image' => 'file|required',
            ]);


            $accessToken = $this->token();

            $file = $request->file('profile_image');
            $folderId = config('services.google.folder_id');

            $filePath = $file->getPathname();

            $metadata = [
                'name' => $file->getClientOriginalName(),
                'parents' => [$folderId],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->attach(
                'metadata',
                json_encode($metadata),
                'metadata.json'
            )->attach(
                'file',
                fopen($filePath, 'r'),
                $file->getClientOriginalName()
            )->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');

            if ($response->successful()) {

                $file_id = json_decode($response->body())->id;

                $fileName = $this->getFileNameFromDrive($file_id, $accessToken);


                $validated['profile_image'] = $file_id;
                $user->update($validated);
                return response()->json([
                    'user' => $user,
                    'employee_profile_image' => $fileName
                ]);
            } else {
                return response('Failed to upload: ' . $response->body(), 500);
            }
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
