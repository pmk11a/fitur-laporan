-- =====================================================
-- Report: Daftar Neraca (KODEMENU: 0102)
-- Source: Delphi case 102, FrmNeraca / FrmReportPreview
-- Query: dbPerkiraan (master perkiraan/coa)
-- Layout: Single dataset, flat table dengan computed fields
-- =====================================================

USE dbwbcp2;
GO

-- 1. Update dbmenureport (Daftar Neraca)
IF NOT EXISTS (SELECT 1 FROM dbmenureport WHERE KODEMENU = '0102')
BEGIN
    INSERT INTO dbmenureport (KODEMENU, Keterangan, L0, ACCESS)
    VALUES ('0102', 'Daftar Neraca', 1, 102);
    PRINT 'Inserted: dbmenureport 0102';
END
ELSE
BEGIN
    UPDATE dbmenureport SET Keterangan = 'Daftar Neraca', ACCESS = 102 WHERE KODEMENU = '0102';
    PRINT 'Updated: dbmenureport 0102';
END
GO

-- 2. User Access SA
IF NOT EXISTS (SELECT 1 FROM dbflmenureport WHERE USERID = 'SA' AND L1 = '0102')
BEGIN
    INSERT INTO dbflmenureport (USERID, L1, Access)
    VALUES ('SA', '0102', 1);
    PRINT 'Inserted: dbflmenureport SA -> 0102';
END
GO

-- 3. Master Laporan
IF NOT EXISTS (SELECT 1 FROM dbmasterlaporan WHERE KODEMENU = '0102')
BEGIN
    INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, status_aktif)
    VALUES ('0102', 'Daftar Neraca', 'Daftar Perkiraan untuk Setup Neraca', 1);
    PRINT 'Inserted: dbmasterlaporan 0102';
END
ELSE
BEGIN
    UPDATE dbmasterlaporan SET nama_laporan = 'Daftar Neraca', deskripsi = 'Daftar Perkiraan untuk Setup Neraca' WHERE KODEMENU = '0102';
    PRINT 'Updated: dbmasterlaporan 0102';
END
GO

-- 4. Query Dataset (single dataset: QuView)
DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '0102');

DELETE FROM dbquerylaporan WHERE id_laporan = @IdLap;

INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan)
VALUES
(@IdLap, 'QuView', 'SELECT Perkiraan, Keterangan, Kelompok, Tipe, DK, Neraca, Valas, Simbol, FlagCashFlow,
        CASE WHEN kelompok=0 THEN ''Aktiva''
             WHEN kelompok=1 THEN ''Kewajiban''
             WHEN kelompok=2 THEN ''Modal''
             WHEN kelompok=3 THEN ''Pendapatan''
             WHEN kelompok=4 THEN ''Biaya'' END AS mKelompok,
        CASE WHEN Tipe=0 THEN ''General''
             WHEN Tipe=1 THEN ''Detail'' END AS mTipe,
        CASE WHEN DK=0 THEN ''Debet''
             WHEN DK=1 THEN ''Kredit'' END AS mDK
FROM dbPerkiraan
WHERE kelompok <= 2
ORDER BY Perkiraan', 'Master Perkiraan', 1);
PRINT 'Inserted: dbquerylaporan QuView';
GO

-- 5. NO Filter Parameters (Delphi -1 = no filter)

-- 6. NO Grouping Config (flat table)

-- 7. Column Config (dari .fr3 ReportMasterNeraca.fr3)
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
PRINT 'Inserted: dbkolomlaporan QuView';
GO

PRINT '';
PRINT '=== Report 0102 (Daftar Neraca - Master Perkiraan) setup complete! ===';
PRINT 'Query: dbPerkiraan WHERE kelompok <= 2 ORDER BY Perkiraan';
PRINT 'Columns: Perkiraan, Keterangan, mKelompok, mDK, mTipe, Neraca';
PRINT 'Test: http://localhost:3000/reports/0102';
GO