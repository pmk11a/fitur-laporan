<?php

namespace App\Http\Controllers;

use App\Services\BrowseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BrowseController extends Controller
{
    public function __construct(protected BrowseService $browseService)
    {
    }

    /**
     * GET /api/browse/types
     * Return all available browse types with their config.
     */
    public function types(Request $request): JsonResponse
    {
        $types = $this->browseService->types();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    /**
     * GET /api/browse/{kodeBrowse}?q={query}&limit=20&userMode={userMode}
     * Search records for a browse type.
     */
    public function search(Request $request, string $kodeBrowse): JsonResponse
    {
        $q = $request->query('q', '');
        $limit = (int) $request->query('limit', 20);
        $userMode = $request->query('userMode');

        $results = $this->browseService->search($kodeBrowse, $q, $limit, $userMode);

        // Return with JSON_INVALID_UTF8_IGNORE to handle any remaining bad bytes
        return response()->json([
            'success' => true,
            'data' => $results,
        ], 200, [], JSON_INVALID_UTF8_IGNORE);
    }

    /**
     * GET /api/browse/{kodeBrowse}/config
     * Return config for a browse type (columns, labels, default fields).
     */
    public function config(Request $request, string $kodeBrowse): JsonResponse
    {
        $config = $this->browseService->getConfig($kodeBrowse);

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Browse type not found: ' . $kodeBrowse,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'kodeBrowse' => $kodeBrowse,
                'keyField' => $config['keyField'],
                'labelField' => $config['labelField'],
                'additionalFields' => $config['additionalFields'] ?? [],
            ],
        ]);
    }
}