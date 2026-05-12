<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Handle login request - validates against ERP users table (DBFLPASS)
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'userId' => 'required_without:username|string',
            'username' => 'required_without:userId|string',
            'password' => 'required|string'
        ]);

        $username = $request->input('userId') ?: $request->input('username');
        $password = $request->input('password');

        try {
            // Find user in DBFLPASS table by USERID or UID
            $user = DB::connection('sqlsrv')->table('DBFLPASS')
                ->where('USERID', $username)
                ->orWhere('UID', $username)
                ->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // DBFLPASS uses UID2 field for password (base64 encoded)
            $storedPassword = $user->UID2 ?? '';

            // Verify password - UID2 is base64 encoded
            if ($this->verifyPassword($password, $storedPassword)) {
                return $this->createAuthResponse($user);
            }

            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $userData = $this->getUserFromToken($token);

        if (!$userData) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        return response()->json(['user' => $userData]);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Create authentication response with token
     */
    private function createAuthResponse($user): JsonResponse
    {
        $token = Str::random(64);
        $tingkat = (int) ($user->TINGKAT ?? 0);
        $status = (int) ($user->STATUS ?? 0);

        // ACCESS dari STATUS (bitmask), fallback ke TINGKAT
        $access = $status > 0 ? $status : (1 << max(0, $tingkat - 1));

        // Get user period from dbperiode
        $period = $this->getUserPeriod($user->USERID);

        return response()->json([
            'user' => [
                'id' => $user->USERID,
                'username' => $user->UID ?: $user->USERID,
                'name' => $user->FullName ?: $user->USERID,
                'TINGKAT' => $tingkat,
                'STATUS' => $status,
                'access' => $access,
                'level' => $tingkat,
                'role' => $this->determineRole($tingkat, $status),
                'kodeBag' => $user->kodeBag ?? null,
                'KodeKasir' => $user->KodeKasir ?? null,
                'Kodegdg' => $user->Kodegdg ?? null,
                'BULAN' => $period['bulan'] ?? null,
                'TAHUN' => $period['tahun'] ?? null,
            ],
            'token' => $token
        ]);
    }

    /**
     * Get user period from dbperiode table
     */
    private function getUserPeriod(string $userId): array
    {
        try {
            $period = DB::connection('sqlsrv')->table('dbperiode')
                ->where('USERID', $userId)
                ->first();

            if ($period) {
                return [
                    'bulan' => (int) $period->BULAN,
                    'tahun' => (int) $period->TAHUN
                ];
            }
        } catch (\Exception $e) {
            // Ignore errors, return empty
        }

        return [];
    }

    /**
     * Verify password - UID2 is base64 encoded plain password
     */
    private function verifyPassword(string $input, string $stored): bool
    {
        if (empty($stored) || empty($input)) {
            return false;
        }

        // Try direct comparison
        if ($input === $stored) {
            return true;
        }

        // Try base64 decode and compare (UID2 is base64 encoded)
        $decoded = base64_decode($stored);
        if ($decoded !== false && $decoded === $input) {
            return true;
        }

        return false;
    }

    /**
     * Determine user role based on level (TINGKAT) and status
     */
    private function determineRole(int $tingkat, int $status): string
    {
        // TINGKAT 99 = Admin, atau STATUS dengan bitmask tinggi
        if ($tingkat >= 99 || $status >= 255 || ($status & 128) > 0) {
            return 'admin';
        }

        return 'user';
    }

    /**
     * Get user from token (simplified)
     */
    private function getUserFromToken(string $token): ?array
    {
        // In production, validate against stored tokens
        // For now, return null for invalid tokens
        if (strlen($token) < 32) {
            return null;
        }

        return null;
    }
}