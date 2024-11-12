<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UploadController extends Controller
{
    private function token()
    {
        $client_id = \config('services.google.client_id');
        $client_secret = \config('services.google.client_secret');
        $refresh_token = \config('services.google.refresh_token');
        $folder_id = \config('services.google.folder_id');
        $response = Http::post('https://oauth2.googleapis.com/token', [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token'
        ]);

        $accesstoken = json_decode((string)$response->getBody(), true)['access_token'];
        return $accesstoken;
    }

    public function uploadOnGoogle(Request $request)
    {

        $validated = $request->validate([
            'file' => 'required',
        ]);

        $folder_id = \config('services.google.folder_id');

        $accesstoken = $this->token();
        // dd($accesstoken);

        $name = Str::slug($request->file->getClientOriginalName());
        $file_type = $request->file->getClientMimeType();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' .$accesstoken,
            'Content-Type' => 'Application/json'
        ])->post('https://www.googleapis.com/drive/v3/files', [
            'data' => $name,
            'mimeType' => $file_type,
            'uploadType' => 'resumable',
            'parents'=>[$folder_id]
        ]);

        if ($response->successful()) {
            return response('uploaded successful!!');
        } else {
            return response('Failed to upload: ' . $response->body(), 500);
        }
    }

    public function getUploadOnGoogle()
    {
        return view('google-upload');
    }
}
