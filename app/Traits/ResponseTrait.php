<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

trait ResponseTrait
{
    /**
     * Returns the success data and message 
     * @param integer $status_code
     * @param string $message
     * @param object $data
     * @return JsonResponse
     */
    public function responseSuccess($status_code = JsonResponse::HTTP_OK, $message = "Success",  $data = null): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'errors'  => null,
            'data'    => $data,
        ], $status_code);
    }

    /**
     * Returns the errors data if there is any error
     *
     * @param object $errors
     * @return JsonResponse
     */
    public function responseError($status_code = JsonResponse::HTTP_BAD_REQUEST, $message = 'Invalid data', $errors = null, $flgLog = false, $logMessage = ''): JsonResponse
    {
        if($flgLog)
            Log::error($logMessage);

        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => $errors,
            'data'    => null,
        ], $status_code);
    }
}
