<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HandlesImageUploads
{
    /**
     * Store the uploaded images and return the file names.
     *
     * @param  array  $file
     * @param  string  $destination
     * @return array
     */
    protected function storeImage($files, string $destination = 'public/images'): array
    {

        $fileNames = [];

        // Ensure $files is always an array
        if (!is_array($files)) {
            $files = [$files];
        }

        // Iterate over each file
        foreach ($files as $file) {
            // Generate a unique image name based on timestamp and uniqid
            $imageName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Store the file with the generated image name
            $file->storeAs($destination, $imageName);

            // Add the generated image name to the array of filenames
            $fileNames[] = $imageName;
        }

        // Return the array of filenames for the stored images
        return $fileNames;
    }

    /**
     * Update the uploaded images and return the file names.
     *
     * @param array $files
     * @param string $destination
     * @param array|null $oldFileNames
     * @return array
     */
    protected function updateImages(array $files, string $destination = 'public/images', ?array $oldFileNames = null): array
    {
        $fileNames = [];

        // Ensure $files is always an array
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            $imageName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs($destination, $imageName);
            $fileNames[] = $imageName;
        }

        if ($oldFileNames) {
            foreach ($oldFileNames as $oldFileName) {
                Storage::delete($destination . '/' . $oldFileName);
            }
        }

        return $fileNames;
    }
}
