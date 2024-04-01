<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Support\Facades\Hash;
use App\Traits\ApiResponseTrait;

class ChangePasswordController extends Controller
{
    use ApiResponseTrait;
    /**
     * Change password
     *
     * @param ChangePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = User::find(auth()->user()->id);
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse('Current password is incorrect', 422);
        }
        $user->password = bcrypt($request->new_password);
        $user->save();
        return $this->successResponse([], 'Password changed successfully', 200);
    }
}
