<?php

namespace App\Http\Controllers;

use App\Models\PERKIRAAN;
use App\Services\ReportService;
use App\Services\BrowseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected BrowseService $browseService
    ) {
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

    // ============================================================
    // TEST BROWSE — untuk testing BrowseService dari ReportController
    // ============================================================

    /**
     * Get all available browse types (for testing)
     * GET /api/reports/test/browse/types
     */
    public function testBrowseTypes(Request $request): JsonResponse
    {
        $types = $this->browseService->types();

        return response()->json([
            'success' => true,
            'data' => [
                'types' => $types,
                'count' => count($types),
            ]
        ]);
    }

    /**
     * Test browse search for a specific kodeBrowse
     * GET /api/reports/test/browse/{kodeBrowse}?q={query}&limit=20&userMode={userMode}
     */
    public function testBrowseSearch(Request $request, string $kodeBrowse): JsonResponse
    {
        $q = $request->query('q', '');
        $limit = (int) $request->query('limit', 20);
        $userMode = $request->query('userMode');
        $parent = $request->query('parent', []);

        $results = $this->browseService->search($kodeBrowse, $q, $limit, $userMode, $parent);

        return response()->json([
            'success' => true,
            'data' => [
                'kodeBrowse' => $kodeBrowse,
                'query' => $q,
                'limit' => $limit,
                'results' => $results,
                'count' => count($results),
            ]
        ], 200, [], JSON_INVALID_UTF8_IGNORE);
    }

    /**
     * Get browse config for a specific kodeBrowse (for testing)
     * GET /api/reports/test/browse/{kodeBrowse}/config
     */
    public function testBrowseConfig(Request $request, string $kodeBrowse): JsonResponse
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
                'table' => $config['table'] ?? null,
                'query' => $config['query'] ?? null,
                'joins' => $config['joins'] ?? null,
                'whereExtra' => $config['whereExtra'] ?? null,
                'alias_fields' => $config['alias_fields'] ?? null,
                'parent_filters' => $config['parent_filters'] ?? null,
                'params' => $config['params'] ?? null,
            ],
        ]);
    }

    /**
     * Validate a single code for browse (for testing)
     * GET /api/reports/test/browse/{kodeBrowse}/validate?code={code}
     */
    public function testBrowseValidate(Request $request, string $kodeBrowse): JsonResponse
    {
        $code = $request->query('code', '');

        if (empty($code)) {
            return response()->json([
                'success' => false,
                'message' => 'Code parameter required',
            ], 400);
        }

        $result = $this->browseService->validateCode($kodeBrowse, $code);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Code not found or invalid',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'kodeBrowse' => $kodeBrowse,
                'code' => $code,
                'result' => $result,
            ]
        ]);
    }

    /**
     * Batch validate multiple codes for browse (for testing)
     * POST /api/reports/test/browse/{kodeBrowse}/validate-batch
     * Body: { "codes": ["code1", "code2", ...] }
     */
    public function testBrowseValidateBatch(Request $request, string $kodeBrowse): JsonResponse
    {
        $codes = $request->input('codes', []);

        if (empty($codes)) {
            return response()->json([
                'success' => false,
                'message' => 'Codes array required',
            ], 400);
        }

        $results = $this->browseService->validateBatch($kodeBrowse, $codes);

        return response()->json([
            'success' => true,
            'data' => [
                'kodeBrowse' => $kodeBrowse,
                'requested' => count($codes),
                'found' => count($results),
                'results' => $results,
            ]
        ]);
    }

    /**
     * Get all records for a browse type (for testing checkbox mode)
     * GET /api/reports/test/browse/{kodeBrowse}/all?limit=500&userMode={userMode}
     */
    public function testBrowseAll(Request $request, string $kodeBrowse): JsonResponse
    {
        $limit = (int) $request->query('limit', 500);
        $userMode = $request->query('userMode');

        $results = $this->browseService->getAll($kodeBrowse, $limit, $userMode);

        return response()->json([
            'success' => true,
            'data' => [
                'kodeBrowse' => $kodeBrowse,
                'limit' => $limit,
                'results' => $results,
                'count' => count($results),
            ]
        ], 200, [], JSON_INVALID_UTF8_IGNORE);
    }

    /**
     * Test browse search with auto-resolved parent filter from filter values
     * GET /api/reports/test/browse/{kodeBrowse}/search-with-parent?parent[kodeGrup]=BJ&q=xxx
     *
     * This endpoint simulates how ReportConfig resolves parent filters automatically
     * by looking up parent filter values from the konfigurasi.
     */
    public function testBrowseSearchWithParent(Request $request, string $kodeBrowse): JsonResponse
    {
        $q = $request->query('q', '');
        $limit = (int) $request->query('limit', 20);
        $userMode = $request->query('userMode');
        $parentRaw = $request->query('parent', []);

        // Get the browse config
        $config = $this->browseService->getConfig($kodeBrowse);

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Browse type not found: ' . $kodeBrowse,
            ], 404);
        }

        // Resolve parent filters based on config
        $parentParams = $this->resolveParentParamsFromConfig($config, $parentRaw);

        // Perform search with resolved parent params
        $results = $this->browseService->search($kodeBrowse, $q, $limit, $userMode, $parentParams);

        return response()->json([
            'success' => true,
            'data' => [
                'kodeBrowse' => $kodeBrowse,
                'query' => $q,
                'limit' => $limit,
                'parent_filters_config' => $config['parent_filters'] ?? [],
                'parent_params_resolved' => $parentParams,
                'parent_params_original' => $parentRaw,
                'results' => $results,
                'count' => count($results),
            ]
        ], 200, [], JSON_INVALID_UTF8_IGNORE);
    }

    /**
     * Show report config with full browse integration (including parent filter info)
     * GET /api/reports/test/config/{kodeMenu}
     *
     * This shows how filters with kode_browse include their browse config
     * and how parent filter relationships are resolved.
     */
    public function testReportConfig(Request $request, string $kodeMenu): JsonResponse
    {
        $config = $this->reportService->getReportConfig($kodeMenu);

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Report not found',
            ], 404);
        }

        // Analyze filters with browse integration
        $filtersAnalysis = [];
        foreach ($config['filters'] ?? [] as $filter) {
            $analysis = [
                'nama_filter' => $filter['nama_filter'],
                'kode_browse' => $filter['kode_browse'] ?? null,
                'has_browse_config' => !empty($filter['browse_config']),
                'has_parent_filter' => !empty($filter['parent_filter_ref']),
                'parent_filter_ref' => $filter['parent_filter_ref'] ?? null,
                'parent_filter_config' => $filter['parent_filter_config'] ?? null,
            ];

            if ($filter['browse_config']) {
                $bc = $filter['browse_config'];
                $analysis['browse_config_summary'] = [
                    'keyField' => $bc['keyField'] ?? null,
                    'labelField' => $bc['labelField'] ?? null,
                    'table' => $bc['table'] ?? null,
                    'parent_filters' => $bc['parent_filters'] ?? [],
                ];
            }

            $filtersAnalysis[] = $analysis;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'KODEMENU' => $config['KODEMENU'],
                'nama_laporan' => $config['nama_laporan'],
                'total_filters' => count($config['filters'] ?? []),
                'total_datasets' => count($config['datasets'] ?? []),
                'filters_analysis' => $filtersAnalysis,
                'full_config' => $config,
            ]
        ]);
    }

    /**
     * Helper: resolve parent params from config and incoming parent values
     */
    private function resolveParentParamsFromConfig(array $config, array $parentRaw): array
    {
        $parentParams = [];
        $parentFilters = $config['parent_filters'] ?? [];

        foreach ($parentFilters as $pf) {
            $sourceColumn = $pf['source_column'] ?? null;
            if (!$sourceColumn) {
                continue;
            }

            // Check if the parent value is provided
            if (isset($parentRaw[$sourceColumn])) {
                $parentParams[$sourceColumn] = $parentRaw[$sourceColumn];
            }
        }

        return $parentParams;
    }
}