<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogAuthenticatedRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if ($user) {
            Log::info('Authenticated API request', [
                'user_id' => $user->id,
                'email' => $user->email,
                'method' => $request->method(),
                'path' => $request->path(),
            ]);
        }

        return $next($request);
    }
}
