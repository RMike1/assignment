<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UploadController extends Controller
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

    public function uploadOnGoogle(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $accessToken = $this->token();
        if (!$accessToken) {
            return response('Failed to retrieve access token', 500);
        }

        $file = $request->file('file');
        $folderId = config('services.google.folder_id');

        $filePath=$file->getPathname();
        
        $metadata = [
            'name' => $file->getClientOriginalName(),
            'parents' => [$folderId],
        ];

        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->attach(
            'metadata', json_encode($metadata), 'metadata.json'
        )->attach(
            'file', fopen($filePath, 'r'), $file->getClientOriginalName()
        )->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');




        if ($response->successful()) {

            $file_id = json_decode($response->body())->id;

            return response('Upload successful!');
        } else {
            return response('Failed to upload: ' . $response->body(), 500);
        }
    }

    public function getUploadOnGoogle()
    {
        return view('google-upload');
    }
}
