-- Create dbLabelGrup table for grouping label mapping
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'dbLabelGrup'))
BEGIN
    CREATE TABLE dbLabelGrup (
        id INT IDENTITY(1,1) PRIMARY KEY,
        field_name VARCHAR(100),
        field_value VARCHAR(100),
        label VARCHAR(200),
        kode_laporan VARCHAR(50) NULL,
        aktif BIT DEFAULT 1,
        sort_order INT DEFAULT 0
    )

    -- Insert default labels for common grouping
    INSERT INTO dbLabelGrup (field_name, field_value, label, aktif, sort_order)
    VALUES
    ('NoBukti', '', 'Bukti Kas Masuk', 1, 1),
    ('NoBukti', '', 'Bukti Bank Masuk', 1, 2),
    ('NoBukti', '', 'Bukti Kas Keluar', 1, 3),
    ('NoBukti', '', 'Bukti Bank Keluar', 1, 4),
    ('Devisi', '', 'Devisi', 1, 1),
    ('Devisi', '', 'Semua Devisi', 1, 2)
END

PRINT 'Table dbLabelGrup created successfully!'
