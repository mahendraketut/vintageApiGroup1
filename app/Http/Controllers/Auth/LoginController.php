<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\StoreLoginRequest;
use App\Traits\ApiResponseTrait;

class LoginController extends Controller
{
    use ApiResponseTrait;
    /**
     * Handle an incoming authentication request.
     *
     * @param StoreLoginRequest $request
     */
    public function login(StoreLoginRequest $request)
    {
        // Check if the user credentials are valid
        if (!auth()->attempt($request->only('email', 'password'))) {
            return $this->unauthorizedResponse('Invalid credentials');
        }

        // Get the authenticated user
        $user = User::where('email', $request->email)->firstOrFail();

        // Generate a token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return the user and token details with response code 200 - OK
        return $this->successResponse(
            [
                'user' => $user,
                'token' => $token,
            ],
            'User authenticated successfully',
            200
        );
    }
}
