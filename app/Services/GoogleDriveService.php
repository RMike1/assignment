<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class GoogleDriveService
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
    public function upload(UploadedFile $file,$userName)
    {
        $accessToken = $this->token();
        $folderId = config('services.google.folder_id');
        $filePath = $file->getPathname();

        $userProfile = strtolower($userName).'-profile-image.jpg';

        $metadata = [
            'name' => $userProfile,
            'parents' => [$folderId],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->attach('metadata', json_encode($metadata), 'metadata.json')
          ->attach('file', fopen($filePath, 'r'), $file->getClientOriginalName())
          ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');

        if ($response->successful()) {
            $fileId = json_decode($response->body())->id;
            $fileName = $this->getFileNameFromDrive($fileId, $accessToken);

            if (!$fileName) {
                return ['success' => false, 'error' => 'Failed to fetch file name from Google Drive'];
            }

            return ['success' => true, 'file_name' => $fileName, 'file_id' => $fileId];
        }

        return ['success' => false, 'error' => $response->body()];
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

    
}
