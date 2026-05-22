<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Accept userId from query string, body, or Bearer token (token = userId in this system)
        $userId = $request->query('userId') ?: $request->input('userId') ?: $request->bearerToken();

        if (empty($userId)) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = DB::connection('sqlsrv')->table('DBFLPASS')
            ->where('USERID', $userId)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 401);
        }

        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}