-- =============================================
-- Laporan: Hasil Produksi Per No Bukti (Detail)
-- Kode Menu: 040853
-- Access Code: 40853 (Delphi param = 11)
-- Stored Procedure: Sp_ReportPRDDet
-- FR3: ReportPrdPerNobukti.fr3
-- Grouping: By NOBUKTI, then mykey
-- =============================================

-- ===================== DBMENUREPORT =====================
DELETE FROM DBMENUREPORT WHERE KODEMENU = '040853';
INSERT INTO DBMENUREPORT (KODEMENU, Keterangan, L0, ACCESS, OL)
VALUES ('040853', 'Hasil Produksi Per No Bukti', 4, 40853, 0);
GO

-- ===================== DBFLMENUREPORT =====================
DELETE FROM DBFLMENUREPORT WHERE L1 = '040853' AND USERID = 'SA';
INSERT INTO DBFLMENUREPORT (USERID, L1, Access, IsDesign, Isexport)
VALUES ('SA', '040853', 1, 1, 1);
GO

-- ===================== dbmasterlaporan =====================
IF EXISTS (SELECT 1 FROM dbmasterlaporan WHERE KODEMENU = '040853')
BEGIN
    DELETE FROM dbkolomlaporan WHERE id_laporan IN (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '040853');
    DELETE FROM dbquerylaporan WHERE id_laporan IN (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '040853');
    DELETE FROM dbgrouplaporan WHERE id_laporan IN (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '040853');
    DELETE FROM dbparameterlaporan WHERE id_laporan IN (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '040853');
END
DELETE FROM dbmasterlaporan WHERE KODEMENU = '040853';
INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, query_sumber_data, status_aktif, footer_bands)
VALUES ('040853', 'Hasil Produksi Per No Bukti', 'Laporan hasil produksi per no bukti berdasarkan stored procedure Sp_ReportPRDDet', NULL, 1, '');
GO

-- ===================== dbquerylaporan =====================
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '040853');

DELETE FROM dbquerylaporan WHERE id_laporan = @IdLap;
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan, visible, config_json) VALUES
    -- T1: Detail dataset utama dari SP Sp_ReportPRDDet
    -- Parameter mapping: :0='T'(detail), :1='N'(no Bukti), :2=TglAwal, :3=TglAkhir, :4='', :5=CboOto(ItemIndex)
    -- SP signature: Sp_ReportPRDDet @type, @subType, @tglAwal, @tglAkhir, @groupFilter, @sortOrder
    (@IdLap, 'T1', 'EXEC Sp_ReportPRDDet @type, @subType, @tglAwal, @tglAkhir, @groupFilter, @sortOrder', 'T1 - Hasil Produksi Per No Bukti Detail (dari SP)', 1, 1, NULL);
GO

-- ===================== dbkolomlaporan =====================
-- Columns from ReportPrdPerNobukti.fr3
-- MasterData1 DataFields: JamProduksi, Ket, Nik, jamtenaker, rpmesin
-- PageHeader refs: NOBUKTI, Tanggal, KODEBRG
-- GroupHeader refs: NOBUKTI, mykey
-- Expr refs: nospk, rptenaker
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '040853');

DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible) VALUES
    -- PageHeader (no detail columns for header - handled in Vue template or hardcoded label)
    -- NOBUKTI: used in PageHeader + GroupHeader for sorting/display
    (@IdLap, 'T1', 'NOBUKTI', 'No.Bukti', 1, 'text', 'left', 0, 1),
    -- Tanggal: Transaction date
    (@IdLap, 'T1', 'Tanggal', 'Tanggal', 2, 'date', 'left', 0, 1),
    -- KODEBRG: Product code from PageHeader
    (@IdLap, 'T1', 'KODEBRG', 'Kode Barang', 3, 'text', 'left', 0, 1),
    -- MasterData1 DataFields
    (@IdLap, 'T1', 'nospk', 'No.SPK', 4, 'text', 'left', 0, 1),
    -- nik: Operator ID
    (@IdLap, 'T1', 'Nik', 'NIK', 5, 'text', 'left', 0, 1),
    -- jamtenaker: Labor time
    (@IdLap, 'T1', 'jamtenaker', 'Jam Tenaga', 6, 'number', 'right', 0, 1),
    -- Ket: Keterangan
    (@IdLap, 'T1', 'Ket', 'Keterangan', 7, 'text', 'left', 0, 1),
    -- JamProduksi: Production hours
    (@IdLap, 'T1', 'JamProduksi', 'Jam Produksi', 8, 'number', 'right', 0, 1),
    -- rpmesin: Machine RPM
    (@IdLap, 'T1', 'rpmesin', 'RPM Mesin', 9, 'number', 'right', 0, 1),
    -- rptenaker: Labor report
    (@IdLap, 'T1', 'rptenaker', 'Report Tenaga', 10, 'text', 'left', 0, 1);
GO

-- ===================== dbgrouplaporan =====================
-- GroupHeader1: Condition = frxDBData."NOBUKTI"
-- GroupHeader2: Condition = frxDBData."mykey"
-- Header text uses [frxDBData."NOBUKTI"] and [frxDBData."KODEBRG"]
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '040853');

DELETE FROM dbgrouplaporan WHERE id_laporan = @IdLap;
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, sort_order, show_subtotal)
VALUES (@IdLap, 1, 'NOBUKTI', '', 'No. Bukti', 1, 0),
       (@IdLap, 2, 'mykey', '', 'Detail', 2, 0);
GO

-- ===================== dbparameterlaporan =====================
-- Delphi param = 11: date range + CboOto (sort order)
-- Parameters: :0='T' (detail type), :1='N' (by No.Bukti), :2=TglAwal, :3=TglAkhir, :4='', :5=CboOto.ItemIndex
-- No browse filter (no party selection) — only date range
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '040853');

DELETE FROM dbparameterlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi, konfigurasi) VALUES
    (@IdLap, 'tglAwal', 'Tanggal Awal', 'date', 1, '', 1, ''),
    (@IdLap, 'tglAkhir', 'Tanggal Akhir', 'date', 1, '', 1, ''),
    (@IdLap, 'subType', 'Klasifikasi', 'select', 0, 'N', 2, ''),
    (@IdLap, 'type', 'Tipe', 'select', 0, 'T', 2, ''),
    (@IdLap, 'sortOrder', 'Urutkan Berdasarkan', 'select', 0, '0', 3, '');
GO

SELECT 'OK' AS status, 'Hasil Produksi Per No Bukti 040853 seeded' AS message;
GO
