USE dbwbcp2;
GO

-- =====================================================
-- User Access: SA -> Report 101 (Daftar Perkiraan)
-- =====================================================

IF NOT EXISTS (SELECT 1 FROM dbflmenureport WHERE USERID = 'SA' AND L1 = '101')
BEGIN
    INSERT INTO dbflmenureport (USERID, L1, Access)
    VALUES ('SA', '101', 1);
    PRINT 'Inserted: dbflmenureport SA -> 101';
END
ELSE
BEGIN
    PRINT 'Skipped: dbflmenureport SA -> 101 already exists';
END
GO

PRINT '';
PRINT '=== SA now has access to report 101 (Daftar Perkiraan) ===';
GO
