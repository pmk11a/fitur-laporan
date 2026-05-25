<?php

namespace App\Http\Controllers;

use App\Services\AdminReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminReportController extends Controller
{
    public function __construct(protected AdminReportService $service)
    {
    }

    // ============================================================
    // REPORTS
    // ============================================================

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->getAllReports()
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateReport($request);
        $result = $this->service->createReport($data);

        return response()->json(['success' => true, 'data' => $result], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $report = $this->service->getReport($id);

        if (!$report) {
            return response()->json(['success' => false, 'message' => 'Report not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $report]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->only(['nama_laporan', 'deskripsi', 'status_aktif', 'footer_bands']);
        $this->service->updateReport($id, $data);

        return response()->json(['success' => true, 'message' => 'Report updated']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->deleteReport($id);

        return response()->json(['success' => true, 'message' => 'Report deleted']);
    }

    public function availableKodeMenu(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->getAvailableKodeMenu()
        ]);
    }

    private function validateReport(Request $request): array
    {
        return $request->validate([
            'KODEMENU' => 'required|string|max:20',
            'nama_laporan' => 'required|string|max:200',
            'deskripsi' => 'nullable|string|max:500',
            'status_aktif' => 'nullable|boolean',
            'footer_bands' => 'nullable',
        ]);
    }

    // ============================================================
    // FILTERS
    // ============================================================

    public function listFilters(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->getFilters($id)
        ]);
    }

    public function storeFilter(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'nama_filter' => 'required|string|max:100',
            'label' => 'nullable|string|max:100',
            'tipe_input' => 'required|string|in:date,text,number,combobox,browse,perkiraan,dropdown,checkbox',
            'wajib_isi' => 'nullable|boolean',
            'nilai_default' => 'nullable|string|max:200',
            'posisi' => 'nullable|integer',
            'konfigurasi' => 'nullable',
        ]);

        $result = $this->service->createFilter($id, $data);

        return response()->json(['success' => true, 'data' => $result], 201);
    }

    public function updateFilter(Request $request, int $id, int $fid): JsonResponse
    {
        $data = $request->validate([
            'nama_filter' => 'nullable|string|max:100',
            'label' => 'nullable|string|max:100',
            'tipe_input' => 'nullable|string|in:date,text,number,combobox,browse,perkiraan,dropdown,checkbox',
            'wajib_isi' => 'nullable|boolean',
            'nilai_default' => 'nullable|string|max:200',
            'posisi' => 'nullable|integer',
            'konfigurasi' => 'nullable',
        ]);

        $this->service->updateFilter($fid, $data);

        return response()->json(['success' => true, 'message' => 'Filter updated']);
    }

    public function destroyFilter(Request $request, int $id, int $fid): JsonResponse
    {
        $this->service->deleteFilter($fid);

        return response()->json(['success' => true, 'message' => 'Filter deleted']);
    }

    public function reorderFilters(Request $request, int $id): JsonResponse
    {
        $orders = $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|integer',
            'orders.*.posisi' => 'required|integer',
        ]);

        $this->service->reorderFilters($id, $orders['orders']);

        return response()->json(['success' => true, 'message' => 'Filters reordered']);
    }

    // ============================================================
    // DATASETS
    // ============================================================

    public function listDatasets(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->getDatasets($id)
        ]);
    }

    public function storeDataset(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'nama_dataset' => 'required|string|max:50',
            'query_sumber_data' => 'required|string',
            'deskripsi' => 'nullable|string|max:200',
            'urutan' => 'nullable|integer',
            'config_json' => 'nullable|array',
            'config_json.display_role' => 'nullable|string|in:summary,detail',
            'config_json.summary_layout' => 'nullable|string|in:grid_2col,grid_1col',
        ]);

        $result = $this->service->createDataset($id, $data);

        return response()->json(['success' => true, 'data' => $result], 201);
    }

    public function updateDataset(Request $request, int $id, int $did): JsonResponse
    {
        $data = $request->validate([
            'nama_dataset' => 'nullable|string|max:50',
            'query_sumber_data' => 'nullable|string',
            'deskripsi' => 'nullable|string|max:200',
            'urutan' => 'nullable|integer',
            'config_json' => 'nullable|array',
            'config_json.display_role' => 'nullable|string|in:summary,detail',
            'config_json.summary_layout' => 'nullable|string|in:grid_2col,grid_1col',
        ]);

        $this->service->updateDataset($did, $data);

        return response()->json(['success' => true, 'message' => 'Dataset updated']);
    }

    public function destroyDataset(Request $request, int $id, int $did): JsonResponse
    {
        $this->service->deleteDataset($did);

        return response()->json(['success' => true, 'message' => 'Dataset deleted']);
    }

    public function previewDataset(Request $request, int $id): JsonResponse
    {
        $sql = $request->input('query_sumber_data', '');
        $filters = $request->input('filters', []);
        $result = $this->service->previewQuery($sql, $filters);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 400);
        }

        return response()->json(['success' => true, 'data' => $result]);
    }

    // ============================================================
    // COLUMNS
    // ============================================================

    public function listColumns(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->getAllColumns($id)
        ]);
    }

    public function storeColumn(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'nama_dataset' => 'required|string|max:50',
            'nama_kolom' => 'required|string|max:100',
            'label_tampil' => 'nullable|string|max:100',
            'urutan_tampil' => 'nullable|integer',
            'format_type' => 'nullable|string|in:text,date,number,currency',
            'alignment' => 'nullable|string|in:left,center,right',
            'is_summable' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
        ]);

        $result = $this->service->createColumn($id, $data);

        return response()->json(['success' => true, 'data' => $result], 201);
    }

    public function updateColumn(Request $request, int $id, int $cid): JsonResponse
    {
        $data = $request->validate([
            'nama_dataset' => 'nullable|string|max:50',
            'nama_kolom' => 'nullable|string|max:100',
            'label_tampil' => 'nullable|string|max:100',
            'urutan_tampil' => 'nullable|integer',
            'format_type' => 'nullable|string|in:text,date,number,currency',
            'alignment' => 'nullable|string|in:left,center,right',
            'is_summable' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
        ]);

        $this->service->updateColumn($cid, $data);

        return response()->json(['success' => true, 'message' => 'Column updated']);
    }

    public function destroyColumn(Request $request, int $id, int $cid): JsonResponse
    {
        $this->service->deleteColumn($cid);

        return response()->json(['success' => true, 'message' => 'Column deleted']);
    }

    // ============================================================
    // GROUPING
    // ============================================================

    public function listGroups(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->getGroups($id)
        ]);
    }

    public function storeGroup(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'group_level' => 'required|integer|min:1|max:5',
            'group_field' => 'nullable|string|max:100',
            'field_value' => 'nullable|string|max:50',
            'label' => 'required|string|max:200',
            'sort_order' => 'nullable|integer',
            'show_subtotal' => 'nullable|boolean',
            'style_config' => 'nullable',
            'special_handling' => 'nullable|string|in:default,running-balance,category-label',
            'config_json' => 'nullable',
        ]);

        $result = $this->service->createGroup($id, $data);

        return response()->json(['success' => true, 'data' => $result], 201);
    }

    public function updateGroup(Request $request, int $id, int $gid): JsonResponse
    {
        $data = $request->validate([
            'group_level' => 'nullable|integer|min:1|max:5',
            'group_field' => 'nullable|string|max:100',
            'field_value' => 'nullable|string|max:50',
            'label' => 'nullable|string|max:200',
            'sort_order' => 'nullable|integer',
            'show_subtotal' => 'nullable|boolean',
            'style_config' => 'nullable',
            'special_handling' => 'nullable|string|in:default,running-balance,category-label',
            'config_json' => 'nullable',
        ]);

        $this->service->updateGroup($gid, $data);

        return response()->json(['success' => true, 'message' => 'Group updated']);
    }

    public function destroyGroup(Request $request, int $id, int $gid): JsonResponse
    {
        $this->service->deleteGroup($gid);

        return response()->json(['success' => true, 'message' => 'Group deleted']);
    }

    // ============================================================
    // MENU ITEMS
    // ============================================================

    public function listMenuItems(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->getMenuItems()
        ]);
    }

    public function updateMenuItem(Request $request, string $kodeMenu): JsonResponse
    {
        $data = $request->only(['Keterangan', 'L0', 'icon', 'OL']);
        $this->service->updateMenuItem($kodeMenu, $data);

        return response()->json(['success' => true, 'message' => 'Menu item updated']);
    }

    // ============================================================
    // USER ACCESS
    // ============================================================

    public function listAccess(Request $request, int $id): JsonResponse
    {
        $report = \Illuminate\Support\Facades\DB::connection('sqlsrv')
            ->table('dbmasterlaporan')->where('id_laporan', $id)->first();

        if (!$report) {
            return response()->json(['success' => false, 'message' => 'Report not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->service->getUserAccess($report->KODEMENU)
        ]);
    }

    public function grantAccess(Request $request, int $id): JsonResponse
    {
        $report = \Illuminate\Support\Facades\DB::connection('sqlsrv')
            ->table('dbmasterlaporan')->where('id_laporan', $id)->first(['KODEMENU']);

        if (!$report) {
            return response()->json(['success' => false, 'message' => 'Report not found'], 404);
        }

        $data = $request->validate([
            'USERID' => 'required|string|max:50',
            'Access' => 'nullable|boolean',
            'IsDesign' => 'nullable|boolean',
            'IsExport' => 'nullable|boolean',
        ]);

        $access = $this->service->grantAccess($report->KODEMENU, $data);

        return response()->json(['success' => true, 'data' => $access], 201);
    }

    public function revokeAccess(Request $request, int $id, string $userId): JsonResponse
    {
        $report = \Illuminate\Support\Facades\DB::connection('sqlsrv')
            ->table('dbmasterlaporan')->where('id_laporan', $id)->first(['KODEMENU']);

        if (!$report) {
            return response()->json(['success' => false, 'message' => 'Report not found'], 404);
        }

        $this->service->revokeAccess($report->KODEMENU, $userId);

        return response()->json(['success' => true, 'message' => 'Access revoked']);
    }

    public function listUsers(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->getAllUsers()
        ]);
    }
}