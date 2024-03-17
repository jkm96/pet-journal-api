<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomException extends Exception
{
    private $statusCode;
    private $data;

    /**
     * @param $data
     * @param $message
     * @param $statusCode
     */
    public function __construct($data, $message, $statusCode)
    {
        $this->message = $message;
        $this->statusCode = $statusCode;
        $this->data = $data;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->data,
            'statusCode' => $this->statusCode,
            'message' => $this->message,
            'succeeded' => false
        ], $this->statusCode);
    }
}
