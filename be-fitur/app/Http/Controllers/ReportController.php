<?php

namespace App\Http\Controllers;

use App\Models\PERKIRAAN;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService)
    {
    }

    /**
     * Get sidebar menu for current user
     * GET /api/reports/menu
     */
    public function menu(Request $request): JsonResponse
    {
        $userId = optional($request->user())->USERID ?? $request->input('userId', '');

        if (empty($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'User ID required'
            ], 401);
        }

        $menu = $this->reportService->getSidebarMenu($userId);

        return response()->json([
            'success' => true,
            'data' => [
                'menus' => $menu,
                'count' => count($menu)
            ]
        ]);
    }

    /**
     * Get report configuration by KODEMENU
     * GET /api/reports/{kodeMenu}
     */
    public function show(Request $request, string $kodeMenu): JsonResponse
    {
        $config = $this->reportService->getReportConfig($kodeMenu);

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Report not found'
            ], 404);
        }

        // Get user default period from dbperiode
        $userId = optional($request->user())->USERID ?? $request->input('userId', '');
        $defaultPeriod = $this->reportService->getUserDefaultPeriod($userId);

        $config['defaultPeriod'] = $defaultPeriod;

        return response()->json([
            'success' => true,
            'data' => $config
        ]);
    }

    /**
     * Generate report preview
     * POST /api/reports/{kodeMenu}/preview
     */
    public function preview(Request $request, string $kodeMenu): JsonResponse
    {
        $filters = $request->input('filters', []);

        // Get userId from authenticated user or request
        $userId = optional($request->user())->USERID ?? $request->input('userId', '');
        $filters['userId'] = $userId;

        $result = $this->reportService->generateReport($kodeMenu, $filters);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Report generation failed'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'datasets' => $result['datasets'],
            'groupedData' => $result['groupedData'],
            'grandTotal' => $result['grandTotal'],
            'config' => $result['config'],
            'errors' => $result['errors'] ?? []
        ]);
    }

    /**
     * Search perkiraan for autocomplete
     * GET /api/reports/perkiraan/search?q={query}
     */
    public function searchPerkiraan(Request $request): JsonResponse
    {
        $q = $request->query('q', '');
        $results = PERKIRAAN::searchAkun($q)
            ->select(['Perkiraan', 'Keterangan'])
            ->limit(20)
            ->get();
        return response()->json(['success' => true, 'data' => $results]);
    }
}