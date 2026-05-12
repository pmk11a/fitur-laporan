-- =====================================================
-- Fluffy Bee Report Engine - Database Setup
-- Execute this in SQL Server Management Studio (SSMS)
-- Database: dbwbcp2 (sama dengan tabel ERP)
-- =====================================================

-- Use database
USE dbwbcp2;
GO

-- =====================================================
-- TABLE: dbmasterlaporan (Master report definitions)
-- =====================================================
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = 'dbmasterlaporan' AND xtype = 'U')
BEGIN
    CREATE TABLE dbmasterlaporan (
        id_laporan INT IDENTITY(1,1) PRIMARY KEY,
        KODEMENU VARCHAR(20) NOT NULL,
        nama_laporan VARCHAR(200) NOT NULL,
        deskripsi VARCHAR(500) NULL,
        query_sumber_data TEXT NULL,
        status_aktif BIT DEFAULT 1,
        created_at DATETIME DEFAULT GETDATE(),
        updated_at DATETIME DEFAULT GETDATE()
    );

    CREATE INDEX IX_dbmasterlaporan_KODEMENU ON dbmasterlaporan(KODEMENU);
END
GO

-- =====================================================
-- TABLE: dbparameterlaporan (Report filter parameters)
-- =====================================================
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = 'dbparameterlaporan' AND xtype = 'U')
BEGIN
    CREATE TABLE dbparameterlaporan (
        id_parameter INT IDENTITY(1,1) PRIMARY KEY,
        id_laporan INT NOT NULL,
        nama_filter VARCHAR(100) NOT NULL,
        label VARCHAR(100) NOT NULL,
        tipe_input VARCHAR(50) NOT NULL,  -- 'date', 'text', 'number', 'combobox'
        wajib_isi BIT DEFAULT 0,
        nilai_default VARCHAR(200) NULL,
        posisi INT DEFAULT 0,
        konfigurasi VARCHAR(MAX) NULL,
        created_at DATETIME DEFAULT GETDATE()
    );

    CREATE INDEX IX_dbparameterlaporan_ID ON dbparameterlaporan(id_laporan);
END
ELSE
BEGIN
    -- Add missing columns if they don't exist
    IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('dbparameterlaporan') AND name = 'label')
    BEGIN
        ALTER TABLE dbparameterlaporan ADD label VARCHAR(100) NULL;
    END
END
GO

-- =====================================================
-- TABLE: dbkomponenlaporan (Report layout components)
-- =====================================================
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = 'dbkomponenlaporan' AND xtype = 'U')
BEGIN
    CREATE TABLE dbkomponenlaporan (
        id_komponen INT IDENTITY(1,1) PRIMARY KEY,
        id_laporan INT NOT NULL,
        nama_komponen VARCHAR(100) NOT NULL,
        tipe_band VARCHAR(50) NOT NULL,  -- 'Header', 'Detail', 'Footer', 'GroupHeader', 'Summary'
        urutan_tampil INT DEFAULT 0,
        konfigurasi_layout VARCHAR(MAX) NULL,
        created_at DATETIME DEFAULT GETDATE()
    );

    CREATE INDEX IX_dbkomponenlaporan_ID ON dbkomponenlaporan(id_laporan);
END
GO

-- =====================================================
-- TABLE: dbmenureport (Menu Definitions)
-- =====================================================
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = 'dbmenureport' AND xtype = 'U')
BEGIN
    CREATE TABLE dbmenureport (
        KODEMENU VARCHAR(20) PRIMARY KEY,
        Keterangan VARCHAR(200) NOT NULL,
        L0 INT DEFAULT 1,
        ACCESS INT DEFAULT 0,
        OL VARCHAR(50) NULL
    );
END
GO

-- =====================================================
-- TABLE: dbflmenureport (User Report Access)
-- =====================================================
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = 'dbflmenureport' AND xtype = 'U')
BEGIN
    CREATE TABLE dbflmenureport (
        USERID VARCHAR(50) NOT NULL,
        L1 VARCHAR(20) NOT NULL,
        Access BIT DEFAULT 1,
        IsDesign BIT DEFAULT 0,
        IsExport BIT DEFAULT 0,
        PRIMARY KEY (USERID, L1)
    );

    CREATE INDEX IX_dbflmenureport_USERID ON dbflmenureport(USERID);
END
GO

PRINT 'All tables created successfully in dbwbcp2!';
GO
