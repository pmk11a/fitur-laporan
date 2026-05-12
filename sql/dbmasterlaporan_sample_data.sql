-- =====================================================
-- Fluffy Bee Report Engine - Sample Data
-- Only insert into dbmasterlaporan, dbparameterlaporan, dbkomponenlaporan
-- dbmenureport and dbflmenureport already exist in database
-- Database: dbwbcp2
-- =====================================================

USE dbwbcp2;
GO

-- =====================================================
-- 1. Report Definitions (dbmasterlaporan)
-- Only insert if not exists (check by KODEMENU)
-- =====================================================

-- Kas Harian (20101)
IF NOT EXISTS (SELECT 1 FROM dbmasterlaporan WHERE KODEMENU = '20101')
BEGIN
    INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, query_sumber_data, status_aktif)
    VALUES (
        '20101',
        'Kas Harian',
        'Laporan kas harian dengan filter tanggal dan divisi',
        'SELECT
            TglTrans AS Tanggal,
            NoBukti,
            Uraian,
            KodePerkiraan,
            NamaPerkiraan,
            Debet,
            Kredit,
            Divisi
        FROM JURNAL
        WHERE KodePerkiraan LIKE ''1%''
            AND TglTrans BETWEEN @TglAwal AND @TglAkhir
            AND (Divisi = @Divisi OR @Divisi = '''')
        ORDER BY TglTrans, NoBukti',
        1
    );
    PRINT 'Inserted: 20101 Kas Harian';
END
ELSE
BEGIN
    PRINT 'Skipped: 20101 already exists';
END
GO

-- Bank Masuk (20102)
IF NOT EXISTS (SELECT 1 FROM dbmasterlaporan WHERE KODEMENU = '20102')
BEGIN
    INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, query_sumber_data, status_aktif)
    VALUES (
        '20102',
        'Bank Masuk',
        'Laporan bank masuk dengan filter tanggal dan divisi',
        'SELECT
            TglTrans AS Tanggal,
            NoBukti,
            Uraian,
            KodePerkiraan,
            NamaPerkiraan,
            Debet,
            Kredit,
            Divisi,
            Lokasi
        FROM JURNAL
        WHERE KodePerkiraan LIKE ''1%'' AND Debet > 0
            AND TglTrans BETWEEN @TglAwal AND @TglAkhir
            AND (Divisi = @Divisi OR @Divisi = '''')
            AND (Lokasi = @Lokasi OR @Lokasi = '''')
        ORDER BY TglTrans, NoBukti',
        1
    );
    PRINT 'Inserted: 20102 Bank Masuk';
END
ELSE
BEGIN
    PRINT 'Skipped: 20102 already exists';
END
GO

-- Bank Keluar (20103)
IF NOT EXISTS (SELECT 1 FROM dbmasterlaporan WHERE KODEMENU = '20103')
BEGIN
    INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, query_sumber_data, status_aktif)
    VALUES (
        '20103',
        'Bank Keluar',
        'Laporan bank keluar dengan filter tanggal dan divisi',
        'SELECT
            TglTrans AS Tanggal,
            NoBukti,
            Uraian,
            KodePerkiraan,
            NamaPerkiraan,
            Debet,
            Kredit,
            Divisi
        FROM JURNAL
        WHERE KodePerkiraan LIKE ''1%'' AND Kredit > 0
            AND TglTrans BETWEEN @TglAwal AND @TglAkhir
            AND (Divisi = @Divisi OR @Divisi = '''')
        ORDER BY TglTrans, NoBukti',
        1
    );
    PRINT 'Inserted: 20103 Bank Keluar';
END
ELSE
BEGIN
    PRINT 'Skipped: 20103 already exists';
END
GO

-- Jurnal Umum (20109)
IF NOT EXISTS (SELECT 1 FROM dbmasterlaporan WHERE KODEMENU = '20109')
BEGIN
    INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, query_sumber_data, status_aktif)
    VALUES (
        '20109',
        'Jurnal Umum',
        'Laporan jurnal umum dengan filter tanggal dan divisi',
        'SELECT
            TglTrans AS Tanggal,
            NoBukti,
            Uraian,
            KodePerkiraan,
            NamaPerkiraan,
            Debet,
            Kredit,
            Divisi
        FROM JURNAL
        WHERE TglTrans BETWEEN @TglAwal AND @TglAkhir
            AND (Divisi = @Divisi OR @Divisi = '''')
        ORDER BY TglTrans, NoBukti',
        1
    );
    PRINT 'Inserted: 20109 Jurnal Umum';
END
ELSE
BEGIN
    PRINT 'Skipped: 20109 already exists';
END
GO

-- Daftar Perkiraan (20107)
IF NOT EXISTS (SELECT 1 FROM dbmasterlaporan WHERE KODEMENU = '20107')
BEGIN
    INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, query_sumber_data, status_aktif)
    VALUES (
        '20107',
        'Daftar Perkiraan',
        'Daftar perkiraan dengan filter perkiraan',
        'SELECT
            KodePerkiraan,
            NamaPerkiraan,
            Jenis,
            Level,
            Induk
        FROM PERKIRAAN
        WHERE KodePerkiraan LIKE @Perkiraan + ''%''
        ORDER BY KodePerkiraan',
        1
    );
    PRINT 'Inserted: 20107 Daftar Perkiraan';
END
ELSE
BEGIN
    PRINT 'Skipped: 20107 already exists';
END
GO

PRINT '=== Report definitions done! ===';
GO

-- =====================================================
-- 2. Filter Parameters (dbparameterlaporan)
-- Only insert if not exists for this report
-- =====================================================

DECLARE @IdLap INT;

-- Kas Harian (20101)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20101');
IF @IdLap IS NOT NULL AND NOT EXISTS (SELECT 1 FROM dbparameterlaporan WHERE id_laporan = @IdLap)
BEGIN
    INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi)
    VALUES
    (@IdLap, 'TglAwal', 'Tanggal Awal', 'date', 1, CONVERT(VARCHAR(10), DATEADD(DAY, -30, GETDATE()), 23), 1),
    (@IdLap, 'TglAkhir', 'Tanggal Akhir', 'date', 1, CONVERT(VARCHAR(10), GETDATE(), 23), 2),
    (@IdLap, 'Divisi', 'Divisi', 'text', 0, '', 3);
    PRINT 'Inserted parameters for 20101';
END
GO

-- Bank Masuk (20102)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20102');
IF @IdLap IS NOT NULL AND NOT EXISTS (SELECT 1 FROM dbparameterlaporan WHERE id_laporan = @IdLap)
BEGIN
    INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi)
    VALUES
    (@IdLap, 'TglAwal', 'Tanggal Awal', 'date', 1, CONVERT(VARCHAR(10), DATEADD(DAY, -30, GETDATE()), 23), 1),
    (@IdLap, 'TglAkhir', 'Tanggal Akhir', 'date', 1, CONVERT(VARCHAR(10), GETDATE(), 23), 2),
    (@IdLap, 'Divisi', 'Divisi', 'text', 0, '', 3),
    (@IdLap, 'Lokasi', 'Lokasi', 'text', 0, '', 4);
    PRINT 'Inserted parameters for 20102';
END
GO

-- Bank Keluar (20103)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20103');
IF @IdLap IS NOT NULL AND NOT EXISTS (SELECT 1 FROM dbparameterlaporan WHERE id_laporan = @IdLap)
BEGIN
    INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi)
    VALUES
    (@IdLap, 'TglAwal', 'Tanggal Awal', 'date', 1, CONVERT(VARCHAR(10), DATEADD(DAY, -30, GETDATE()), 23), 1),
    (@IdLap, 'TglAkhir', 'Tanggal Akhir', 'date', 1, CONVERT(VARCHAR(10), GETDATE(), 23), 2),
    (@IdLap, 'Divisi', 'Divisi', 'text', 0, '', 3);
    PRINT 'Inserted parameters for 20103';
END
GO

-- Jurnal Umum (20109)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20109');
IF @IdLap IS NOT NULL AND NOT EXISTS (SELECT 1 FROM dbparameterlaporan WHERE id_laporan = @IdLap)
BEGIN
    INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi)
    VALUES
    (@IdLap, 'TglAwal', 'Tanggal Awal', 'date', 1, CONVERT(VARCHAR(10), DATEADD(DAY, -30, GETDATE()), 23), 1),
    (@IdLap, 'TglAkhir', 'Tanggal Akhir', 'date', 1, CONVERT(VARCHAR(10), GETDATE(), 23), 2),
    (@IdLap, 'Divisi', 'Divisi', 'text', 0, '', 3);
    PRINT 'Inserted parameters for 20109';
END
GO

-- Daftar Perkiraan (20107)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20107');
IF @IdLap IS NOT NULL AND NOT EXISTS (SELECT 1 FROM dbparameterlaporan WHERE id_laporan = @IdLap)
BEGIN
    INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi)
    VALUES
    (@IdLap, 'Perkiraan', 'Kode Perkiraan', 'text', 0, '', 1);
    PRINT 'Inserted parameters for 20107';
END
GO

PRINT '=== Filter parameters done! ===';
GO

-- =====================================================
-- 3. Layout Components (dbkomponenlaporan)
-- =====================================================

-- Kas Harian (20101)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20101');
IF @IdLap IS NOT NULL AND NOT EXISTS (SELECT 1 FROM dbkomponenlaporan WHERE id_laporan = @IdLap)
BEGIN
    INSERT INTO dbkomponenlaporan (id_laporan, nama_komponen, tipe_band, urutan_tampil, konfigurasi_layout)
    VALUES
    (@IdLap, 'Header', 'Header', 1, '{"title": "Laporan Kas Harian", "showLogo": true}'),
    (@IdLap, 'TableHeader', 'GroupHeader', 2, '{"columns": ["Tanggal", "No Bukti", "Uraian", "Perkiraan", "Debet", "Kredit"]}'),
    (@IdLap, 'Detail', 'Detail', 3, '{"fields": ["Tanggal", "NoBukti", "Uraian", "NamaPerkiraan", "Debet", "Kredit"]}'),
    (@IdLap, 'Footer', 'Summary', 4, '{"aggregates": ["SUM(Debet)", "SUM(Kredit)"]}');
    PRINT 'Inserted layout for 20101';
END
GO

PRINT '=== All sample data inserted successfully! ===';
GO
