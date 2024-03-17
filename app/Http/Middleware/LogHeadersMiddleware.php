<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

//        Log::info('Request URL: ' . $request->fullUrl());
//        $this->logHeaders('Request Headers', $request->header());

        $response = $next($request);
//
//        // Log response headers
//        if ($response->headers) {
//            $this->logHeaders('Response Headers', $response->headers->all());
//        }

        return $response;
    }

    /**
     * Log headers.
     *
     * @param string $label
     * @param array $headers
     */
    private function logHeaders($label, array $headers)
    {
        Log::info($label);
        foreach ($headers as $name => $values) {
            Log::info("$name: " . implode(', ', $values));
        }
    }
}
