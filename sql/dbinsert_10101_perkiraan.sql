USE dbwbcp2;
GO

-- =====================================================
-- Report: Daftar Aktiva (KODEMENU: 010101, ACCESS: 010101)
-- Source: Delphi case 10101, ShowReportPreview(..., -1) -> no filter
-- Note: KODEMENU stored with leading zero as '010101'
-- =====================================================

-- 1. Insert Menu
IF NOT EXISTS (SELECT 1 FROM dbmenureport WHERE KODEMENU = '010101')
BEGIN
    INSERT INTO dbmenureport (KODEMENU, Keterangan, L0, ACCESS)
    VALUES ('010101', 'Daftar Aktiva', 1, 10101);
    PRINT 'Inserted: dbmenureport 010101';
END
ELSE
BEGIN
    PRINT 'Skipped: dbmenureport 010101 already exists';
END
GO

-- 2. User Access SA
IF NOT EXISTS (SELECT 1 FROM dbflmenureport WHERE USERID = 'SA' AND L1 = '010101')
BEGIN
    INSERT INTO dbflmenureport (USERID, L1, Access)
    VALUES ('SA', '010101', 1);
    PRINT 'Inserted: dbflmenureport SA -> 010101';
END
GO

-- 3. Master Laporan
IF NOT EXISTS (SELECT 1 FROM dbmasterlaporan WHERE KODEMENU = '010101')
BEGIN
    INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, status_aktif)
    VALUES ('010101', 'Daftar Aktiva', 'Laporan Daftar Aktiva Tetap', 1);
    PRINT 'Inserted: dbmasterlaporan 010101';
END
GO

-- 4. Query Dataset
DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '010101');

IF @IdLap IS NOT NULL AND NOT EXISTS (SELECT 1 FROM dbquerylaporan WHERE id_laporan = @IdLap AND nama_dataset = 'QuView')
BEGIN
    INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan)
    VALUES (
        @IdLap,
        'QuView',
        'SELECT Perkiraan, Keterangan, Quantity, Persen, Tanggal, MyTipe, NamaBag, KodeBag, MyAkumulasi, MyBiaya, MyBiaya2 FROM vwaktiva ORDER BY Perkiraan',
        'Data Aktiva',
        1
    );
    PRINT 'Inserted: dbquerylaporan for 010101';
END
GO

-- 5. NO Filter Parameters (Delphi uses -1 = no filter)

-- 6. Column Config (from ReportMasterAktiva.fr3)
DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '010101');

IF @IdLap IS NOT NULL
BEGIN
    DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;

    INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible)
    VALUES
    (@IdLap, 'QuView', 'Perkiraan', 'Kode Aktiva', 1, 'text', 'left', 0, 1),
    (@IdLap, 'QuView', 'Keterangan', 'Nama Aktiva', 2, 'text', 'left', 0, 1),
    (@IdLap, 'QuView', 'Quantity', 'Qty', 3, 'number', 'right', 1, 1),
    (@IdLap, 'QuView', 'Persen', 'Susut (%)', 4, 'number', 'right', 0, 1),
    (@IdLap, 'QuView', 'Tanggal', 'Tgl Perolehan', 5, 'date', 'center', 0, 1),
    (@IdLap, 'QuView', 'MyTipe', 'Metode', 6, 'text', 'left', 0, 1),
    (@IdLap, 'QuView', 'NamaBag', 'Bagian', 7, 'text', 'left', 0, 1),
    (@IdLap, 'QuView', 'MyAkumulasi', 'Akumulasi Penyusutan', 8, 'number', 'right', 1, 1),
    (@IdLap, 'QuView', 'MyBiaya', 'Biaya Penyusutan 1', 9, 'number', 'right', 1, 1),
    (@IdLap, 'QuView', 'MyBiaya2', 'Biaya Penyusutan 2', 10, 'number', 'right', 1, 1);
    PRINT 'Inserted: dbkolomlaporan for 010101';
END
GO

-- 7. No Grouping
DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '010101');

IF @IdLap IS NOT NULL
BEGIN
    DELETE FROM dbgrouplaporan WHERE id_laporan = @IdLap;
    PRINT 'Deleted: dbgrouplaporan for 010101';
END
GO

PRINT '';
PRINT '=== Report 010101 (Daftar Aktiva) setup complete! ===';
PRINT 'Test: http://localhost:3000/reports/010101';
GO
