<?php

namespace App\Http\Middleware;

use Closure;

class AllowRawbtProtocol
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Tambahkan header untuk mengizinkan protocol rawbt
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET');

        return $response;
    }
}
