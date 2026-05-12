-- =====================================================
-- Bank Harian Report Setup (KODEMENU: 020102)
-- Fluffy Bee Dynamic Report Engine
-- Database: dbwbcp2 (SQL Server)
-- Layout: ReportBankHarian.fr3 (2 halaman: Detail + Summary)
-- Stored Procedures: Sp_LapSaldoAwal, Sp_LapBankHarian
-- Reference Delphi: FrmReportPreview.pas case 20102
-- =====================================================

USE dbwbcp2;
GO

PRINT '=== Setting up Bank Harian (020102) Report ===';
PRINT '';

-- =====================================================
-- 0. Clean up any existing records for 020102
-- =====================================================
DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020102');

IF @IdLap IS NOT NULL
BEGIN
    DELETE FROM dbgrouplaporan WHERE id_laporan = @IdLap;
    DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;
    DELETE FROM dbparameterlaporan WHERE id_laporan = @IdLap;
    DELETE FROM dbquerylaporan WHERE id_laporan = @IdLap;
    DELETE FROM dbmasterlaporan WHERE id_laporan = @IdLap;
    PRINT 'Cleaned up existing records for 020102';
END
GO

-- =====================================================
-- 1. dbmenureport - Menu Entry
-- =====================================================
IF NOT EXISTS (SELECT 1 FROM dbmenureport WHERE KODEMENU = '020102')
BEGIN
    INSERT INTO dbmenureport (KODEMENU, Keterangan, L0, ACCESS)
    VALUES ('020102', 'Bank Harian', 1, 201);
    PRINT 'Inserted: dbmenureport for 020102';
END
ELSE
BEGIN
    UPDATE dbmenureport SET Keterangan = 'Bank Harian', ACCESS = 201 WHERE KODEMENU = '020102';
    PRINT 'Updated: dbmenureport for 020102';
END
GO

-- =====================================================
-- 2. dbflmenureport - User Access
-- =====================================================
IF NOT EXISTS (SELECT 1 FROM dbflmenureport WHERE USERID = 'SA' AND L1 = '020102')
BEGIN
    INSERT INTO dbflmenureport (USERID, L1, Access, IsDesign, IsExport)
    VALUES ('SA', '020102', 1, 0, 0);
    PRINT 'Inserted: dbflmenureport for SA';
END
ELSE
BEGIN
    UPDATE dbflmenureport SET Access = 1, IsDesign = 0, IsExport = 0
    WHERE USERID = 'SA' AND L1 = '020102';
    PRINT 'Updated: dbflmenureport for SA';
END
GO

-- =====================================================
-- 3. dbmasterlaporan - Master Report Definition
-- =====================================================
-- Add footer_bands column if not exists
IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'dbmasterlaporan' AND COLUMN_NAME = 'footer_bands')
BEGIN
    ALTER TABLE dbmasterlaporan ADD footer_bands NVARCHAR(MAX) NULL;
    PRINT 'Added: footer_bands column to dbmasterlaporan';
END
GO

IF NOT EXISTS (SELECT 1 FROM dbmasterlaporan WHERE KODEMENU = '020102')
BEGIN
    INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, query_sumber_data, status_aktif, footer_bands)
    VALUES ('020102', 'Bank Harian', 'Laporan bank harian dengan mutasi transaksi bank', NULL, 1,
        '{"bands":{"title":{"enabled":true,"content":"LAPORAN BANK HARIAN","align":"center"},"pageHeader":{"enabled":true,"content":"Bank Harian"},"summary":{"enabled":true,"layout":{"columns":3,"alignment":"spread"},"signatures":[{"label":"Pimpinan","position":"left"},{"label":"Kontrol","position":"center"},{"label":"Kasir","position":"right"}]}}}');
    PRINT 'Inserted: dbmasterlaporan for 020102';
END
ELSE
BEGIN
    UPDATE dbmasterlaporan
    SET nama_laporan = 'Bank Harian',
        deskripsi = 'Laporan bank harian dengan mutasi transaksi bank',
        status_aktif = 1,
        footer_bands = '{"bands":{"title":{"enabled":true,"content":"LAPORAN BANK HARIAN","align":"center"},"pageHeader":{"enabled":true,"content":"Bank Harian"},"summary":{"enabled":true,"layout":{"columns":3,"alignment":"spread"},"signatures":[{"label":"Pimpinan","position":"left"},{"label":"Kontrol","position":"center"},{"label":"Kasir","position":"right"}]}}}'
    WHERE KODEMENU = '020102';
    PRINT 'Updated: dbmasterlaporan for 020102';
END
GO

-- =====================================================
-- 4. dbquerylaporan - Dataset Definitions (3 datasets)
-- =====================================================
DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020102');

IF @IdLap IS NOT NULL
BEGIN
    -- Dataset T1: Saldo Awal Bank
    DELETE FROM dbquerylaporan WHERE id_laporan = @IdLap AND nama_dataset = 'T1';
    INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan)
    VALUES (@IdLap, 'T1', 'EXEC Sp_LapSaldoAwal @Perkiraan, @TglAwal, @TglAkhir, @Divisi', 'Saldo Awal Bank', 1);
    PRINT 'Inserted: dbquerylaporan T1 (Saldo Awal)';

    -- Dataset T2: Detail Transaksi Bank (Halaman 1 - MasterData1)
    DELETE FROM dbquerylaporan WHERE id_laporan = @IdLap AND nama_dataset = 'T2';
    INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan)
    VALUES (@IdLap, 'T2', 'EXEC Sp_LapBankHarian @Perkiraan, @TglAwal, @TglAkhir, @Divisi', 'Detail Transaksi Bank Harian', 2);
    PRINT 'Inserted: dbquerylaporan T2 (Detail)';

    -- Dataset T3: Summary Saldo Bank (Halaman 2 - MasterData2)
    DELETE FROM dbquerylaporan WHERE id_laporan = @IdLap AND nama_dataset = 'T3';
    INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan)
    VALUES (@IdLap, 'T3', 'EXEC Sp_LapBankHarian @Perkiraan, @TglAwal, @TglAkhir, @Divisi', 'Summary Saldo Bank', 3);
    PRINT 'Inserted: dbquerylaporan T3 (Summary)';
END
GO

-- =====================================================
-- 5. dbparameterlaporan - Filter Parameters (4 filters)
-- =====================================================
DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020102');

IF @IdLap IS NOT NULL
BEGIN
    DELETE FROM dbparameterlaporan WHERE id_laporan = @IdLap;

    INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi)
    VALUES
    (@IdLap, 'TglAwal', 'Tanggal Awal', 'date', 1, CONVERT(VARCHAR(10), DATEADD(DAY, -30, GETDATE()), 23), 1),
    (@IdLap, 'TglAkhir', 'Tanggal Akhir', 'date', 1, CONVERT(VARCHAR(10), GETDATE(), 23), 2),
    (@IdLap, 'Perkiraan', 'Kode Perkiraan', 'perkiraan', 1, 'BANK', 3),
    (@IdLap, 'Divisi', 'Divisi', 'text', 0, '', 4);

    PRINT 'Inserted: dbparameterlaporan (4 filters)';
END
GO

-- =====================================================
-- 6. dbkolomlaporan - Column Configuration
-- 2 HALAMAN LAYOUT (sesuai ReportBankHarian.fr3):
-- Halaman 1 - Detail Transaksi (T2): Perkiraan, Tanggal, NoBukti, Keterangan, Lawan, Debet, Kredit
-- Halaman 2 - Summary Bank (T3): Perkiraan, SaldoUS, SaldoRp, CHGB, SaldoTotalRp
-- Footer Summary (T1): SaldoAwal, SaldoAkhir, Total (D/K)
-- =====================================================
DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020102');

IF @IdLap IS NOT NULL
BEGIN
    DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;

    -- ============================================
    -- HALAMAN 1: Detail Transaksi (T2 - MasterData1 frxDBDataset2)
    -- DataFields dari .fr3: Perkiraan, Tanggal, NoBukti, Keterangan, Lawan, Debet, Kredit
    -- ============================================
    INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible)
    VALUES
    (@IdLap, 'T2', 'Perkiraan', 'Perk. Bank', 1, 'text', 'center', 0, 1),
    (@IdLap, 'T2', 'Tanggal', 'Tanggal', 2, 'date', 'center', 0, 1),
    (@IdLap, 'T2', 'NoBukti', 'No. Bukti', 3, 'text', 'left', 0, 1),
    (@IdLap, 'T2', 'Keterangan', 'Uraian', 4, 'text', 'left', 0, 1),
    (@IdLap, 'T2', 'Lawan', 'Perk.', 5, 'text', 'left', 0, 1),
    (@IdLap, 'T2', 'Debet', 'Penerimaan', 6, 'currency', 'right', 1, 1),
    (@IdLap, 'T2', 'Kredit', 'Pengeluaran', 7, 'currency', 'right', 1, 1);

    -- ============================================
    -- HALAMAN 2: Summary Saldo Bank (T3 - MasterData2 frxDBDataset3)
    -- DataFields dari .fr3: Perkiraan+Keterangan, SaldoUS, SaldoRp, CHGB, SaldoTotalRp
    -- ============================================
    INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible)
    VALUES
    (@IdLap, 'T3', 'Perkiraan', 'Bank', 1, 'text', 'left', 0, 1),
    (@IdLap, 'T3', 'SaldoUS', 'Riil $', 2, 'currency', 'right', 1, 1),
    (@IdLap, 'T3', 'SaldoRp', 'Riil Rp', 3, 'currency', 'right', 1, 1),
    (@IdLap, 'T3', 'CHGB', 'CHGB', 4, 'currency', 'right', 1, 1),
    (@IdLap, 'T3', 'SaldoTotalRp', 'Total', 5, 'currency', 'right', 1, 1);

    -- ============================================
    -- FOOTER: Summary Saldo (T1 - frxDBDataset1)
    -- Footer calculations: SaldoAwalD, SaldoAkhirD, TotalD, SaldoAwalK, SaldoAkhirK, TotalK
    -- ============================================
    INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible)
    VALUES
    -- Kolom Debet (SaldoAwalD = SaldoAwal, SaldoAkhirD, TotalD)
    (@IdLap, 'T1', 'SaldoAwalD', 'Saldo Awal', 1, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'SaldoAkhirD', 'Saldo Akhir', 2, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'TotalD', 'Total', 3, 'currency', 'right', 1, 1),
    -- Kolom Kredit (SaldoAwalK, SaldoAkhirK, TotalK)
    (@IdLap, 'T1', 'SaldoAwalK', 'Saldo Awal (K)', 4, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'SaldoAkhirK', 'Saldo Akhir (K)', 5, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'TotalK', 'Total (K)', 6, 'currency', 'right', 1, 1);

    PRINT 'Inserted: dbkolomlaporan (18 columns - 3 datasets)';
END
GO

-- =====================================================
-- 7. No grouping for Bank Harian
-- =====================================================
DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020102');
DELETE FROM dbgrouplaporan WHERE id_laporan = @IdLap;
PRINT 'Cleared: dbgrouplaporan';
GO

-- =====================================================
-- Verification
-- =====================================================
PRINT '';
PRINT '=== Verification ===';

SELECT 'dbmenureport' AS tbl, KODEMENU, Keterangan, L0, ACCESS FROM dbmenureport WHERE KODEMENU = '020102'
UNION ALL
SELECT 'dbflmenureport', L1, USERID, Access, 0 FROM dbflmenureport WHERE L1 = '020102'
UNION ALL
SELECT 'dbmasterlaporan', KODEMENU, nama_laporan, status_aktif, 0 FROM dbmasterlaporan WHERE KODEMENU = '020102'
UNION ALL
SELECT 'dbquerylaporan', 'T1/T2/T3', nama_dataset, urutan, 0 FROM dbquerylaporan WHERE id_laporan = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020102')
UNION ALL
SELECT 'dbparameterlaporan', '4 params', nama_filter, posisi, wajib_isi FROM dbparameterlaporan WHERE id_laporan = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020102')
UNION ALL
SELECT 'dbkolomlaporan', CAST(COUNT(*) AS VARCHAR), 'columns', 0, 0 FROM dbkolomlaporan WHERE id_laporan = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020102');

PRINT '';
PRINT '=== Bank Harian (020102) setup complete! ===';
PRINT 'Test URL: http://localhost:3000/reports/020102';
GO