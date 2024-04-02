<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegistrationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;

class RegisterController extends Controller
{
    use ApiResponseTrait;

    /**
     * Handle an incoming registration request.
     *
     * @param StoreRegistrationRequest $request
     */
    public function register(StoreRegistrationRequest $request)
    {
        // Create a new user account
        $user = User::create([
            'full_name' => $request->full_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Attach a profile to the user
        $user->profile()->create([
            'full_name' => $request->full_name,
            'phone' => $request->phone,
        ]);

        // Generate a token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return the user and token details with response code 201 - Created
        return $this->createdResponse([
            'user' => $user,
            'token' => $token,
        ]);
    }
}
