<?php

namespace App\Traits;

trait GetAuthenticatedUser
{
    private function authenticatedUser()
    {
        return auth('sanctum')->user();
    }
    
}