<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json([
                    'code' => 403,
                    'status' => 'Token is Invalid'
                ], 403);
            } else if ($e instanceof TokenExpiredException) {
                return response()->json([
                    'code' => 401,
                    'status' => 'Token is Expired'
                ], 401);
            } else {
                return response()->json([
                    'code' => 404,
                    'status' => 'Authorization Token not found'
                ], 404);
            }
        }
        return $next($request);
    }
}
