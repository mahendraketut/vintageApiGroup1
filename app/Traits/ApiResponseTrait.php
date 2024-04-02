<?php

namespace App\Traits;

trait ApiResponseTrait
{
    /**
     * Send a success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    private function successResponse($data, string $message = '', int $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'memory used' => memory_get_usage() . ' bytes',
                'IP address' => request()->ip(),
                'time taken' => round(microtime(true) - LARAVEL_START, 2) . ' seconds',
                'user agent' => request()->userAgent()
            ]
        ], $status);
    }

    /**
     * Send an error response.
     *
     * @param string $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    private function errorResponse(string $message, int $status)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $status);
    }

    /**
     * API Response for created resource.
     * @param array $data
     */
    protected function createdResponse($data)
    {
        return $this->successResponse($data, 'Resource created successfully', 201);
    }

    /**
     * API Response for get resource.
     * @param array $data
     */
    protected function showResponse($data)
    {
        return $this->successResponse($data, 'Resource fetched successfully', 200);
    }

    /**
     * API Response for updated resource.
     * @param array $data
     */
    protected function updateResponse($data, $message = 'Resource updated successfully')
    {
        return $this->successResponse($data, $message, 200);
    }

    /**
     * API Response for deleted resource.
     * @param array $data
     */
    protected function deleteResponse($data, $message = 'Resource deleted successfully')
    {
        return $this->successResponse($data, $message, 200);
    }

    /**
     * API Response for restored resource.
     * @param array $data
     */
    protected function restoreResponse($data, $message = 'Resource restored successfully')
    {
        return $this->successResponse($data, $message, 200);
    }

    /**
     * API Response for resource not found.
     * @param string $message
     */
    protected function notFoundResponse($message = 'Resource not found')
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * API Response for validation error.
     * @param array $data
     */
    protected function validationErrorResponse($data)
    {
        return $this->errorResponse('Validation error', 422);
    }

    /**
     * API Response for unauthorized user.
     * @param string $message
     */
    protected function unAuthorisedResponse($message = 'Unauthorised')
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * API Response for forbidden user.
     * @param string $message
     */
    protected function forbiddenResponse($message = 'Forbidden')
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * API Response for internal server error.
     * @param string $message
     */
    protected function serverErrorResponse($message = 'Internal server error')
    {
        return $this->errorResponse($message, 500);
    }
}
