<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserPreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Universal User Preference Controller
 *
 * One set of endpoints to handle all user preferences across the app.
 * Pattern: {namespace}/{key}
 *
 *   GET    /api/preferences                          -> all namespaces
 *   GET    /api/preferences?namespace=format         -> one namespace
 *   GET    /api/preferences/{namespace}/{key}        -> one value
 *   PUT    /api/preferences/{namespace}/{key}        -> set one value
 *   DELETE /api/preferences/{namespace}/{key}        -> delete one value
 *   POST   /api/preferences/bulk                     -> bulk save
 */
class UserPreferenceController extends Controller
{
    public function __construct(
        private UserPreferenceService $service
    ) {}

    /**
     * Resolve user_id from request.
     * For now we read from query/body (auth context varies per app).
     */
    private function resolveUserId(Request $request): int
    {
        // 1. From explicit param (admin override, etc.)
        if ($request->filled('user_id')) {
            return (int) $request->input('user_id');
        }
        // 2. From authenticated user
        $user = $request->user();
        if ($user) {
            return (int) $user->id;
        }
        // 3. Fallback: header X-User-Id
        $hdr = $request->header('X-User-Id');
        if ($hdr) {
            return (int) $hdr;
        }
        return 1; // default for dev
    }

    /**
     * GET /api/preferences[?namespace=xxx]
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);
        $namespace = $request->query('namespace');

        if ($namespace) {
            return response()->json([
                'namespace' => $namespace,
                'preferences' => $this->service->getNamespace($userId, $namespace),
            ]);
        }

        return response()->json([
            'preferences' => $this->service->getAll($userId),
        ]);
    }

    /**
     * GET /api/preferences/{namespace}/{key}
     */
    public function show(Request $request, string $namespace, string $key): JsonResponse
    {
        $userId = $this->resolveUserId($request);
        $value = $this->service->get($userId, $namespace, $key);

        return response()->json([
            'namespace' => $namespace,
            'key' => $key,
            'value' => $value,
        ]);
    }

    /**
     * PUT /api/preferences/{namespace}/{key}
     * Body: arbitrary JSON value
     */
    public function update(Request $request, string $namespace, string $key): JsonResponse
    {
        $userId = $this->resolveUserId($request);
        $value = $request->all(); // accept any shape

        $this->service->set($userId, $namespace, $key, $value);

        return response()->json([
            'message' => 'Preference saved',
            'namespace' => $namespace,
            'key' => $key,
            'value' => $value,
        ]);
    }

    /**
     * DELETE /api/preferences/{namespace}/{key}
     */
    public function destroy(Request $request, string $namespace, string $key): JsonResponse
    {
        $userId = $this->resolveUserId($request);
        $deleted = $this->service->delete($userId, $namespace, $key);

        return response()->json([
            'message' => $deleted ? 'Preference deleted' : 'Preference not found',
            'namespace' => $namespace,
            'key' => $key,
        ]);
    }

    /**
     * POST /api/preferences/bulk
     * Body: [{namespace, key, value}, ...]
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);
        $items = $request->validate([
            '*.namespace' => 'required|string|max:50',
            '*.key' => 'required|string|max:100',
            '*.value' => 'present',
        ]);

        $this->service->bulkSet($userId, $items);

        return response()->json([
            'message' => 'Bulk preferences saved',
            'count' => count($items),
        ]);
    }

    /**
     * DELETE /api/preferences/{namespace}
     * Remove all preferences in a namespace.
     */
    public function destroyNamespace(Request $request, string $namespace): JsonResponse
    {
        $userId = $this->resolveUserId($request);
        $count = $this->service->deleteNamespace($userId, $namespace);

        return response()->json([
            'message' => "Deleted {$count} preferences",
            'namespace' => $namespace,
        ]);
    }
}
