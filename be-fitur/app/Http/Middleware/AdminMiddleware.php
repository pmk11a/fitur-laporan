<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->bearerToken()
            ?: $request->input('userId')
            ?: $request->query('userId');

        if (empty($userId)) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = DB::connection('sqlsrv')->table('DBFLPASS')
            ->where('USERID', $userId)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 401);
        }

        $tingkat = (int) ($user->TINGKAT ?? 0);
        $status = (int) ($user->STATUS ?? 0);

        if ($tingkat >= 99 || $status >= 255 || ($status & 128) > 0) {
            $request->setUserResolver(fn() => $user);
            return $next($request);
        }

        return response()->json(['message' => 'Access denied. Admin only.'], 403);
    }
}