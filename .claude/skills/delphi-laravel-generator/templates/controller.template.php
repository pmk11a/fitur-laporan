<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\{{ModelName}}Service;{{AdditionalServiceUses}}
use App\Http\Requests\V1\Store{{ModelName}}Request;
use App\Http\Requests\V1\Update{{ModelName}}Request;
use App\Http\Requests\V1\Delete{{ModelName}}Request;
use App\Http\Resources\V1\{{ModelName}}Resource;
use App\Http\Resources\V1\{{ModelName}}Collection;
use App\Models\{{ModelName}};
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * {{ModelName}} Controller
 *
 * Generated from: {{DelphiForm}}
 * RESTful API endpoints for {{ModelNameLower}}
 *
 * ⚠️ GOTCHA: Auth property is USERID (uppercase), NOT userid!
 * Use: $request->user()->USERID
 *
 * @package App\Http\Controllers\Api\V1
 */
class {{ModelName}}Controller extends Controller
{
    public function __construct(
        private {{ModelName}}Service $service{{AdditionalServiceProps}}
    ) {}

    /**
     * Display a listing of {{ModelNameLower}} records
     *
     * From Delphi: GetData procedure, Grid data display
     *
     * @param Request $request
     * @return {{ModelName}}Collection
     */
    public function index(Request $request): {{ModelName}}Collection
    {
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        $query = {{ModelName}}::with({{EagerRelations}})
            ->{{DefaultSort}}
            ->filter($request->all());

        {{SearchLogic}}

        return new {{ModelName}}Collection($query->paginate($perPage, ['*'], 'page', $page));
    }

    /**
     * Store a newly created {{ModelNameLower}} record
     *
     * From Delphi: btTambahClick, btSimpanClick
     *
     * @param Store{{ModelName}}Request $request
     * @return JsonResponse
     */
    public function store(Store{{ModelName}}Request $request): JsonResponse
    {
        try {
            ${{ModelNameLower}} = $this->service->create($request->validated());

            return (new {{ModelName}}Resource(${{ModelNameLower}}))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            Log::error('{{ModelName}} creation failed', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'message' => 'Gagal menyimpan data',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified {{ModelNameLower}} record
     *
     * From Delphi: FormShow, Detail display
     *
     * @param {{ModelName}} ${{ModelNameLower}}
     * @return {{ModelName}}Resource
     */
    public function show({{ModelName}} ${{ModelNameLower}}): {{ModelName}}Resource
    {
        ${{ModelNameLower}}->load({{DetailRelations}});

        return new {{ModelName}}Resource(${{ModelNameLower}});
    }

    /**
     * Update the specified {{ModelNameLower}} record
     *
     * From Delphi: btEditClick, btSimpanClick
     *
     * @param Update{{ModelName}}Request $request
     * @param {{ModelName}} ${{ModelNameLower}}
     * @return {{ModelName}}Resource
     */
    public function update(Update{{ModelName}}Request $request, {{ModelName}} ${{ModelNameLower}}): {{ModelName}}Resource
    {
        try {
            ${{ModelNameLower}} = $this->service->update(${{ModelNameLower}}, $request->validated());

            return new {{ModelName}}Resource(${{ModelNameLower}});
        } catch (\Exception $e) {
            Log::error('{{ModelName}} update failed', [
                'error' => $e->getMessage(),
                'id' => ${{ModelNameLower}}->{{PrimaryKey}}
            ]);

            return response()->json([
                'message' => 'Gagal mengupdate data',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified {{ModelNameLower}} record
     *
     * From Delphi: btHapusClick with CekDelete validation
     *
     * @param {{ModelName}} ${{ModelNameLower}}
     * @return JsonResponse
     */
    public function destroy({{ModelName}} ${{ModelNameLower}}): JsonResponse
    {
        try {
            $this->service->delete(${{ModelNameLower}});

            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('{{ModelName}} deletion failed', [
                'error' => $e->getMessage(),
                'id' => ${{ModelNameLower}}->{{PrimaryKey}}
            ]);

            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    // =========================================================================
    // ADDITIONAL ENDPOINTS
    // =========================================================================

    {{AdditionalEndpoints}}
}

/*
 * TEMPLATE PLACEHOLDERS:
 *
 * {{ModelName}}           - Model class name (e.g., User, Pengajuan)
 * {{ModelNameLower}}      - Model name in lowercase (e.g., user, pengajuan)
 * {{DelphiForm}}          - Source Delphi form name (e.g., FrmPemakai)
 * {{PrimaryKey}}          - Primary key field name
 * {{EagerRelations}}      - Relations to eager load in index
 * {{DetailRelations}}     - Relations to load in show
 * {{DefaultSort}}         - Default ordering clause
 * {{SearchLogic}}         - Custom search/filter logic
 * {{AdditionalEndpoints}} - Additional controller methods
 *
 * MULTI-SERVICE INJECTION (Phase 1.5 - Form Reference Detection):
 *
 * {{AdditionalServiceUses}} - Additional use statements for referenced forms
 *   Example: ",\nuse App\Services\ReportMenuService;"
 *
 * {{AdditionalServiceProps}} - Additional service properties in constructor
 *   Example: ",\n    private ReportMenuService $reportMenuService"
 *
 * Usage when form references detected:
 * 1. Scan for Application.CreateForm(TFrMenuReport, ...)
 * 2. Find FrMenuReport.pas in codebase
 * 3. Extract service name (ReportMenuService) from config/form_patterns.json
 * 4. Generate use statement and constructor property
 */
