<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;

class LogoutController extends Controller
{
    use ApiResponseTrait;

    /**
     * Handle an incoming logout request.
     *
     * @param Request $request
     */
    public function logout(Request $request)
    {
        // Revoke the user's token
        $request->user()->currentAccessToken()->delete();

        // Return a success response with response code 200 - OK
        return $this->successResponse([], 'Logout successful', 200);
    }
}
