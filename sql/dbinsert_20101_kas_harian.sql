-- =====================================================
-- Kas Harian Report Setup (KODEMENU: 020101)
-- Fluffy Bee Dynamic Report Engine
-- Database: dbwbcp2 (SQL Server)
-- Layout from: ReportKasHarian.fr3
-- Note: 2 tingkat layout - Header dan Footer
-- =====================================================

USE dbwbcp2;
GO

PRINT '=== Setting up Kas Harian (020101) Report ===';
PRINT '';

-- =====================================================
-- 0. Clean up any existing records for 020101
-- =====================================================
DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020101');

IF @IdLap IS NOT NULL
BEGIN
    DELETE FROM dbgrouplaporan WHERE id_laporan = @IdLap;
    DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;
    DELETE FROM dbparameterlaporan WHERE id_laporan = @IdLap;
    DELETE FROM dbquerylaporan WHERE id_laporan = @IdLap;
    DELETE FROM dbmasterlaporan WHERE id_laporan = @IdLap;
    PRINT 'Cleaned up existing records for 020101';
END
GO

-- =====================================================
-- 1. dbmenureport - Menu Entry
-- =====================================================
IF NOT EXISTS (SELECT 1 FROM dbmenureport WHERE KODEMENU = '020101')
BEGIN
    INSERT INTO dbmenureport (KODEMENU, Keterangan, L0, ACCESS)
    VALUES ('020101', 'Kas Harian', 1, 201);
    PRINT 'Inserted: dbmenureport for 020101';
END
ELSE
BEGIN
    UPDATE dbmenureport SET Keterangan = 'Kas Harian', ACCESS = 201 WHERE KODEMENU = '020101';
    PRINT 'Updated: dbmenureport for 020101';
END
GO

-- =====================================================
-- 2. dbflmenureport - User Access
-- =====================================================
IF NOT EXISTS (SELECT 1 FROM dbflmenureport WHERE USERID = 'SA' AND L1 = '020101')
BEGIN
    INSERT INTO dbflmenureport (USERID, L1, Access, IsDesign, IsExport)
    VALUES ('SA', '020101', 1, 0, 0);
    PRINT 'Inserted: dbflmenureport for SA';
END
ELSE
BEGIN
    UPDATE dbflmenureport SET Access = 1, IsDesign = 0, IsExport = 0
    WHERE USERID = 'SA' AND L1 = '020101';
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

IF NOT EXISTS (SELECT 1 FROM dbmasterlaporan WHERE KODEMENU = '020101')
BEGIN
    INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, query_sumber_data, status_aktif, footer_bands)
    VALUES ('020101', 'Kas Harian', 'Laporan kas harian IDR', NULL, 1,
        '{"bands":{"title":{"enabled":true,"content":"LAPORAN KAS","align":"center"},"pageHeader":{"enabled":true,"content":"Kas Harian"},"summary":{"enabled":true,"layout":{"columns":3,"alignment":"spread"},"signatures":[{"label":"Kontrol","position":"left"},{"label":"Kasir","position":"center"},{"label":"Pimpinan","position":"right"}]}}}');
    PRINT 'Inserted: dbmasterlaporan for 020101';
END
ELSE
BEGIN
    UPDATE dbmasterlaporan
    SET nama_laporan = 'Kas Harian',
        deskripsi = 'Laporan kas harian IDR',
        status_aktif = 1,
        footer_bands = '{"bands":{"title":{"enabled":true,"content":"LAPORAN KAS","align":"center"},"pageHeader":{"enabled":true,"content":"Kas Harian"},"summary":{"enabled":true,"layout":{"columns":3,"alignment":"spread"},"signatures":[{"label":"Kontrol","position":"left"},{"label":"Kasir","position":"center"},{"label":"Pimpinan","position":"right"}]}}}'
    WHERE KODEMENU = '020101';
    PRINT 'Updated: dbmasterlaporan for 020101';
END
GO

-- =====================================================
-- 4. dbquerylaporan - Dataset Definitions
-- =====================================================
DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020101');

IF @IdLap IS NOT NULL
BEGIN
    -- Dataset T1: Saldo Awal (from Sp_LapSaldoAwal)
    DELETE FROM dbquerylaporan WHERE id_laporan = @IdLap AND nama_dataset = 'T1';
    INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan)
    VALUES (@IdLap, 'T1', 'EXEC Sp_LapSaldoAwal @Perkiraan, @TglAwal, @TglAkhir, @Divisi', 'Saldo Awal Kas', 1);
    PRINT 'Inserted: dbquerylaporan T1';

    -- Dataset T2: Detail (from Sp_LapKasHarian)
    DELETE FROM dbquerylaporan WHERE id_laporan = @IdLap AND nama_dataset = 'T2';
    INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan)
    VALUES (@IdLap, 'T2', 'EXEC Sp_LapKasHarian @Perkiraan, @TglAwal, @TglAkhir, @Divisi', 'Detail Transaksi Kas', 2);
    PRINT 'Inserted: dbquerylaporan T2';
END
GO

-- =====================================================
-- 5. dbparameterlaporan - Filter Parameters
-- =====================================================
DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020101');

IF @IdLap IS NOT NULL
BEGIN
    DELETE FROM dbparameterlaporan WHERE id_laporan = @IdLap;

    INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi)
    VALUES
    (@IdLap, 'TglAwal', 'Tanggal Awal', 'date', 1, CONVERT(VARCHAR(10), DATEADD(DAY, -30, GETDATE()), 23), 1),
    (@IdLap, 'TglAkhir', 'Tanggal Akhir', 'date', 1, CONVERT(VARCHAR(10), GETDATE(), 23), 2),
    (@IdLap, 'Perkiraan', 'Kode Perkiraan', 'perkiraan', 1, 'KAS', 3),
    (@IdLap, 'Divisi', 'Divisi', 'text', 0, '', 4);

    PRINT 'Inserted: dbparameterlaporan (4 filters)';
END
GO

-- =====================================================
-- 6. dbkolomlaporan - Column Configuration
-- 2 TINGKAT LAYOUT (sesuai ReportKasHarian.fr3):
-- Tingkat 1 - Header Detail:
--   Tgl. | No.Bukti | URAIAN | Perk. | Penerimaan (TUNAI) | Pengeluaran (TUNAI) | Penerimaan (CH/GB) | Pengeluaran (CH/GB)
-- Tingkat 2 - Footer Summary:
--   Sub. Jumlah | Jumlah | Saldo Awal | Saldo Akhir
--   Uang Tunai | CH/GB | Bon Smnt. | Sisa Keuangan | Saldo Kasir
-- =====================================================
DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020101');

IF @IdLap IS NOT NULL
BEGIN
    DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;

    -- ============================================
    -- TINGKAT 1: Detail Transactions (T2)
    -- DataFields dari .fr3: tanggal, nobukti, Keterangan, lawan, debet, kredit, debet2, kredit2
    -- ============================================
    INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible)
    VALUES
    (@IdLap, 'T2', 'tanggal', 'Tgl.', 1, 'date', 'left', 0, 1),
    (@IdLap, 'T2', 'nobukti', 'No.Bukti', 2, 'text', 'left', 0, 1),
    (@IdLap, 'T2', 'Keterangan', 'Uraian', 3, 'text', 'left', 0, 1),
    (@IdLap, 'T2', 'lawan', 'Perk.', 4, 'text', 'left', 0, 1),
    (@IdLap, 'T2', 'debet', 'Penerimaan (TUNAI)', 5, 'currency', 'right', 1, 1),
    (@IdLap, 'T2', 'kredit', 'Pengeluaran (TUNAI)', 6, 'currency', 'right', 1, 1),
    (@IdLap, 'T2', 'debet2', 'Penerimaan (CH/GB)', 7, 'currency', 'right', 1, 1),
    (@IdLap, 'T2', 'kredit2', 'Pengeluaran (CH/GB)', 8, 'currency', 'right', 1, 1);

    -- ============================================
    -- TINGKAT 2: Summary/Footer (T1)
    -- DataFields dari .fr3 Footer:
    -- Kolom kiri: Uang Tunai, CH/GB, Bon Smnt., Sisa Keuangan, Saldo Kasir, Beda Kas
    -- Kolom kanan (Tunai): Total Penerimaan, SaldoAwalD, SaldoAkhirD, Total
    -- Kolom kanan (CH/GB): Total Pengeluaran, SaldoAwalK, SaldoAkhirK, Total
    -- ============================================
    INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible)
    VALUES
    -- Footer kiri - Uang Tunai & CH/GB section
    (@IdLap, 'T1', 'Tunai', 'Uang Tunai', 1, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'SaldoGiro', 'CH/GB', 2, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'SaldoBon', 'Bon Smnt.', 3, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'SaldoBonD', 'Bon USD', 4, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'SaldoBonE', 'Bon EUR', 5, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'SaldoBonA', 'Bon AUD', 6, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'SaldoBonDH', 'Bon DH', 7, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'SaldoGiroTolakan', 'CH/GB Tolakan', 8, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'Saldo', 'Sisa Keuangan', 9, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'SaldoKasir', 'Saldo Kasir', 10, 'currency', 'right', 0, 1),
    -- Footer kanan - Saldo calculations
    (@IdLap, 'T1', 'SaldoAwalD', 'Saldo Awal', 11, 'currency', 'right', 1, 1),
    (@IdLap, 'T1', 'SaldoAkhirD', 'Saldo Akhir', 12, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'TotalD', 'Total', 13, 'currency', 'right', 1, 1),
    (@IdLap, 'T1', 'SaldoAwalK', 'Saldo Awal (K)', 14, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'SaldoAkhirK', 'Saldo Akhir (K)', 15, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'TotalK', 'Total (K)', 16, 'currency', 'right', 1, 1);

    PRINT 'Inserted: dbkolomlaporan (16 columns - 2 tingkat)';
END
GO

-- =====================================================
-- 7. No grouping for Kas Harian
-- =====================================================
DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020101');
DELETE FROM dbgrouplaporan WHERE id_laporan = @IdLap;
PRINT 'Cleared: dbgrouplaporan';
GO

-- =====================================================
-- Verification
-- =====================================================
PRINT '';
PRINT '=== Verification ===';

SELECT 'dbmenureport' AS tbl, COUNT(*) AS cnt FROM dbmenureport WHERE KODEMENU = '020101'
UNION ALL
SELECT 'dbflmenureport', COUNT(*) FROM dbflmenureport WHERE L1 = '020101'
UNION ALL
SELECT 'dbmasterlaporan', COUNT(*) FROM dbmasterlaporan WHERE KODEMENU = '020101'
UNION ALL
SELECT 'dbquerylaporan', COUNT(*) FROM dbquerylaporan WHERE id_laporan = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020101')
UNION ALL
SELECT 'dbparameterlaporan', COUNT(*) FROM dbparameterlaporan WHERE id_laporan = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020101')
UNION ALL
SELECT 'dbkolomlaporan', COUNT(*) FROM dbkolomlaporan WHERE id_laporan = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020101');

PRINT '';
PRINT '=== Setup Complete! ===';
PRINT 'Test URL: http://localhost:3000/reports/020101';
GO