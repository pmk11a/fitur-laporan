<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\{Module}Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * {Module} Controller
 *
 * Handle {module} API endpoints.
 * Migrated from: Delphi Frm{Xxx}
 */
class {Module}Controller extends Controller
{
    protected {Module}Service $service;

    public function __construct({Module}Service $service)
    {
        $this->service = $service;
    }

    /**
     * Get data for authenticated user
     * Migrated from: Delphi FunctionName
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // ⚠️ PENTING: USERID bukan userid
        $userId = $request->user()->USERID;

        $data = $this->service->getData($userId);

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * Store/Update data
     * Migrated from: Delphi FunctionName
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'field1' => 'required|string',
            'field2' => 'required|integer|min:1|max:9999',
        ]);

        // ⚠️ PENTING: USERID bukan userid
        $userId = $request->user()->USERID;

        // Validate business logic
        $errors = $this->service->validate($validated);
        if (!empty($errors)) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $errors,
            ], 422);
        }

        // Save data
        $this->service->save($userId, $validated);

        // Get updated data
        $data = $this->service->getData($userId);

        return response()->json([
            'data' => $data,
            'message' => 'Data berhasil disimpan',
        ]);
    }

    /**
     * Delete data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $userId = $request->user()->USERID;

        $this->service->delete($userId);

        return response()->json([
            'message' => 'Data berhasil dihapus',
        ]);
    }

    /**
     * Check if data exists
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function check(Request $request): JsonResponse
    {
        $userId = $request->user()->USERID;

        return response()->json([
            'exists' => $this->service->exists($userId),
        ]);
    }
}
