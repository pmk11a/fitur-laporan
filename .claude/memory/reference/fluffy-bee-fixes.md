---
name: fluffy-bee-fixes
description: All fixes and patterns learned from Kas Harian (020101) report
type: reference
---

# Fluffy Bee - Fixes & Patterns

## 1. Database-Driven Footer Bands (footer_bands JSON)

### Table Schema
```sql
ALTER TABLE dbmasterlaporan ADD footer_bands NVARCHAR(MAX) NULL;
```

### JSON Schema
```json
{
  "bands": {
    "title": {"enabled": true, "content": "LAPORAN KAS", "align": "center"},
    "pageHeader": {"enabled": true, "content": "Kas Harian"},
    "summary": {
      "enabled": true,
      "layout": {"columns": 3, "alignment": "spread"},
      "signatures": [
        {"label": "Kontrol", "position": "left"},
        {"label": "Kasir", "position": "center"},
        {"label": "Pimpinan", "position": "right"}
      ]
    }
  }
}
```

### Backend Files
- `be-fitur/app/Models/MasterLaporan.php` - add to fillable + cast:
```php
protected $fillable = [..., 'footer_bands'];
protected $casts = [..., 'footer_bands' => 'array'];
```

- `be-fitur/app/Services/ReportService.php` - parse + include:
```php
private function parseFooterBands(?string $json): ?array
{
    if (empty($json)) return null;
    try {
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : null;
    } catch (\Exception $e) { return null; }
}
```

## 2. User Default Period (dbperiode)

### Backend
```php
// ReportService.php
public function getUserDefaultPeriod(string $userId): ?array
{
    $period = DB::connection('sqlsrv')->selectOne(
        "SELECT BULAN, TAHUN FROM dbperiode WHERE USERID = ?",
        [$userId]
    );
    if ($period) {
        $bulan = (int) $period->BULAN;
        $tahun = (int) $period->TAHUN;
        $firstDay = sprintf('%04d-%02d-01', $tahun, $bulan);
        $lastDay = date('Y-m-t', strtotime($firstDay));
        return ['bulan' => $bulan, 'tahun' => $tahun, 'tglAwal' => $firstDay, 'tglAkhir' => $lastDay];
    }
    return null;
}
```

### Controller
```php
// ReportController.php - show method
$userId = $request->user()->USERID ?? $request->input('userId', '');
$defaultPeriod = $this->reportService->getUserDefaultPeriod($userId);
$config['defaultPeriod'] = $defaultPeriod;
```

### Frontend Store
```typescript
// reports.ts
interface ReportState {
  defaultPeriod: { bulan: number; tahun: number; tglAwal: string; tglAkhir: string } | null
}

// In fetchReport:
const userId = authStore.user?.userId || ''
const url = `${config.public.apiBase}/reports/${kodeMenu}${userId ? `?userId=${encodeURIComponent(userId)}` : ''}`
```

### Frontend Page
```javascript
// [kode].vue - initialize filter values
watch(() => reportStore.currentReport, () => {
  if (reportStore.defaultPeriod) {
    dynamicFilterValues.value = {
      TglAwal: reportStore.defaultPeriod.tglAwal,
      TglAkhir: reportStore.defaultPeriod.tglAkhir,
      // ...
    }
  }
})
```

## 3. SP Parameter Substitution

Laravel tidak bisa pass params ke SP dengan `?` placeholder. Gunakan string substitution:

```php
// ReportService.php - executeQuery
foreach ($filters as $key => $value) {
    $placeholder = '@' . $key;
    if (str_contains($sql, $placeholder)) {
        $sql = str_replace($placeholder, "'" . addslashes($value) . "'", $sql);
    }
}
$results = DB::connection('sqlsrv')->select($sql);
```

## 4. Column Name Case Sensitivity

SP returns column names dengan case berbeda dari .fr3:
- SP: `Debet`, `Debet2`
- .fr3: `debet`, `debet2`

Update `dbkolomlaporan` sesuai SP output:
```sql
UPDATE dbkolomlaporan SET nama_kolom = 'Debet' WHERE ... AND label_tampil = 'Penerimaan (TUNAI)';
UPDATE dbkolomlaporan SET nama_kolom = 'Debet2' WHERE ... AND label_tampil = 'Penerimaan (CH/GB)';
```

## 7. Multi-Dataset Table Display (T2 Fix)

For multi-dataset reports like Kas Harian (020101):
- T1 = Summary/footer data (1 row)
- T2 = Detail/transactions (actual table rows)

**Problem:** Table uses `reportData` which = T1 (1 row), not T2.

**Solution:** Update `[kode].vue` to use T2 for display:

```vue
<!-- Table body - use T2 -->
<tr v-for="(row, rowIdx) in (reportStore.datasets['T2'] || reportStore.reportData || []).slice(0, 100)">

<!-- Records count -->
{{ (reportStore.datasets['T2'] || reportStore.reportData || []).length }} records found
```

```typescript
// reportHeaders computed - check T2 first
const reportHeaders = computed(() => {
  if (reportStore.currentReport?.columns?.['T2']) {
    const cols = reportStore.currentReport.columns['T2']
    return cols.filter(c => c.is_visible !== false).map(c => c.nama_kolom)
  }
  // Fallback for single-dataset reports
  if (reportStore.currentReport?.columns) {
    const mainDataset = reportStore.currentReport.datasets?.[0]?.nama_dataset || 'Daftar Perkiraan'
    const cols = reportStore.currentReport.columns[mainDataset] || []
    return cols.filter(c => c.is_visible !== false).map(c => c.nama_kolom)
  }
  return Object.keys(reportStore.reportData?.[0] || {})
})

// columnLabels computed - same pattern
const columnLabels = computed(() => {
  if (reportStore.currentReport?.columns?.['T2']) {
    return reportStore.currentReport.columns['T2']
      .filter(c => c.is_visible !== false)
      .map(c => c.label_tampil || c.nama_kolom)
  }
  return reportHeaders.value
})
```

## 8. T1 Calculated Fields (from .fr3 Script)

SP returns raw data, but calculated fields from .fr3 FastReport script are computed in report, NOT by SP.

For Kas Harian (020101), T1 Summary needs calculated fields computed from T2 transactions:

```typescript
// t1SummaryData computed property in [kode].vue
const t1SummaryData = computed(() => {
  const t1 = reportStore.datasets['T1']
  const t2 = reportStore.datasets['T2']
  if (!t1 || t1.length === 0) return null

  const data = { ...t1[0] }

  if (t2 && t2.length > 0) {
    // Sum all transaction columns
    const sumDebet = t2.reduce((sum: number, row: any) => sum + parseFloat(row.Debet || 0), 0)
    const sumKredit = t2.reduce((sum: number, row: any) => sum + parseFloat(row.kredit || 0), 0)
    const sumDebet2 = t2.reduce((sum: number, row: any) => sum + parseFloat(row.debet2 || 0), 0)
    const sumKredit2 = t2.reduce((sum: number, row: any) => sum + parseFloat(row.kredit2 || 0), 0)

    // Base values from T1 SP
    const saldoAwal = parseFloat(data.SaldoAwal || 0)
    const saldoAwalD = parseFloat(data.SaldoAwalD || 0)
    const saldoAwalK = parseFloat(data.SaldoAwalK || 0)

    // SaldoAkhirD = SaldoAwal + SUM(debet) + SUM(debet2) - SUM(kredit) - SUM(kredit2)
    data.SaldoAkhirD = saldoAwal + sumDebet + sumDebet2 - sumKredit - sumKredit2

    // SaldoAkhirK = SaldoAwal + SUM(debet2) - SUM(kredit2)
    data.SaldoAkhirK = saldoAwal + sumDebet2 - sumKredit2

    // TotalD = SUM(debet) + SaldoAwalD + SaldoAkhirD
    data.TotalD = sumDebet + saldoAwalD + data.SaldoAkhirD

    // TotalK = SUM(kredit) + SaldoAwalK + SaldoAkhirK
    data.TotalK = sumKredit + saldoAwalK + data.SaldoAkhirK

    // Saldo = if SaldoAkhirD > 0 then SaldoAkhirD else SaldoAkhirK
    data.Saldo = data.SaldoAkhirD > 0 ? data.SaldoAkhirD : data.SaldoAkhirK

    // Tunai = (SUM(debet) + SUM(debet2) + SaldoAwal - SUM(kredit) - SUM(kredit2)) - TotalBonGiro
    const saldoGiro = parseFloat(data.SaldoGiro || 0)
    const saldoBon = parseFloat(data.SaldoBon || 0)
    const saldoBonD = parseFloat(data.SaldoBonD || 0)
    const saldoBonE = parseFloat(data.SaldoBonE || 0)
    const saldoBonA = parseFloat(data.SaldoBonA || 0)
    const saldoBonDH = parseFloat(data.SaldoBonDH || 0)
    const saldoGiroTolakan = parseFloat(data.SaldoGiroTolakan || 0)
    const totalBonGiro = saldoGiro + saldoBon + saldoBonD + saldoBonE + saldoBonA + saldoBonDH + saldoGiroTolakan
    data.Tunai = (sumDebet + sumDebet2 + saldoAwal - sumKredit - sumKredit2) - totalBonGiro
  }

  return data
})
```

## 9. Empty Value Display (.000000 Fix)

SP returns empty numeric values as `.000000`, `.00`, or `.0`. Handle in `formatCell`:

```typescript
function formatCell(value: any): string {
  if (value === null || value === undefined) return '-'
  if (typeof value === 'number') return value.toLocaleString('id-ID')
  if (typeof value === 'boolean') return value ? 'Yes' : 'No'
  if (typeof value === 'string') {
    // Handle ".000000" format (empty numeric from SP)
    if (value === '.000000' || value === '.00' || value === '.0') return '-'
    // Handle date formats: "2022-02-01 000000.000" or "2022-02-01"
    const dateMatch = value.match(/^(\d{4}-\d{2}-\d{2})/)
    if (dateMatch) {
      return new Date(dateMatch[1]).toLocaleDateString('id-ID')
    }
  }
  return String(value)
}
```

## 10. Date Format Parsing

Dates from SP come as `2022-02-01 000000.000` format. Parse correctly:

```typescript
// Match date part from datetime string
const dateMatch = value.match(/^(\d{4}-\d{2}-\d{2})/)
if (dateMatch) {
  return new Date(dateMatch[1]).toLocaleDateString('id-ID')
}
```

## 11. Export CSV Uses T2

For multi-dataset reports, export must use T2 (detail data), not reportData:

```typescript
async function exportReport(format = 'csv') {
  const datasets = reportStore.datasets
  const detailData = datasets['T2'] || datasets[Object.keys(datasets)[0]]

  if (!detailData || detailData.length === 0) {
    alert('No data to export')
    return
  }
  // ...
}
```

## Key Files Modified

| File | Changes |
|------|---------|
| `dbinsert_20101_kas_harian.sql` | footer_bands column + JSON config |
| `be-fitur/app/Models/MasterLaporan.php` | footer_bands fillable/cast |
| `be-fitur/app/Services/ReportService.php` | parseFooterBands, getUserDefaultPeriod, SP substitution |
| `be-fitur/app/Http/Controllers/ReportController.php` | include defaultPeriod |
| `fe-fitur/stores/reports.ts` | defaultPeriod state, userId in URL |
| `fe-fitur/pages/reports/[kode].vue` | T2 display fix, export, footer bands |

---

## 12. Table dbLabelGrup (Required for Grouping)

### Table Schema
```sql
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'dbLabelGrup'))
BEGIN
    CREATE TABLE dbLabelGrup (
        id INT IDENTITY(1,1) PRIMARY KEY,
        field_name VARCHAR(100),
        field_value VARCHAR(100),
        label VARCHAR(200),
        kode_laporan VARCHAR(50) NULL,
        aktif BIT DEFAULT 1,
        sort_order INT DEFAULT 0
    )
END
```

### Default Labels
```sql
INSERT INTO dbLabelGrup (field_name, field_value, label, aktif, sort_order)
VALUES
('NoBukti', '', 'Bukti Kas Masuk', 1, 1),
('NoBukti', '', 'Bukti Bank Masuk', 1, 2),
('NoBukti', '', 'Bukti Kas Keluar', 1, 3),
('NoBukti', '', 'Bukti Bank Keluar', 1, 4)
```

### Model
```php
// be-fitur/app/Models/LabelGrup.php
class LabelGrup extends Model
{
    protected $table = 'dbLabelGrup';
    public $timestamps = false;

    public static function getMapping(string $field): array
    {
        return static::where('field_name', $field)
            ->where('aktif', true)
            ->orderBy('sort_order')
            ->pluck('label', 'field_value')
            ->toArray();
    }
}
```

## 13. Array Key Access in buildGroupedData

**Problem:** `$levelFields[2]` may not exist for single-level grouping.

**Fix:**
```php
// Before (crash if only 1 grouping level)
$level2Value = $row[$levelFields[2]] ?? '';

// After (safe)
$level2Value = isset($levelFields[2]) ? ($row[$levelFields[2]] ?? '') : '';
```

## 14. Safe Array Access in generateReport

**Problem:** `$config['datasets']`, `$config['grouping']`, `$config['columns']` may cause undefined index.

**Fix:**
```php
// Use null coalescing everywhere
foreach ($config['datasets'] ?? [] as $dataset) { ... }
$grouping = $config['grouping'] ?? [];
$mainDataset = $config['datasets'][0]['nama_dataset'] ?? null;
```

## 15. Try-Catch in generateReport

**Fix:** Wrap entire function in try-catch to return proper error message:
```php
public function generateReport(string $kodeMenu, array $filters): array
{
    try {
        // ... existing code ...
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'Report generation failed: ' . $e->getMessage()
        ];
    }
}
```

## 16. Remove Duplicate Records

**Problem:** SQL INSERT run multiple times causes duplicate filters/columns.

**Fix:** Delete duplicates keeping first record:
```sql
DELETE FROM dbparameterlaporan
WHERE id_parameter IN (
    SELECT id_parameter FROM (
        SELECT id_parameter, ROW_NUMBER() OVER (PARTITION BY nama_filter ORDER BY id_parameter) AS rn
        FROM dbparameterlaporan
        WHERE id_laporan = @id_laporan
    ) t WHERE rn > 1
)
```

## 17. KODEMENU vs ACCESS (Critical!)

| Field | Contoh | Fungsi |
|-------|--------|--------|
| `DBMENUREPORT.KODEMENU` | `02020101` | Kode di database & URL web |
| `DBMENUREPORT.ACCESS` | `2020101` | Kode trigger Delphi |

- **URL web**: `/reports/02020101` (pakai KODEMENU)
- **dbmasterlaporan.KODEMENU**: pakai KODEMENU dari DBMENUREPORT, BUKAN ACCESS

See: `.claude/memory/reference/report-setup-pattern.md`