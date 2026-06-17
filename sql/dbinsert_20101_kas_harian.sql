-- =====================================================
-- Update 020101 (Kas Harian) - T1 config + footer_table
-- Derives formulas from ReportKasHarian.fr3 PascalScript
-- =====================================================

DECLARE @id_laporan VARCHAR(10);
SET @id_laporan = (SELECT TOP 1 id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020101');

IF @id_laporan IS NULL
BEGIN
    PRINT 'ERROR: No report found with KODEMENU=020101';
    ROLLBACK;
    RETURN;
END

PRINT 'Found report: ' + @id_laporan;
PRINT '';

-- =====================================================
-- 1. UPDATE T1 config_json
-- =====================================================
-- Formulas derived from ReportKasHarian.fr3 Footer1OnBeforePrint:
--   SaldoAwalD = SaldoAwal
--   SaldoAkhirD = 0
--   TotalD = sum(debet) + SaldoAwalD + SaldoAkhirD
--   TotalK = sum(kredit) + SaldoAwalK + SaldoAkhirK
--   Tunai = sum(debet+debet2) + SaldoAwalD - sum(kredit+kredit2) - (SaldoGiro+SaldoBon+SaldoBonD+SaldoBonE+SaldoBonA+SaldoBonDH)
--
-- Note: T2 has 4 numeric columns (Debet, Debet2, kredit, kredit2) unlike 020102 (2 columns)
-- =====================================================
UPDATE dbquerylaporan
SET config_json = '{"display_role":"summary","summary_fields":["SaldoAwal","SaldoGiro","SaldoBon","SaldoBonD","SaldoBonE","SaldoBonA","SaldoBonDH","SaldoAwalD","SaldoAwalK","SaldoAkhirD","SaldoAkhirK","TotalD","TotalK","Tunai"],"right_fields":[],"t2_sum_fields":["Debet","Debet2","kredit","kredit2"],"bon_giro_fields":["SaldoGiro","SaldoBon","SaldoBonD","SaldoBonE","SaldoBonA","SaldoBonDH"],"computed":{"SaldoAwalD":{"expression":"SaldoAwal","operands":{"SaldoAwal":"t1"}},"SaldoAwalK":{"expression":"0","operands":{}},"SaldoAkhirD":{"expression":"0","operands":{}},"SaldoAkhirK":{"expression":"SaldoAwal + sum(Debet) + sum(Debet2) - sum(kredit) - sum(kredit2)","operands":{"SaldoAwal":"t1","Debet":"sum:t2","Debet2":"sum:t2","kredit":"sum:t2","kredit2":"sum:t2"}},"TotalD":{"expression":"sum(Debet) + sum(Debet2) + SaldoAwal + 0","operands":{"Debet":"sum:t2","Debet2":"sum:t2","SaldoAwal":"t1"}},"TotalK":{"expression":"sum(kredit) + sum(kredit2) + 0 + (SaldoAwal + sum(Debet) + sum(Debet2) - sum(kredit) - sum(kredit2))","operands":{"kredit":"sum:t2","kredit2":"sum:t2","SaldoAwal":"t1","Debet":"sum:t2","Debet2":"sum:t2"}},"Tunai":{"expression":"sum(Debet) + sum(Debet2) + SaldoAwal - sum(kredit) - sum(kredit2) - (SaldoGiro + SaldoBon + SaldoBonD + SaldoBonE + SaldoBonA + SaldoBonDH)","operands":{"Debet":"sum:t2","Debet2":"sum:t2","SaldoAwal":"t1","kredit":"sum:t2","kredit2":"sum:t2","SaldoGiro":"t1","SaldoBon":"t1","SaldoBonD":"t1","SaldoBonE":"t1","SaldoBonA":"t1","SaldoBonDH":"t1"}}},"summary_layout":"footer_only"}'
WHERE id_laporan = @id_laporan AND nama_dataset = 'T1';

PRINT 'Updated T1 config: ' + CAST(@@ROWCOUNT AS VARCHAR) + ' rows';

-- =====================================================
-- 2. UPDATE footer_bands (add footer_table + columns)
-- =====================================================
-- Footer layout: 4 columns (TUNAI D | CH/GB D | TUNAI K | CH/GB K)
-- Rows: Sub Jumlah, Jumlah, Saldo Awal, Saldo Akhir, Kontrol
-- =====================================================
UPDATE dbmasterlaporan
SET footer_bands = N'{
    "bands": {
        "title": {"enabled": true, "content": "LAPORAN KAS", "align": "center"},
        "pageHeader": {"enabled": true, "content": "Kas Harian"},
        "summary": {
            "enabled": true,
            "layout": {"columns": 3, "alignment": "spread"},
            "signatures": [
                {"label": "Kontrol", "position": "left"},
                {"label": "Kasir", "position": "center"},
                {"label": "Pimpinan", "position": "right"}
            ],
            "columns": [
                {"label": "TUNAI", "col_key": "debet"},
                {"label": "CH/GB", "col_key": "debet2"},
                {"label": "TUNAI", "col_key": "kredit"},
                {"label": "CH/GB", "col_key": "kredit2"}
            ],
            "footer_table": {
                "rows": [
                    {"label": "Sub Jumlah", "data_source": "sum", "fields": {"debet": "Debet+Debet2", "debet2": "0", "kredit": "kredit+kredit2", "kredit2": "0"}},
                    {"label": "Jumlah", "data_source": "sum", "fields": {"debet": "Debet+Debet2", "debet2": "0", "kredit": "kredit+kredit2", "kredit2": "0"}},
                    {"label": "Saldo Awal", "data_source": "t1", "fields": {"debet": "SaldoAwalD", "debet2": "0", "kredit": "0", "kredit2": "0"}},
                    {"label": "Saldo Akhir", "data_source": "t1", "fields": {"debet": "0", "debet2": "0", "kredit": "SaldoAkhirK", "kredit2": "0"}},
                    {"label": "Kontrol", "data_source": "computed", "fields": {"debet": "TotalD", "debet2": "0", "kredit": "TotalK", "kredit2": "0"}}
                ]
            }
        }
    }
}'
WHERE KODEMENU = '020101';

PRINT 'Updated footer_bands: ' + CAST(@@ROWCOUNT AS VARCHAR) + ' rows';

SELECT 'OK' AS status, COUNT(*) AS rows_affected FROM dbquerylaporan WHERE id_laporan = @id_laporan;
SELECT 'OK' AS status, COUNT(*) AS footer_updated FROM dbmasterlaporan WHERE KODEMENU = '020101';
GO
