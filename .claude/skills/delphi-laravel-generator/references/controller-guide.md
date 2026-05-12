# Controller Generation Guide

Generate Laravel RESTful controllers from Delphi forms.

## Table of Contents

- [Overview](#overview)
- [Controller Template](#controller-template)
- [Delphi Event Handler to Controller Method](#delphi-event-handler-to-controller-method)
- [Route Generation](#route-generation)
- [Example: PengajuanController](#example-pengajuancontroller)
- [API Resources](#api-resources)
- [Eager Loading](#eager-loading)
- [Filtering](#filtering)

---

## Overview

Controllers follow Laravel RESTful conventions with service injection.

## Controller Template

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\{{ModelName}}Service;
use App\Http\Requests\V1\Store{{ModelName}}Request;
use App\Http\Requests\V1\Update{{ModelName}}Request;
use App\Http\Resources\V1\{{ModelName}}Resource;
use App\Http\Resources\V1\{{ModelName}}Collection;
use App\Models\{{ModelName}};
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * {{ModelName}} Controller
 *
 * Generated from: {{DelphiForm}}
 */
class {{ModelName}}Controller extends Controller
{
    public function __construct(
        private {{ModelName}}Service $service
    ) {}

    /**
     * Display a listing of {{ModelNameLower}} records
     */
    public function index(Request $request): {{ModelName}}Collection
    {
        $perPage = $request->input('per_page', 15);

        $query = {{ModelName}}::with({{EagerRelations}})
            ->{{DefaultSort}}
            ->filter($request->all());

        {{SearchLogic}}

        return new {{ModelName}}Collection($query->paginate($perPage));
    }

    /**
     * Store a newly created {{ModelNameLower}} record
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
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Gagal menyimpan data',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified {{ModelNameLower}} record
     */
    public function show({{ModelName}} ${{ModelNameLower}}): {{ModelName}}Resource
    {
        ${{ModelNameLower}}->load({{DetailRelations}});

        return new {{ModelName}}Resource(${{ModelNameLower}});
    }

    /**
     * Update the specified {{ModelNameLower}} record
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
```

## Delphi Event Handler to Controller Method

| Delphi Event | Controller Method | HTTP Method | Route |
|--------------|-------------------|-------------|-------|
| FormShow / GetData | index() | GET | /api/v1/resource |
| btTambahClick / btSimpanClick | store() | POST | /api/v1/resource |
| ShowDetail | show() | GET | /api/v1/resource/{id} |
| btEditClick / btSimpanClick | update() | PUT/PATCH | /api/v1/resource/{id} |
| btHapusClick | destroy() | DELETE | /api/v1/resource/{id} |

## Route Generation

```php
// routes/api.php
Route::apiResource('{{route_name}}', {{ModelName}}Controller::class);

// Or with version prefix:
Route::prefix('v1')->group(function () {
    Route::apiResource('{{route_name}}', {{ModelName}}Controller::class);
});
```

## Example: PengajuanController

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PengajuanService;
use App\Http\Requests\V1\StorePengajuanRequest;
use App\Http\Requests\V1\UpdatePengajuanRequest;
use App\Http\Resources\V1\PengajuanResource;
use App\Models\Pengajuan;

class PengajuanController extends Controller
{
    public function __construct(
        private PengajuanService $service
    ) {}

    public function index(Request $request)
    {
        $pengajuan = Pengajuan::with(['customer', 'produk'])
            ->orderBy('Tanggal', 'desc')
            ->filter($request->all())
            ->paginate();

        return PengajuanResource::collection($pengajuan);
    }

    public function store(StorePengajuanRequest $request)
    {
        $pengajuan = $this->service->create($request->validated());

        return (new PengajuanResource($pengajuan))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Pengajuan $pengajuan)
    {
        $pengajuan->load(['customer', 'produk', 'jaminan', 'angsuran']);

        return new PengajuanResource($pengajuan);
    }

    public function update(UpdatePengajuanRequest $request, Pengajuan $pengajuan)
    {
        $pengajuan = $this->service->update($pengajuan, $request->validated());

        return new PengajuanResource($pengajuan);
    }

    public function destroy(Pengajuan $pengajuan)
    {
        $this->service->delete($pengajuan);

        return response()->json(null, 204);
    }

    // Additional endpoints from Delphi procedures
    public function approve(Request $request, Pengajuan $pengajuan)
    {
        $this->service->approve($pengajuan->NoBukti);

        return new PengajuanResource($pengajuan->fresh());
    }

    public function calculate(Request $request, Pengajuan $pengajuan)
    {
        $angsuran = $pengajuan->calculateAngsuran();

        return response()->json(['angsuran' => $angsuran]);
    }
}
```

## API Resources

Create API Resources for consistent response format:

```php
<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class PengajuanResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'no_bukti' => $this->NoBukti,
            'tanggal' => $this->Tanggal?->format('Y-m-d'),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'produk' => new ProdukResource($this->whenLoaded('produk')),
            'pinjaman' => (float) $this->Pinjaman,
            'tenor' => $this->Tenor,
            'angsuran' => $this->calculateAngsuran(),
            'status' => $this->Status,
        ];
    }
}
```

## Eager Loading

Always eager load relationships:

```php
// In index()
Pengajuan::with(['customer', 'produk'])->paginate();

// In show()
$pengajuan->load(['customer', 'produk', 'jaminan', 'angsuran']);
```

## Filtering

Use query scopes for filtering:

```php
// In Model
public function scopeFilter($query, array $filters)
{
    if (isset($filters['status'])) {
        $query->where('Status', $filters['status']);
    }

    if (isset($filters['start_date'])) {
        $query->where('Tanggal', '>=', $filters['start_date']);
    }

    return $query;
}

// In Controller
Pengajuan::filter($request->all())->paginate();
```

## See Also

- `service-guide.md` - Service layer
- `validation-guide.md` - Request validation
