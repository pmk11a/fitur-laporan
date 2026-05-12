-- =====================================================
-- Report: Daftar Perkiraan (KODEMENU: 101, ACCESS: 101)
-- Source: Delphi selectreport case 101
-- Query: SELECT * FROM vwPerkiraan ORDER BY Perkiraan
-- =====================================================

USE dbwbcp2;
GO

-- 1. Insert Menu (if not exists)
IF NOT EXISTS (SELECT 1 FROM dbmenureport WHERE KODEMENU = '101')
BEGIN
    INSERT INTO dbmenureport (KODEMENU, Keterangan, L0, ACCESS)
    VALUES ('101', 'Daftar Perkiraan', 1, 101);
    PRINT 'Inserted: dbmenureport 101';
END
ELSE
BEGIN
    PRINT 'Skipped: dbmenureport 101 already exists';
END
GO

-- 2. User Access (IT gets access)
IF NOT EXISTS (SELECT 1 FROM dbflmenureport WHERE USERID = 'IT' AND L1 = '101')
BEGIN
    INSERT INTO dbflmenureport (USERID, L1, Access)
    VALUES ('IT', '101', 1);
    PRINT 'Inserted: dbflmenureport IT -> 101';
END
GO

-- 3. Master Laporan
IF NOT EXISTS (SELECT 1 FROM dbmasterlaporan WHERE KODEMENU = '101')
BEGIN
    INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, status_aktif)
    VALUES ('101', 'Daftar Perkiraan', 'Laporan Daftar Perkiraan Akun', 1);
    PRINT 'Inserted: dbmasterlaporan 101';
END
GO

DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '101');

-- 4. Query Dataset (single dataset, no grouping)
IF NOT EXISTS (SELECT 1 FROM dbquerylaporan WHERE id_laporan = @IdLap AND nama_dataset = 'QuView')
BEGIN
    INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan)
    VALUES (
        @IdLap,
        'QuView',
        'SELECT Perkiraan, Keterangan, myKelompok, myDK, mytipe, Valas FROM vwPerkiraan ORDER BY Perkiraan',
        'Data Perkiraan',
        1
    );
    PRINT 'Inserted: dbquerylaporan for 101';
END
GO

-- 5. Filter Parameters (no filters needed - just show all)
IF NOT EXISTS (SELECT 1 FROM dbparameterlaporan WHERE id_laporan = @IdLap)
BEGIN
    INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi)
    VALUES
    (@IdLap, 'Semua', 'Tampilkan Semua', 'checkbox', 0, '', 1);
    PRINT 'Inserted: dbparameterlaporan for 101';
END
GO

-- 6. Column Config (visible columns)
DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible)
VALUES
(@IdLap, 'QuView', 'Perkiraan', 'Kode Perkiraan', 1, 'text', 'left', 0, 1),
(@IdLap, 'QuView', 'Keterangan', 'Nama Perkiraan', 2, 'text', 'left', 0, 1),
(@IdLap, 'QuView', 'myKelompok', 'Kelompok', 3, 'text', 'left', 0, 1),
(@IdLap, 'QuView', 'myDK', 'D/K', 4, 'text', 'center', 0, 1),
(@IdLap, 'QuView', 'mytipe', 'Tipe', 5, 'text', 'left', 0, 1),
(@IdLap, 'QuView', 'Valas', 'Valas', 6, 'text', 'center', 0, 1);
GO

-- 7. No Grouping Config (flat table)
DELETE FROM dbgrouplaporan WHERE id_laporan = @IdLap;
PRINT 'Deleted: dbgrouplaporan for 101 (no grouping needed)';
GO

PRINT '';
PRINT '=== Report 101 (Daftar Perkiraan) setup complete! ===';
PRINT 'Test: http://localhost:3000/reports/101';
GO