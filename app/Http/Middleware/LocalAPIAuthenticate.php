<?php

namespace App\Http\Middleware;

use Closure;

class LocalAPIAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Only allow 127.0.0.0/8
        if ((ip2long($request->ip()) & 4278190080) !== 2130706432)
            return response(json_encode(["result" => false]), 403);
        return $next($request);
    }
}
