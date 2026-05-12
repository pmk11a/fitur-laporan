-- =====================================================
-- Fluffy Bee Report Engine - Multi-Dataset Tables
-- Execute in SSMS on dbwbcp2 database
-- =====================================================

USE dbwbcp2;
GO

-- =====================================================
-- TABLE: dbquerylaporan (Multi-dataset queries)
-- =====================================================
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = 'dbquerylaporan' AND xtype = 'U')
BEGIN
    CREATE TABLE dbquerylaporan (
        id_query INT IDENTITY(1,1) PRIMARY KEY,
        id_laporan INT NOT NULL,
        nama_dataset VARCHAR(50) NOT NULL,
        query_sumber_data TEXT NOT NULL,
        deskripsi VARCHAR(200) NULL,
        urutan INT DEFAULT 1
    );

    CREATE INDEX IX_dbquerylaporan_LAPORAN ON dbquerylaporan(id_laporan);
    CREATE INDEX IX_dbquerylaporan_DATASET ON dbquerylaporan(id_laporan, nama_dataset);

    PRINT 'Created: dbquerylaporan';
END
GO

-- =====================================================
-- TABLE: dbkolomlaporan (Column visibility & format)
-- =====================================================
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = 'dbkolomlaporan' AND xtype = 'U')
BEGIN
    CREATE TABLE dbkolomlaporan (
        id_kolom INT IDENTITY(1,1) PRIMARY KEY,
        id_laporan INT NOT NULL,
        nama_dataset VARCHAR(50) NOT NULL,
        nama_kolom VARCHAR(100) NOT NULL,
        label_tampil VARCHAR(100) NOT NULL,
        urutan_tampil INT DEFAULT 0,
        format_type VARCHAR(50) DEFAULT 'text',
        alignment VARCHAR(20) DEFAULT 'left',
        is_summable BIT DEFAULT 0,
        is_visible BIT DEFAULT 1
    );

    CREATE INDEX IX_dbkolomlaporan_LAPORAN ON dbkolomlaporan(id_laporan, nama_dataset);

    PRINT 'Created: dbkolomlaporan';
END
GO

-- =====================================================
-- TABLE: dbgrouplaporan (Group config for hierarchical)
-- =====================================================
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = 'dbgrouplaporan' AND xtype = 'U')
BEGIN
    CREATE TABLE dbgrouplaporan (
        id_group INT IDENTITY(1,1) PRIMARY KEY,
        id_laporan INT NOT NULL,
        group_level INT DEFAULT 1,
        group_field VARCHAR(100) NOT NULL,
        field_value VARCHAR(50) NOT NULL,
        label VARCHAR(200) NOT NULL,
        sort_order INT DEFAULT 0,
        show_subtotal BIT DEFAULT 1,
        style_config VARCHAR(MAX) NULL
    );

    CREATE INDEX IX_dbgrouplaporan_LAPORAN ON dbgrouplaporan(id_laporan);
    CREATE INDEX IX_dbgrouplaporan_LEVEL ON dbgrouplaporan(id_laporan, group_level);

    PRINT 'Created: dbgrouplaporan';
END
GO

PRINT '';
PRINT '=== All tables created successfully! ===';
GO

-- =====================================================
-- Sample Data: Neraca (KODEMENU: 102)
-- =====================================================

-- 1. Master Laporan
IF NOT EXISTS (SELECT 1 FROM dbmasterlaporan WHERE KODEMENU = '102')
BEGIN
    INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, status_aktif)
    VALUES ('102', 'Neraca', 'Neraca Keuangan', 1);
    PRINT 'Inserted: dbmasterlaporan for Neraca (102)';
END
ELSE
BEGIN
    PRINT 'Skipped: dbmasterlaporan 102 already exists';
END
GO

DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '102');

-- 2. Queries (stored procedure dengan parameter)
IF NOT EXISTS (SELECT 1 FROM dbquerylaporan WHERE id_laporan = @IdLap AND nama_dataset = 'QuView3')
BEGIN
    INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan)
    VALUES (@IdLap, 'QuView3', 'EXEC sp_ReportNeracaAktiva @Divisi, @Bulan, @Tahun', 'Neraca Aktiva', 1);
    PRINT 'Inserted: dbquerylaporan QuView3';
END

IF NOT EXISTS (SELECT 1 FROM dbquerylaporan WHERE id_laporan = @IdLap AND nama_dataset = 'QuView4')
BEGIN
    INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan)
    VALUES (@IdLap, 'QuView4', 'EXEC sp_ReportNeracaPasiva @Divisi, @Bulan, @Tahun', 'Neraca Pasiva', 2);
    PRINT 'Inserted: dbquerylaporan QuView4';
END
GO

-- 3. Filter Parameters
IF NOT EXISTS (SELECT 1 FROM dbparameterlaporan WHERE id_laporan = @IdLap AND nama_filter = 'Divisi')
BEGIN
    INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi)
    VALUES
    (@IdLap, 'Divisi', 'Divisi', 'text', 0, '', 1),
    (@IdLap, 'Bulan', 'Bulan', 'number', 1, '1', 2),
    (@IdLap, 'Tahun', 'Tahun', 'number', 1, '2024', 3);
    PRINT 'Inserted: dbparameterlaporan for Neraca';
END
GO

-- 4. Grouping Config
DELETE FROM dbgrouplaporan WHERE id_laporan = @IdLap;

-- Level 1: Parent Groups (AKTIVA, KEWAJIBAN)
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, sort_order, show_subtotal, style_config)
VALUES
(@IdLap, 1, 'grupAP1', 'A', 'AKTIVA', 1, 1, '{"bold": true, "fontSize": 14}'),
(@IdLap, 1, 'grupAP1', 'P', 'KEWAJIBAN DAN MODAL', 2, 1, '{"bold": true, "fontSize": 14}');

-- Level 2: Child Groups (AKTIVA LANCAR, AKTIVA TETAP, dll)
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, sort_order, show_subtotal, style_config)
VALUES
-- Aktiva Groups
(@IdLap, 2, 'grupAP2', 'A1', 'AKTIVA LANCAR', 1, 1, '{"bold": true, "bgColor": "#e8f4f8"}'),
(@IdLap, 2, 'grupAP2', 'A2', 'AKTIVA TETAP', 2, 1, '{"bold": true, "bgColor": "#e8f4f8"}'),
(@IdLap, 2, 'grupAP2', 'A3', 'AKTIVA LAIN-LAIN', 3, 1, '{"bold": true, "bgColor": "#e8f4f8"}'),
(@IdLap, 2, 'grupAP2', 'A4', 'INVESTASI & AKUMULASI PENYUSUTAN', 4, 1, '{"bold": true, "bgColor": "#e8f4f8"}'),
-- Pasiva Groups
(@IdLap, 2, 'grupAP2', 'P1', 'KEWAJIBAN LANCAR', 5, 1, '{"bold": true, "bgColor": "#f8e8e8"}'),
(@IdLap, 2, 'grupAP2', 'P2', 'KEWAJIBAN TIDAK LANCAR', 6, 1, '{"bold": true, "bgColor": "#f8e8e8"}'),
(@IdLap, 2, 'grupAP2', 'P3', 'EKUITAS (MODAL)', 7, 1, '{"bold": true, "bgColor": "#f8e8e8"}');

PRINT 'Inserted: dbgrouplaporan for Neraca';
GO

-- 5. Column Config
DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible)
VALUES
(@IdLap, 'QuView3', 'keterangan', 'Keterangan', 1, 'text', 'left', 0, 1),
(@IdLap, 'QuView3', 'grupAP1', '', 0, 'text', 'left', 0, 0),  -- hidden grouping field
(@IdLap, 'QuView3', 'grupAP2', '', 0, 'text', 'left', 0, 0),  -- hidden grouping field
(@IdLap, 'QuView3', 'jumlah1', 'Jumlah', 2, 'currency', 'right', 1, 1),
(@IdLap, 'QuView3', 'jumlah2', 'Bulan Lalu', 3, 'currency', 'right', 1, 1);

PRINT 'Inserted: dbkolomlaporan for Neraca';
GO

PRINT '';
PRINT '=== Neraca report setup complete! ===';
PRINT 'To test: Open http://localhost:3000/reports/102';
GO