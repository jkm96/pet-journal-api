<?php

namespace App\Exceptions;

use App\Utils\Helpers\ResponseHelpers;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $message = 'Entry for ' . str_replace('App\\', '', $e->getModel()) . ' not found';
            return ResponseHelpers::ConvertToJsonResponseWrapper([], $message, 404);
        }

        if ($e instanceof NotFoundHttpException) {
            return ResponseHelpers::ConvertToJsonResponseWrapper([], "Page Not Found.", 404);
        }

        return parent::render($request, $e);
    }
}
