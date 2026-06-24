-- Schema: dbnotatemplate
-- Stores print templates for Nota documents (Nota Jual, Nota PO, Nota Beli, etc.)
-- Pattern: 1 master template + JSON config drives Blade rendering

USE dbwbcp2;
GO

IF NOT EXISTS (SELECT 1 FROM sysobjects WHERE name='dbnotatemplate' AND xtype='U')
BEGIN
    CREATE TABLE dbnotatemplate (
        id_template INT IDENTITY(1,1) PRIMARY KEY,
        kode_nota VARCHAR(50) UNIQUE NOT NULL,
        nama_nota VARCHAR(100) NOT NULL,
        paper_size VARCHAR(20) DEFAULT 'A4',
        orientation VARCHAR(20) DEFAULT 'portrait',
        margins VARCHAR(20) DEFAULT '10mm',
        font_family VARCHAR(50) DEFAULT 'Tahoma',
        font_size VARCHAR(10) DEFAULT '10pt',
        config_json NVARCHAR(MAX) NOT NULL,
        query_header NVARCHAR(MAX) NULL,
        query_detail NVARCHAR(MAX) NULL,
        query_params NVARCHAR(MAX) NULL,
        aktif BIT DEFAULT 1,
        created_at DATETIME DEFAULT GETDATE(),
        updated_at DATETIME DEFAULT GETDATE()
    );

    PRINT 'dbnotatemplate created';
END
ELSE
BEGIN
    PRINT 'dbnotatemplate already exists';
END
GO
