<?php

namespace App\Http\Controllers;

use App\Services\BrowseService;
use App\Services\GenericBrowseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminBrowseController extends Controller
{
    public function __construct(
        protected BrowseService $browseService,
        protected GenericBrowseService $genericBrowseService
    ) {
    }

    /**
     * List all browse configs (both hardcoded and database)
     * GET /api/admin/browse
     */
    public function index(Request $request): JsonResponse
    {
        $types = $this->browseService->types();

        return response()->json([
            'success' => true,
            'data' => [
                'configs' => $types,
                'total' => count($types),
                'summary' => [
                    'hardcoded' => count(array_filter($types, fn($t) => ($t['source'] ?? '') === 'hardcoded')),
                    'database' => count(array_filter($types, fn($t) => ($t['source'] ?? '') === 'database')),
                ]
            ]
        ]);
    }

    /**
     * Flat browse list with group classification.
     * GET /api/admin/browse/list
     * Used by Report Filters tab to populate the "Browse Type" dropdown.
     */
    public function list(): JsonResponse
    {
        $groupMap = self::getGroupMap();

        $types = $this->browseService->types();

        $results = [];
        foreach ($types as $t) {
            $code = $t['kodeBrowse'];
            $results[] = [
                'kodeBrowse' => $code,
                'group'      => $this->classifyGroup($code, $groupMap),
                'source'     => $t['source'],
            ];
        }

        // Sort by group then code
        usort($results, function ($a, $b) {
            $g = $a['group'] <=> $b['group'];
            return $g !== 0 ? $g : $a['kodeBrowse'] <=> $b['kodeBrowse'];
        });

        return response()->json([
            'success' => true,
            'data'    => $results,
        ]);
    }

    private static function getGroupMap(): array
    {
        return [
            'Perkiraan'     => ['1005', '10051', '10053', '10054', '100444'],
            'Barang'        => ['911', '912', '913', '915', '917', '120302', '3001101'],
            'Gudang'        => ['916', '11002', '11009'],
            'Supplier'      => ['10141'],
            'Customer'      => ['10142'],
            'Expedisi'      => ['10143'],
            'Cust/Supp'     => ['1014'],
            'Lainnya'       => ['1004', '1002', '1003', '157', '1006', '1008', '91117'],
        ];
    }

    private function classifyGroup(string $code, array $groupMap): string
    {
        foreach ($groupMap as $groupName => $codes) {
            if (in_array($code, $codes, true)) {
                return $groupName;
            }
        }
        return 'Lainnya';
    }

    /**
     * Get a single browse config detail
     * GET /api/admin/browse/{kodeBrowse}
     */
    public function show(Request $request, string $kodeBrowse): JsonResponse
    {
        $config = $this->browseService->getConfig($kodeBrowse);

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Browse config not found: ' . $kodeBrowse,
            ], 404);
        }

        // Check if this is from database or hardcoded
        $dbConfig = $this->genericBrowseService->find($kodeBrowse);
        $source = $dbConfig ? 'database' : 'hardcoded';

        return response()->json([
            'success' => true,
            'data' => [
                'kodeBrowse' => $kodeBrowse,
                'source' => $source,
                'config' => $config,
                'can_edit' => $source === 'database',
                'can_delete' => $source === 'database',
            ]
        ]);
    }

    /**
     * Create a new browse config (database)
     * POST /api/admin/browse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kodeBrowse' => 'required|string|max:50',
            'table' => 'nullable|string|max:255',
            'keyField' => 'required_without:query|string|max:255',
            'labelField' => 'required_without:query|string|max:255',
            'query' => 'nullable|string',
            'additionalFields' => 'nullable|array',
            'additionalFields.*' => 'string',
            'joins' => 'nullable|array',
            'joins.*' => 'string',
            'whereExtra' => 'nullable|string|max:1000',
            'alias_fields' => 'nullable|array',
            'parent_filters' => 'nullable|array',
            'params' => 'nullable|array',
            'isactive' => 'nullable|boolean',
        ]);

        // Check if already exists in DB
        if ($this->genericBrowseService->find($validated['kodeBrowse'])) {
            return response()->json([
                'success' => false,
                'message' => 'Browse config already exists. Use PUT to update.',
            ], 409);
        }

        $this->genericBrowseService->upsert($validated['kodeBrowse'], $validated);

        $config = $this->browseService->getConfig($validated['kodeBrowse']);

        return response()->json([
            'success' => true,
            'message' => 'Browse config created successfully',
            'data' => [
                'kodeBrowse' => $validated['kodeBrowse'],
                'source' => 'database',
                'config' => $config,
            ]
        ], 201);
    }

    /**
     * Update a browse config (database only)
     * PUT /api/admin/browse/{kodeBrowse}
     */
    public function update(Request $request, string $kodeBrowse): JsonResponse
    {
        if (!$this->genericBrowseService->find($kodeBrowse)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update: Browse config is hardcoded. Clone it first to database.',
            ], 422);
        }

        $validated = $request->validate([
            'table' => 'nullable|string|max:255',
            'keyField' => 'nullable|string|max:255',
            'labelField' => 'nullable|string|max:255',
            'query' => 'nullable|string',
            'additionalFields' => 'nullable|array',
            'additionalFields.*' => 'string',
            'joins' => 'nullable|array',
            'joins.*' => 'string',
            'whereExtra' => 'nullable|string|max:1000',
            'alias_fields' => 'nullable|array',
            'parent_filters' => 'nullable|array',
            'params' => 'nullable|array',
            'isactive' => 'nullable|boolean',
        ]);

        $this->genericBrowseService->upsert($kodeBrowse, $validated);

        $config = $this->browseService->getConfig($kodeBrowse);

        return response()->json([
            'success' => true,
            'message' => 'Browse config updated successfully',
            'data' => [
                'kodeBrowse' => $kodeBrowse,
                'source' => 'database',
                'config' => $config,
            ]
        ]);
    }

    /**
     * Delete a browse config (database only)
     * DELETE /api/admin/browse/{kodeBrowse}
     */
    public function destroy(Request $request, string $kodeBrowse): JsonResponse
    {
        if (!$this->genericBrowseService->find($kodeBrowse)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete: Browse config not found in database (may be hardcoded).',
            ], 404);
        }

        $this->genericBrowseService->deactivate($kodeBrowse);

        return response()->json([
            'success' => true,
            'message' => 'Browse config deactivated successfully',
        ]);
    }

    /**
     * Clone a hardcoded config to database for editing
     * POST /api/admin/browse/{kodeBrowse}/clone
     */
    public function clone(Request $request, string $kodeBrowse): JsonResponse
    {
        $config = $this->browseService->getConfig($kodeBrowse);

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Browse config not found: ' . $kodeBrowse,
            ], 404);
        }

        if ($this->genericBrowseService->find($kodeBrowse)) {
            return response()->json([
                'success' => false,
                'message' => 'Browse config already exists in database.',
            ], 409);
        }

        $this->genericBrowseService->upsert($kodeBrowse, $config);

        return response()->json([
            'success' => true,
            'message' => 'Browse config cloned to database successfully',
            'data' => [
                'kodeBrowse' => $kodeBrowse,
                'source' => 'database',
                'config' => $this->browseService->getConfig($kodeBrowse),
            ]
        ]);
    }

    /**
     * Sync all hardcoded configs to database
     * POST /api/admin/browse/sync
     */
    public function sync(Request $request): JsonResponse
    {
        $mode = $request->input('mode', 'all'); // 'all' or 'missing'

        $map = BrowseService::getConfigMap();
        $results = [
            'total' => count($map),
            'created' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        foreach ($map as $kodeBrowse => $config) {
            try {
                $existing = $this->genericBrowseService->find($kodeBrowse);

                if ($mode === 'missing' && $existing) {
                    $results['skipped']++;
                    continue;
                }

                $this->genericBrowseService->upsert($kodeBrowse, $config);
                $results['created']++;
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'kodeBrowse' => $kodeBrowse,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Sync completed: {$results['created']} created, {$results['skipped']} skipped",
            'data' => $results,
        ]);
    }

    /**
     * Get available tables for browse (helper for creating new configs)
     * GET /api/admin/browse/tables
     */
    public function tables(Request $request): JsonResponse
    {
        $search = $request->query('search', '');
        $limit = (int) $request->query('limit', 50);

        try {
            $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
            $params = [];

            if ($search) {
                $sql .= " AND TABLE_NAME LIKE :search";
                $params['search'] = "%{$search}%";
            }

            $sql .= " ORDER BY TABLE_NAME";

            $tables = \DB::connection('sqlsrv')->select($sql, $params);

            return response()->json([
                'success' => true,
                'data' => [
                    'tables' => array_map(fn($t) => $t->TABLE_NAME, $tables),
                    'total' => count($tables),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tables: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get columns for a table (helper for creating new configs)
     * GET /api/admin/browse/tables/{tableName}/columns
     */
    public function columns(Request $request, string $tableName): JsonResponse
    {
        try {
            $sql = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_NAME = :table
                    ORDER BY ORDINAL_POSITION";

            $columns = \DB::connection('sqlsrv')->select($sql, ['table' => $tableName]);

            return response()->json([
                'success' => true,
                'data' => [
                    'table' => $tableName,
                    'columns' => array_map(fn($c) => [
                        'name' => $c->COLUMN_NAME,
                        'type' => $c->DATA_TYPE,
                        'nullable' => $c->IS_NULLABLE === 'YES',
                    ], $columns),
                    'total' => count($columns),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch columns: ' . $e->getMessage(),
            ], 500);
        }
    }
}
