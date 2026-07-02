-- =============================================
-- Laporan: Kartu Piutang
-- Kode Menu: 020401
-- Access Code: 20401 (Delphi param = 7)
-- Stored Procedure: Sp_ReportKartuPiutang
-- FR3: ReportKartuPiutang1.fr3
-- Grouping: By kode (customer/piutang)
-- =============================================

-- ===================== DBMENUREPORT =====================
DELETE FROM DBMENUREPORT WHERE KODEMENU = '020401';
INSERT INTO DBMENUREPORT (KODEMENU, Keterangan, L0, ACCESS, OL)
VALUES ('020401', 'Kartu Piutang', 2, 20401, 0);
GO

-- ===================== DBFLMENUREPORT =====================
DELETE FROM DBFLMENUREPORT WHERE L1 = '020401' AND USERID = 'SA';
INSERT INTO DBFLMENUREPORT (USERID, L1, Access, IsDesign, Isexport)
VALUES ('SA', '020401', 1, 1, 1);
GO

-- ===================== dbmasterlaporan =====================
IF EXISTS (SELECT 1 FROM dbmasterlaporan WHERE KODEMENU = '020401')
BEGIN
    DELETE FROM dbkolomlaporan WHERE id_laporan IN (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020401');
    DELETE FROM dbquerylaporan WHERE id_laporan IN (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020401');
    DELETE FROM dbgrouplaporan WHERE id_laporan IN (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020401');
    DELETE FROM dbparameterlaporan WHERE id_laporan IN (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020401');
END
DELETE FROM dbmasterlaporan WHERE KODEMENU = '020401';
INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, query_sumber_data, status_aktif, footer_bands)
VALUES ('020401', 'Kartu Piutang', 'Laporan kartu piutang per customer/piutang berdasarkan stored procedure', NULL, 1, '');
GO

-- ===================== dbquerylaporan =====================
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020401');

DELETE FROM dbquerylaporan WHERE id_laporan = @IdLap;
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan, visible, config_json) VALUES
    -- T1: Running total dataset (Konstanta halaman, variable Nomor, SaldoAkhir)
    -- Berisi konstanta untuk running balance computation
    (@IdLap, 'T1', 'SELECT CAST(0 AS DECIMAL(18,2)) AS SaldoAwal, CAST(0 AS DECIMAL(18,2)) AS Debet, CAST(0 AS DECIMAL(18,2)) AS Kredit, CAST(0 AS DECIMAL(18,2)) AS SaldoAkhir', 'T1 - konstanta running balance (Nomor, SaldoAkhir)', 0, 0, NULL),
    -- T2: Dataset utama dari SP Sp_ReportKartuPiutang
    -- Parameter: 10 positional SP params -> mapped to named @placeholders
    -- :0=TglAwal :1=TglAkhir :2=KodeAwal :3=KodeAkhir :4=Devisi :5=Urut :6=Perkiraan :7=Rekap :8=Valas :9=KodeReport
    -- SP signature:   @awal @akhir @kodesupp @kodesupp1 @devisi @Urut @Perkiraan @rekap @KodeVls @kodereport
    (@IdLap, 'T2', 'EXEC Sp_ReportKartuPiutang @awal, @akhir, @kodesupp, @kodesupp1, @devisi, @Urut, @Perkiraan, @rekap, @KodeVls, @kodereport', 'T2 - Kartu Piutang detail (dari SP)', 1, 1, '{"display_role":"detail","static_params":{"rekap":"0","kodereport":"20401"}}');
GO

-- ===================== dbkolomlaporan =====================
-- Columns from ReportKartuPiutang1.fr3 MasterData1 DataField attributes
-- Exact casing from FR3: NoBukti, kredit1, Tanggal, NoFaktur, debet1, NoRetur, SelisihKurs
-- GroupHeader: Kode, Nama (displayed in group header text)
-- Script: SaldoAkhir (running total accumulator), Nomor (row counter)
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020401');

DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible) VALUES
    -- T1: Running balance fields (displayed via Pascal script variable [SaldoAkhir] and [Nomor])
    (@IdLap, 'T1', 'Nomor', 'No.', 1, 'number', 'right', 0, 0),
    (@IdLap, 'T1', 'SaldoAkhir', 'Saldo Rp.', 8, 'currency', 'right', 0, 0),

    -- T2: Detail columns (from DataField in MasterData1 + PageHeader memos)
    -- Order mengikuti FR3 left-to-right position
    (@IdLap, 'T2', 'Tanggal', 'Tanggal', 2, 'date', 'left', 0, 1),
    (@IdLap, 'T2', 'NoFaktur', 'No.Faktur', 3, 'text', 'left', 0, 1),
    (@IdLap, 'T2', 'NoBukti', 'No.Bukti', 4, 'text', 'left', 0, 1),
    (@IdLap, 'T2', 'NoRetur', 'No.Retur', 5, 'text', 'left', 0, 1),
    (@IdLap, 'T2', 'debet1', 'Debet Rp.', 6, 'currency', 'right', 1, 1),
    (@IdLap, 'T2', 'kredit1', 'Kredit Rp.', 7, 'currency', 'right', 1, 1),
    (@IdLap, 'T2', 'SelisihKurs', 'Selisih Kurs', 8, 'currency', 'right', 0, 1),
    (@IdLap, 'T2', 'SaldoRp', 'Saldo Rp.', 9, 'currency', 'right', 0, 1);
GO

-- ===================== dbgrouplaporan =====================
-- GroupHeader1: Condition = frxDBDataset3."kode"
-- Header text: "[frxDBDataset3."Kode"]  [frxDBDataset3."Nama"]"
-- GroupFooter: Sub Total
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020401');

DELETE FROM dbgrouplaporan WHERE id_laporan = @IdLap;
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, sort_order, show_subtotal)
VALUES (@IdLap, 1, 'kode', '', 'Sub Total', 1, 0);
GO

-- ===================== dbparameterlaporan =====================
-- Delphi param = 7: date range + party filter
-- Parameter mapping: FR3 Pascal variable -> SP EXEC parameter names
--   :0=TglAwal8.Date  -> @awal
--   :1=TglAkhir8.Date -> @akhir
--   :2=Awal8.Text     -> @kodesupp  (customer code awal)
--   :3=Akhir8.Text    -> @kodesupp1 (customer code akhir)
--   :4=Devisi8.Text   -> @devisi
--   :5=Urut8.ItemIndex-> @Urut
--   :6=Perkiraan8.Text -> @Perkiraan (piutang account)
--   :7=0              -> @rekap     (hardcoded in config_json static_params)
--   :8=Valas8.ItemIndex -> @KodeVls (currency code, default IDR)
--   :9=kodereport     -> @kodereport (hardcoded in config_json static_params)
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020401');

DELETE FROM dbparameterlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi, konfigurasi) VALUES
    -- nama_filter harus SAMA persis dengan parameter SP (case-sensitive di SQL Server)
    (@IdLap, 'awal', 'Tanggal Awal', 'date', 1, '', 1, ''),
    (@IdLap, 'akhir', 'Tanggal Akhir', 'date', 1, '', 1, ''),
    (@IdLap, 'kodesupp', 'Customer Awal', 'browse', 0, '', 2, '{"kode_browse":"1014","mode":"single","parent_filters":[{"source":"Perkiraan","target":"Perkiraan"}]}'),
    (@IdLap, 'kodesupp1', 'Customer Akhir', 'browse', 0, '', 2, '{"kode_browse":"1014","mode":"single","parent_filters":[{"source":"Perkiraan","target":"Perkiraan"}]}'),
    (@IdLap, 'devisi', 'Divisi', 'browse', 0, '', 2, '{"kode_browse":"1004","mode":"single"}'),
    (@IdLap, 'Urut', 'Urutkan Berdasarkan', 'select', 0, '0', 2, ''),
    (@IdLap, 'Perkiraan', 'Perkiraan / Customer', 'browse', 0, '', 1, '{"kode_browse":"100408","mode":"single"}'),
    (@IdLap, 'KodeVls', 'Mata Uang', 'select', 0, 'IDR', 3, '');
GO

SELECT 'OK' AS status, 'Kartu Piutang 020401 seeded' AS message;
GO
