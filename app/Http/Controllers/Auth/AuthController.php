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

        return null; // Return null if the request fails
    }

    public function add_employee(Request $request)
    {
        Gate::authorize('addEmployee', User::class);
        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'profile_image' => 'file|required',
        ]);

        $accessToken = $this->token();
        // dd($accessToken);

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
            // $validated['profile_image']='ImageID';

            $user = User::create($validated);

            return response()->json([
                'user' => $user,
                'employee_profile_image'=>$fileName
            ]);
        } else {
            return response('Failed to upload: ' . $response->body(), 500);
        }
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

                $validated['profile_image'] = $file_id;
                $user->update($validated);
                return response()->json([
                    'user' => $user,
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
