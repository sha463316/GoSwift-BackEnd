<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
            if (auth()->user()->role === 'admin') {
                return $next($request);
            }
            // if user role is not admin
            return response()->json(['message' => 'Access denied. Admins only.'], 403);
        }

}
