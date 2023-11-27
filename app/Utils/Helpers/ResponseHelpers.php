<?php

namespace App\Utils\Helpers;

use App\Http\Resources\UserResource;

class ResponseHelpers
{
    public static function ConvertToJsonResponseWrapper($data, $message,$statusCode)
    {
        $succeeded = $statusCode === 200;
        return response()->json([
            "data" => $data,
            "statusCode" => $statusCode,
            "message" => $message,
            "succeeded" => $succeeded
        ], $statusCode);
    }
}
