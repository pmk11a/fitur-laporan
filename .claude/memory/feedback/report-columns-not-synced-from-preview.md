---
name: report-columns-not-synced-from-preview
description: Bug fix: report columns not updated from preview endpoint - columns only set on fetchReport, not on generateReportWithFilters
type: feedback
---

## Bug: Kolom dinamis (misal jumlah2) tidak muncul di report

**Why:** `reportStore.columns` hanya di-update saat `fetchReport()` (GET `/reports/{kode}` endpoint).
Saat `generateReportWithFilters()` (POST `/reports/{kode}/preview` endpoint), columns TIDAK di-update.
Jadi即使 database sudah benar, `columns` di store masih stale/missing.

**Root cause:** Frontend architecture mismatch
- GET `/reports/{kode}` → returns `config.columns` → stored in `reportStore.columns`
- POST `/reports/{kode}/preview` → returns `config.columns` → but NOT stored

**Fix:** Update `columns` di `generateReportWithFilters()`:

```typescript
// fe-fitur/stores/reports.ts
if (response.success) {
  this.datasets = response.datasets || {}
  this.groupedData = response.groupedData
  this.grandTotal = response.grandTotal || {}
  this.reportData = Object.values(response.datasets)[0] || []
  // Update columns from config (for dynamic columns like jumlah2)
  this.columns = response.config?.columns || {}
  this.groupingConfig = response.groupingConfig || null
}
```

**How to apply:** Whenever add/update columns di `dbkolomlaporan`:
1. Update database columns definition
2. Ensure API preview response includes `config.columns`
3. Ensure store updates `columns` from preview response

**Related:** Report 020503 (Neraca) - tambah kolom `jumlah2` (bulan lalu) via `dbkolomlaporan`