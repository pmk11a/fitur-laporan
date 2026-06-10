-- ============================================================
-- MIGRATION: Tambah kolom `visible` ke dbquerylaporan
-- Tujuannya: menyembunyikan/menampilkan dataset tertentu per-laporan
--            tanpa menghapus baris (soft-toggle).
-- Default 1 (visible) agar baris lama tidak ikut tersembunyi.
-- ============================================================

USE dbwbcp2;
GO

-- Step 1: Tambah kolom `visible` (BIT, NOT NULL, default 1)
IF NOT EXISTS (
    SELECT 1
    FROM sys.columns
    WHERE object_id = OBJECT_ID(N'dbquerylaporan')
      AND name = 'visible'
)
BEGIN
    ALTER TABLE dbquerylaporan
        ADD visible BIT NOT NULL
            CONSTRAINT DF_dbquerylaporan_visible DEFAULT (1);
    PRINT 'Kolom `visible` berhasil ditambahkan ke dbquerylaporan (default 1)';
END
ELSE
BEGIN
    PRINT 'Kolom `visible` sudah ada - dilewati';
END
GO

-- Step 2: Verify
SELECT
    c.name AS column_name,
    t.name AS data_type,
    c.is_nullable
FROM sys.columns c
JOIN sys.types t ON c.user_type_id = t.user_type_id
WHERE c.object_id = OBJECT_ID(N'dbquerylaporan')
  AND c.name = 'visible';
GO
