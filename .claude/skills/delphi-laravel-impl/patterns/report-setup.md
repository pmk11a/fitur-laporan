# Fluffy Bee Report Setup Pattern

## Trigger
```
Buatkan report [Nama] dengan kode [KODEMENU]
```

---

## Pre-Setup Checklist (WAJIB)

```
[ ] 1. Baca Delphi case [code] di FrmReportPreview.pas
[ ] 2. Baca Form.dfm untuk columns (field names + computed fields)
[ ] 3. Baca .fr3 untuk layout (header labels, alignment)
[ ] 4. Tanya user: KODEMENU berapa? (cek existing di menu.ts/MenuController.php)
[ ] 5. Cek stored procedures kalau multi-dataset
[ ] 6. Buat SQL
[ ] 7. Tanya: Execute sekarang?
[ ] 8. Test: http://localhost:3000/reports/{KODEMENU}
```

---

## Source Files to Read

| File | Purpose |
|------|---------|
| `pwt/ReportPreview/FrmReportPreview.pas` | Delphi case logic, query |
| `pwt/**/*.dfm` | **COLUMN DEFINITIONS + Computed fields** |
| `pwt/MyProject/ReportFiles/*.fr3` | Layout, header labels |
| `fe-fitur/stores/menu.ts` | **Existing KODEMENU** |
| `be-fitur/app/Http/Controllers/MenuController.php` | **Existing KODEMENU** |

---

## Common KODEMENU Pattern

**Delphi case 102 = sidebar KODEMENU 0102**
- Always include leading zero
- `0102` ≠ `102`

---

## SQL Setup Template

```sql
USE dbwbcp2;
GO

-- 1. dbmenureport (KODEMENU dengan leading zero!)
IF NOT EXISTS (SELECT 1 FROM dbmenureport WHERE KODEMENU = '0102')
BEGIN
    INSERT INTO dbmenureport (KODEMENU, Keterangan, L0, ACCESS)
    VALUES ('0102', 'Daftar Neraca', 1, 102);
END
ELSE
BEGIN
    UPDATE dbmenureport SET Keterangan = 'Daftar Neraca', ACCESS = 102 WHERE KODEMENU = '0102';
END
GO

-- 2. dbflmenureport
IF NOT EXISTS (SELECT 1 FROM dbflmenureport WHERE USERID = 'SA' AND L1 = '0102')
BEGIN
    INSERT INTO dbflmenureport (USERID, L1, Access) VALUES ('SA', '0102', 1);
END
GO

-- 3. dbmasterlaporan
IF NOT EXISTS (SELECT 1 FROM dbmasterlaporan WHERE KODEMENU = '0102')
BEGIN
    INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, status_aktif)
    VALUES ('0102', 'Daftar Neraca', 'Deskripsi', 1);
END
ELSE
BEGIN
    UPDATE dbmasterlaporan SET nama_laporan = 'Daftar Neraca', deskripsi = 'Deskripsi' WHERE KODEMENU = '0102';
END
GO

-- 4. dbquerylaporan (dari .dfm SQL Strings)
DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '0102');
DELETE FROM dbquerylaporan WHERE id_laporan = @IdLap;

INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan)
VALUES
(@IdLap, 'QuView', 'SELECT ... FROM table WHERE kondisi ORDER BY col', 'Deskripsi', 1);
GO

-- 5. dbparameterlaporan (DELPHI -1 = NO FILTER)

-- 6. dbkolomlaporan (dari .dfm field names)
DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '0102');
DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible)
VALUES
(@IdLap, 'QuView', 'FieldName', 'Label Display', 1, 'text', 'left', 0, 1);
GO

-- 7. dbgrouplaporan (jika ada grouping)
-- INSERT INTO dbgrouplaporan ...

PRINT '=== Report 0102 setup complete! ===';
GO
```

---

## Frontend Auto-Generate (No Filter)

Edit: `fe-fitur/pages/reports/[kode].vue`

```typescript
onMounted(async () => {
  if (kodeMenu.value) {
    await reportStore.fetchReport(kodeMenu.value)
    // Auto-generate if no filters
    if (!reportStore.currentReport?.filters?.length && reportStore.currentReport) {
      await generateReport()
    }
  }
})
```

---

## Testing

```
http://localhost:3000/reports/{KODEMENU}
```

Expected: Auto-generate, no filter UI, data displayed in table

---

## Lesson Learned

| Bug | Prevention |
|-----|------------|
| 0 records (KODEMENU 102 vs 0102) | Tanya KODEMENU sebelum buat SQL |
| Missing computed fields | Selalu baca .dfm untuk columns |
| Wrong columns | Baca .fr3 untuk field names |

---

## Backend: ReportService.php

Already supports:
- Single dataset (QuView)
- Multi-dataset (QuView3, QuView4)
- Grouping (buildGroupedData)
- Stored procedure execution

No modification needed for standard reports.