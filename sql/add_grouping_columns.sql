-- =====================================================
-- Add generic grouping configuration columns to dbgrouplaporan
-- Purpose: Make grouping database-driven, remove hardcoded patterns
-- =====================================================

-- Add special_handling column (strategy type)
IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'dbgrouplaporan' AND COLUMN_NAME = 'special_handling')
BEGIN
    ALTER TABLE dbgrouplaporan ADD special_handling VARCHAR(50) NULL DEFAULT 'default'
    PRINT 'Added: special_handling column to dbgrouplaporan'
END

-- Add config_json column (JSON config for special handling)
IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'dbgrouplaporan' AND COLUMN_NAME = 'config_json')
BEGIN
    ALTER TABLE dbgrouplaporan ADD config_json NVARCHAR(MAX) NULL
    PRINT 'Added: config_json column to dbgrouplaporan'
END

GO

-- =====================================================
-- Update existing grouping config for buku tambahan (202021)
-- =====================================================
DECLARE @ID_LAPORAN INT
SET @ID_LAPORAN = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '202021')

IF @ID_LAPORAN IS NOT NULL
BEGIN
    -- Update first group (NoACC) to use running-balance
    UPDATE dbgrouplaporan
    SET special_handling = 'running-balance',
        config_json = '{"balanceColumn": "SaldoAkhir", "startRowMarker": "SALDO AWAL", "markerColumn": "Nobukti"}'
    WHERE id_laporan = @ID_LAPORAN AND group_field = 'NoACC' AND group_level = 1

    PRINT 'Updated: dbgrouplaporan for 202021 (buku tambahan) - running-balance'
END

GO

-- =====================================================
-- Update existing grouping config for neraca reports
-- =====================================================

-- Update all reports that group by Kel or grupAP2 to use category-label
UPDATE dbgrouplaporan
SET special_handling = 'category-label'
WHERE id_laporan IN (
    SELECT DISTINCT id_laporan FROM dbgrouplaporan
    WHERE group_field IN ('Kel', 'grupAP2', 'grupAP1')
)
AND (special_handling IS NULL OR special_handling = 'default')

PRINT 'Updated: dbgrouplaporan for neraca reports - category-label'

GO

-- =====================================================
-- Verify changes
-- =====================================================
SELECT 'dbgrouplaporan with new columns' as info,
       id_group,
       group_field,
       special_handling,
       config_json
FROM dbgrouplaporan
WHERE special_handling IS NOT NULL
ORDER BY id_laporan, group_level, sort_order

PRINT 'Migration completed: Generic grouping configuration added!'