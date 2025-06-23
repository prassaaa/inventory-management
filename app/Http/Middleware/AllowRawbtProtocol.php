<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AllowRawbtProtocol
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Jangan tambahkan header untuk BinaryFileResponse (file downloads)
        if ($response instanceof BinaryFileResponse) {
            return $response;
        }

        // Tambahkan header untuk mengizinkan protocol rawbt hanya untuk response biasa
        if (method_exists($response, 'header')) {
            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Access-Control-Allow-Methods', 'GET');
        }

        return $response;
    }
}
