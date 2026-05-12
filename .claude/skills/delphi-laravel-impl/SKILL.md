---
name: delphi-laravel-impl
description: ⚠️ MANDATORY untuk migrasi modul Keu-app ERP. Trigger: "implementasi", "migrasi", "debug", "fitur baru".
compatibility: Requires access to D:\ykka\Keu-app\ (be-keu, fe-keu, docs/traceability)
---

# Keu-app Module Implementation Skill

> ⚠️ **SINGLE ENTRY POINT FOR MIGRATION**
> Semua migrasi modul = `/delphi-laravel-impl`
> Untuk scan Delphi = `/skill delphi-traceability-analyzer`
> Untuk reference patterns = `.claude/skills/delphi-laravel-impl/patterns/`

> Catatan: Skill ini originally dari KSP project. KSP-specific references disimpan di folder `references/` sebagai referensi.

## ⚡⚡⚡ CRITICAL WORKFLOW ⚡⚡⚡

**JANGAN-coding sebelum checklist selesai!**

### SEBELUM MULAI (WAJIB)
```
1. /delphi-laravel-impl   ← Invoke skill ini
2. Baca verification-checklist.md (if exists)
3. Run: python .claude/skills/delphi-traceability-analyzer/scripts/scan_delphi.py <file.pas>
4. Buat: memory/modules/<modulename>.md (status: DALAM PROGRESS)
```

### CHECKLIST
- [ ] Traceability JSON ada (`docs/traceability/*.json`)
- [ ] `total_functions > 0` di JSON
- [ ] Memory file dibuat
- [ ] Semua function categories mapped (event_handler, utility, database)

### SEBELUM SELESAI
- [ ] Semua `missing_features` implemented ATAU ada justification
- [ ] Memory file status = COMPLETE

---

## Common Gotchas (SELALU Baca!)

**READ: `.claude/memory/feedback/migration-patterns.md` sebelum mulai!**

Quick reference:
1. **Column names UPPERCASE** - SQL Server pakai `USERID`, bukan `userid`
2. **Auth: USERID** - `$request->user()->USERID`, bukan `->userid`
3. **noBukti slash** - Route pakai `->where('param', '.*')`, frontend pakai `encodeURIComponent()`
4. **Direct Import** - For DynamicPage use direct import
5. **Hook: res.data** - Not `res.data.data`
6. **DFM ComboBox** - Items di .dfm, bukan .pas

---

## Quick Reference

### Scan Delphi
```bash
cd D:\ykka\Keu-app
python .claude/skills/delphi-traceability-analyzer/scripts/scan_delphi.py \
  pwt/Path/To/FrmModuleName.pas \
  --output docs/traceability/frmmodulename.json
```

### Generate Complete Module
```
/delphi-laravel-impl FrmModuleName
```
→ Output: Backend (Model + Service + Controller + Request + Route) + Frontend (Service + Hook + Page)

### File Structure
```
Backend: be-keu/app/Services/{Module}Service.php
         be-keu/app/Http/Controllers/Api/V1/{Module}Controller.php
         be-keu/app/Http/Requests/V1/{Module}Request.php
         be-keu/app/Models/{Module}.php

Frontend: fe-keu/src/features/{category}/{module}/Page.tsx
          fe-keu/src/hooks/use{Module}.ts
          fe-keu/src/services/{module}Service.ts

Templates: .claude/skills/delphi-laravel-impl/patterns/
```

### Traceability Schema
```json
{
  "module": "FrmXxx",
  "functions": [
    {
      "name": "BitBtn1Click",
      "category": "event_handler",
      "business_logic": "...",
      "laravel_target": { "file": "...", "method": "...", "status": "pending" }
    }
  ]
}
```

---

## Complete Workflow

### Phase 1: UI Mapping
1. Baca traceability JSON - extract event handlers
2. Buat mapping table: Element → Event → Line → Status
3. Baca .dfm untuk ComboBox items (bukan di .pas!)

### Phase 2: Backend Generation
Pattern ada di `patterns/`:
- `backend-controller.php` → Controller dengan USERID handling
- `backend-service.php` → Service dengan UPPERCASE columns
- `backend-request.php` → Request validation

**Wire all templates:**
1. Generate Model (if not exists)
2. Generate Service
3. Generate Controller (inject Service)
4. Generate Request (validation rules)
5. Register route in `routes/api.php`

### Phase 3: Frontend Generation
Pattern ada di `patterns/`:
- `frontend-page.tsx` → Single form page
- `frontend-list-page.tsx` → Grid page with filters
- `frontend-hook.ts` → TanStack Query hooks
- `frontend-service.ts` → API service

**Frontend structure:**
```
fe-keu/src/
├── services/{module}Service.ts      # API calls
├── hooks/use{Module}.ts             # TanStack Query hooks
├── features/{category}/{module}/
│   └── {Module}Page.tsx            # Main page
└── types/{module}.ts               # TypeScript interfaces
```

### Phase 4: Route Registration
```php
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::apiResource('module', ModuleController::class);
    // atau dengan parameter custom:
    Route::get('module', [ModuleController::class, 'index']);
    Route::post('module', [ModuleController::class, 'store']);
    Route::get('module/{module}', [ModuleController::class, 'show'])
        ->where('module', '.*');
    Route::put('module/{module}', [ModuleController::class, 'update'])
        ->where('module', '.*');
    Route::delete('module/{module}', [ModuleController::class, 'destroy'])
        ->where('module', '.*');
});
```

### Phase 5: Verification
- [ ] PHP syntax valid (`php -l`)
- [ ] Routes registered (`php artisan route:list`)
- [ ] Column names UPPERCASE
- [ ] USERID handling correct
- [ ] Frontend res.data correct
- [ ] Folder matches ROUTE

---

## Templates (Wire ALL, no partial)

### Backend: Model Template
```php
// be-keu/app/Models/{Module}.php
class {Module} extends Model
{
    protected $table = 'TABLE_NAME';      // UPPERCASE
    protected $primaryKey = 'PK_NAME';   // UPPERCASE
    protected $fillable = [...];
    protected $casts = [...];
}
```

### Backend: Service Template
```php
// be-keu/app/Services/{Module}Service.php
class {Module}Service
{
    public function getData(string $userId): ?array
    {
        // ⚠️ Column UPPERCASE
        return DB::table('TABLE_NAME')
            ->where('UserID', $userId)
            ->first();
    }
}
```

### Backend: Controller Template
```php
// be-keu/app/Http/Controllers/Api/V1/{Module}Controller.php
class {Module}Controller extends Controller
{
    public function __construct(protected {Module}Service $service) {}

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->USERID;  // ⚠️ UPPERCASE
        $data = $this->service->getData($userId);
        return response()->json(['success' => true, 'data' => $data]);
    }
}
```

### Frontend: Service Template
```typescript
// fe-keu/src/services/{module}Service.ts
class {Module}Service {
  async getData(): Promise<ModuleResponse> {
    const response = await api.get('/api/v1/{module}');
    return response.data;  // ⚠️ res.data, not res.data.data
  }
}
```

### Frontend: Hook Template
```typescript
// fe-keu/src/hooks/use{Module}.ts
export const use{Module} = () => {
  return useQuery({
    queryKey: ['{module}'],
    queryFn: () => {module}Service.get{Module}(),
  });
};
```

### Frontend: Page Template
```typescript
// fe-keu/src/features/{category}/{module}/{Module}Page.tsx
export function {Module}Page() {
  const { data } = use{Module}();
  // ⚠️ Use data directly, not data?.data
  // Handle with: return res?.data ?? {}
}
```

## Report Setup (Fluffy Bee - Dynamic Report Engine)

### Trigger
```
Buatkan report [Nama] dengan kode [KODEMENU]
```

### Pre-Setup Checklist (WAJIB)
```
[ ] 1. Baca Delphi case [code] di FrmReportPreview.pas
[ ] 2. Baca Form.dfm untuk columns (field names + computed fields)
[ ] 3. Baca .fr3 untuk layout
[ ] 4. Tanya user: KODEMENU berapa? (cek existing di menu.ts)
[ ] 5. Cek stored procedures kalau multi-dataset
[ ] 6. Buat SQL
[ ] 7. Tanya: Execute sekarang?
[ ] 8. Test: http://localhost:3000/reports/{KODEMENU}
```

### Pattern
```
patterns/report-setup.md
```

---

## ⚡ IMMEDIATE: Error >15min → Tambah ke memory

**Jika error baru >15 menit debug → tanya:**
> "Error ini pattern yang bisa ke depan. Mau saya tambahkan ke migration-patterns.md?"

---

## Feedback Loop

Setiap selesai modul:
1. Ada error baru di gotchas? → Update `migration-patterns.md`
2. Pattern baru di templates? → Update patterns files
3. Workflow perlu perbaikan? → Update SKILL.md
4. Update `context-store/migration_status.json` dengan progress

---

## Memory Integration

**READ on start:**
- `.claude/memory/feedback/migration-patterns.md` - Critical gotchas

**SAVE on completion:**
- New patterns → `.claude/memory/feedback/migration-patterns.md`
- Progress → `.claude/context-store/migration_status.json`

**AUTO-PRUNE:** Monthly review untuk hapus duplicate/obsolete patterns.