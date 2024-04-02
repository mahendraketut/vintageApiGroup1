<?php

namespace App\Http\Controllers\Profiles;

use App\Http\Controllers\Controller;

use App\Models\Profile;
use App\Http\Requests\UpdateProfileRequest;
use App\Traits\ApiResponseTrait;
use App\Traits\HandlesImageUploads;

class ProfileController extends Controller
{
    use ApiResponseTrait, HandlesImageUploads;
    /**
     * Display the specified resource.
     */
    public function show($profileId)
    {
        // Find the profile
        $profile = Profile::find($profileId)->with('user', 'image')->first();

        // Return the profile details with response code 200 - OK if found, or send a 404 - Not Found response
        if ($profile == null) {
            return $this->notFoundResponse('Profile not found');
        }
        return $this->successResponse($profile);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProfileRequest $request, $profileId)
    {
        // Find the profile and the user that owns it
        $profile = Profile::find($profileId);

        // Update the user details
        $profile->user->update([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'username' => $request->username,
        ]);

        // Update the profile details
        $profile->update([
            'full_name' => $request->full_name,
            'phone' => $request->phone,
        ]);

        // Check if an image was uploaded
        if ($request->hasFile('image')) {

            if ($profile->image) {
                // store the image in the storage folder using Handle Image Uploads trait
                $imageName = $this->updateImages([$request->file('image')], 'public/images', [$profile->image->url]);
            } else {
                // Create a new image record in the database and associate it with the profile using Handle Image Uploads trait
                $imageName = $this->storeImage($request->file('image'), 'public/images');
            }

            $profile->image()->updateOrCreate([], ['url' => $imageName[0]]);
        }

        // Return the updated profile details with response code 200 - OK
        return $this->successResponse($profile, 'Profile updated successfully');
    }
}
