<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomException extends Exception
{
    private $status_code;

    /**
     * @param $message
     * @param $status_code
     */
    public function __construct($message, $status_code)
    {
        $this->message = $message;
        $this->status_code = $status_code;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message'=> $this->message
        ], $this->status_code);
    }
}
