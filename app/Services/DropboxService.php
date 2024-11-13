<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DropboxService
{
    public function upload(UploadedFile $file,$userName)
    {
        $userProfile = strtolower($userName).'-profile-image.jpg';

        $fileName = $userProfile;
        $filePath = 'profile_images/' . $fileName;
        $stored = Storage::disk('dropbox')->putFileAs('profile_images', $file, $fileName);

        if ($stored) {
            return ['success' => true, 'file_id' => $filePath, 'file_name' => $fileName];
        }

        return ['success' => false, 'error' => 'Failed to upload to Dropbox'];
    }
}
