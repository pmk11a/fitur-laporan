---
name: report-setup-pattern
description: Pattern untuk setup report di Fluffy Bee - INCLUDE LESSON LEARNED
type: reference
---

# Report Setup Pattern

## Workflow Setup Report Baru

### 1. Cek Delphi Source
```
Grep: case KodeReport of [code] : ShowReportPreview
```
- Line ~1423: `10101 : ShowReportPreview(' Daftar Aktiva ',-1)`
- `-1` = no filter UI, Langsung generate
- `number` = ada filter index

### 2. Cek .dfm File (COLUMN DEFINITIONS - WAJIB!)
```
Glob: pwt/**/*.dfm
Read: Form.dfm (line 244-325 untuk QuPerkiraan)
```
- Extract field names dari SQL Strings
- Extract computed fields (CASE WHEN... END AS mKelompok)
- **INI WAJIB DIBACA** - jangan skip!

### 3. Cek .fr3 File (Layout)
```
Glob: **/ReportFiles/*.fr3
Read: untuk header labels dan alignment
```
- Extract columns dari `<TfrxMemoView ... DataField="...">`
- Extract format_type (text, number, date)
- Extract alignment (left, center, right)

### 4. Cek Sidebar/Existing KODEMENU (KRUSIAL!)
```
Grep: menu.ts, MenuController.php
Cek: existing KODEMENU dengan leading zero
```
- Menu pakai `0102`, bukan `102`
- Selalu tanya user: "KODEMENU berapa?" sebelum buat SQL
- Kalau user bilang "Daftar Neraca", cek dulu KODEMENU existing

### 5. Cek Stored Procedures (kalau ada multi-dataset)
```
Grep: sp_ReportNeracaAktiva, sp_ReportNeracaPasiva
```
- Extract dari FrmReportPreview.pas

---

## Step-by-Step Checklist

```
[ ] 1. Baca Delphi case [code] di FrmReportPreview.pas
[ ] 2. Baca Form.dfm untuk columns & computed fields
[ ] 3. Baca .fr3 untuk layout & labels
[ ] 4. Tanya user: KODEMENU berapa? (cek existing di sidebar)
[ ] 5. Cek stored procedures kalau ada
[ ] 6. Buat SQL dengan KODEMENU yang BENAR
[ ] 7. Tanya user: "Execute SQL sekarang?"
[ ] 8. Test: http://localhost:3000/reports/{KODEMENU}
```

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
(@IdLap, 'QuView', 'SELECT ... FROM dbPerkiraan WHERE kelompok <= 2 ORDER BY Perkiraan', 'Master Perkiraan', 1);
GO

-- 5. NO dbparameterlaporan (Delphi -1 = no filter)

-- 6. dbkolomlaporan (dari .dfm field names + computed fields)
DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '0102');
DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible)
VALUES
(@IdLap, 'QuView', 'Perkiraan', 'Perkiraan', 1, 'text', 'left', 0, 1),
(@IdLap, 'QuView', 'Keterangan', 'Keterangan', 2, 'text', 'left', 0, 1),
(@IdLap, 'QuView', 'mKelompok', 'Kelompok', 3, 'text', 'center', 0, 1),
(@IdLap, 'QuView', 'mDK', 'Debet/Kredit', 4, 'text', 'center', 0, 1),
(@IdLap, 'QuView', 'mTipe', 'Tipe', 5, 'text', 'center', 0, 1),
(@IdLap, 'QuView', 'Neraca', 'Neraca', 6, 'text', 'left', 0, 1);
GO

PRINT '=== Report 0102 (Daftar Neraca) setup complete! ===';
GO
```

---

## Lesson Learned (dari bug 102 vs 0102)

### Yang Salah:
1. Skip `.dfm` - langsung pakai `.fr3` untuk columns
2. Buat SQL pakai `102` padahal sidebar pakai `0102`
3. Langsung assume tanpa cek existing KODEMENU

### Yang Benar:
1. **Selalu baca `.dfm`** untuk columns (field names + computed fields)
2. **Selalu tanya KODEMENU** sebelum buat SQL
3. **Cek existing** di `menu.ts` atau `MenuController.php`
4. **Leading zero itu penting** - `0102` ≠ `102`

---

## KODEMENU vs ACCESS (CRITICAL!)

### Dua Kode yang Berbeda:

| Field | Contoh | Fungsi |
|-------|--------|--------|
| `DBMENUREPORT.KODEMENU` | `02020101` | Kode di database & URL web (`/reports/02020101`) |
| `DBMENUREPORT.ACCESS` | `2020101` | Kode trigger di Delphi (TIDAK perlu di dbmasterlaporan) |

### Jangan Salah:
- ❌ Pakai ACCESS Delphi (`2020101`) sebagai KODEMENU di `dbmasterlaporan`
- ❌ Tambah kolom ACCESS di `dbmasterlaporan` (kolom itu tidak ada)

### Yang Benar:
```sql
-- dbmasterlaporan pakai KODEMENU dari DBMENUREPORT
INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, ...)
VALUES ('02020101', 'Penerimaan Kas', ...)  -- KODEMENU, BUKAN ACCESS
```

### Cara Cek yang Benar:
```sql
-- 1. Cek DBMENUREPORT untuk dapat KODEMENU
SELECT KODEMENU, Keterangan, ACCESS FROM DBMENUREPORT WHERE Keterangan LIKE '%Penerimaan%'

-- Hasil:
-- KODEMENU: 02020101 (pakai ini untuk URL & dbmasterlaporan)
-- ACCESS:   2020101  (ini trigger Delphi, tidak perlu di dbmasterlaporan)

-- 2. URL web pakai KODEMENU
http://localhost:3000/reports/02020101
```

---

## Key Files to Check
| File | Purpose |
|------|---------|
| `pwt/ReportPreview/FrmReportPreview.pas` | Delphi case logic |
| `pwt/**/*.dfm` | **COLUMN DEFINITIONS + Computed fields** |
| `pwt/MyProject/ReportFiles/*.fr3` | Layout & labels |
| `fe-fitur/stores/menu.ts` | **Existing KODEMENU** |
| `be-fitur/app/Http/Controllers/MenuController.php` | **Existing KODEMENU** |
| `be-fitur/app/Services/ReportService.php` | Backend API |
| `fe-fitur/pages/reports/[kode].vue` | Frontend display |

---

## Testing URL
```
http://localhost:3000/reports/{KODEMENU}
```

---

## Template Prompt untuk Buat Report

```
Buatkan report [Nama] dengan KODEMENU [kode]
```

**Contoh:**
```
Buatkan report Daftar Neraca dengan kode 102
```

**Respon Claude harus:**
1. Cek Delphi case 102
2. Baca .dfm untuk columns
3. Baca .fr3 untuk layout
4. Tanya user: "KODEMENU yang benar di sidebar apa?"
5. Buat SQL
6. Tanya: "Execute SQL sekarang?"