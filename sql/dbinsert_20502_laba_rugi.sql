-- ============================================================
-- MIGRATION: Laporan Laba Rugi (020502)
-- Delphi: ShowReportPreview(' Laba Rugi',2)
-- FR3:    ReportLabaRugi.fr3
-- SP:     sp_ReportLabaRugi (single dataset frxDBDataset3)
-- Type:   single-dataset detail + summary (3-column amounts)
--
-- Strategi: DELETE dulu, baru INSERT fresh (idempotent reset)
-- Tabel: dbmasterlaporan, dbquerylaporan, dbkolomlaporan,
--        dbparameterlaporan, DBMENUREPORT, DBFLMENUREPORT
--
-- PENTING: Setiap `GO` membuat batch baru di SQL Server.
-- Variabel lokal (@IdLap) harus dideklarasikan ulang
-- di setiap batch.
--
-- PENTING: dbmenreport & dbflmenureport TIDAK diubah
-- (lihat memory: no-modify-menu-report-tables)
-- ============================================================

-- =============================================
-- 1. dbmasterlaporan - reset + insert
-- =============================================
DELETE FROM dbmasterlaporan WHERE KODEMENU = '020502';
INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, query_sumber_data, status_aktif, footer_bands)
VALUES ('020502', 'Laba Rugi', 'Profit and Loss Statement - Bulan Lalu / Bulan Ini / S/d Bulan Ini', NULL, 1,
'{"bands":{"title":{"enabled":true,"content":"L A P O R A N   L A B A   R U G I","align":"center"},"pageHeader":{"enabled":true,"content":"[Perusahaan Name] - Periode: [Bulan]/[Tahun]"},"pageFooter":{"enabled":true,"content":"Halaman [Page] dari [TotalPages#]"},"summary":{"enabled":false}}}');
GO

-- =============================================
-- 2. dbquerylaporan - single dataset QuView3
--    SP params: @Bulan, @Tahun, @Devisi (dari frontend filters), @prosesRlHpp(bit), @jumlahA/B/C(numeric)
--    @prosesRlHpp=0 (bit default), @jumlahA/B/C=NULL (tidak dikirim frontend, ambil default dari SP)
-- =============================================
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020502');

DELETE FROM dbquerylaporan WHERE id_laporan = @IdLap;
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan, visible, config_json) VALUES
    (@IdLap, 'QuView3', 'EXEC sp_ReportLabaRugi @Bulan, @Tahun, @Devisi, 0, NULL, NULL, NULL', 'Dataset Laba Rugi detail + summary', 1, 1, '{"display_role":"detail"}');
GO

-- =============================================
-- 3. dbkolomlaporan
--    nama_kolom HARUS case-sensitive match dengan field SP sp_ReportLabaRugi.
--
--    FR3 MasterData1 columns (ordered by Left position):
--      Memo8    → DataField=keterangan     (left align, text)
--      frxDBDataset3perkiraan → DataField=prk (left, text)
--      Memo19   → [r1] = TotalA (Bulan Lalu) — fkNumeric/%2.0n → currency
--      Memo11   → [r2] = TotalB (Bulan Ini)  — fkNumeric/%2.0n → currency
--      Memo20   → [r3] = TotalC (S/d Bulan Ini) — fkNumeric/%2.0n → currency
--
--    Script references (expressions used in BeforePrint):
--      jumlah,TotalA,TotalB,TotalC,perkiraan,grup,Bulan,Tahun,IsLRHPP,
--      JumlahA,JumlahB,JumlahC,tanda,tipe
--
--    Hidden fields (for script logic only):
--      Bulan,Tahun,jumlah,grup,tipe,tanda,IsLRHPP,JumlahA,JumlahB,JumlahC
--    Visible display columns:
--      prk, keterangan, TotalA, TotalB, TotalC
-- =============================================
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020502');

DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible) VALUES
    -- Visible display columns (order matches .fr3 Left position)
    (@IdLap, 'QuView3', 'prk',         'Perkiraan',  1, 'text',    'left',  0, 1),
    (@IdLap, 'QuView3', 'keterangan',  'Keterangan', 2, 'text',    'left',  0, 1),
    (@IdLap, 'QuView3', 'TotalA',      'Bulan Lalu', 3, 'currency','right', 1, 1),
    (@IdLap, 'QuView3', 'TotalB',      'Bulan Ini',  4, 'currency','right', 1, 1),
    (@IdLap, 'QuView3', 'TotalC',      'S/d Bulan Ini',5, 'currency','right', 1, 1),
    -- Hidden fields (referenced in .fr3 PascalScript for expressions/logic)
    (@IdLap, 'QuView3', 'JumlahA',     'JmlA',      6, 'currency','right', 1, 0),
    (@IdLap, 'QuView3', 'JumlahB',     'JmlB',      7, 'currency','right', 1, 0),
    (@IdLap, 'QuView3', 'JumlahC',     'JmlC',      8, 'currency','right', 1, 0),
    (@IdLap, 'QuView3', 'Bulan',       'Bulan',     9, 'number',  'left',  0, 0),
    (@IdLap, 'QuView3', 'Tahun',       'Tahun',    10, 'number',  'left',  0, 0),
    (@IdLap, 'QuView3', 'grup',        'Grup',     11, 'text',    'left',  0, 0),
    (@IdLap, 'QuView3', 'jumlah',      'Jumlah',   12, 'text',    'left',  0, 0),
    (@IdLap, 'QuView3', 'perkiraan',   'Perk',     13, 'text',    'left',  0, 0),
    (@IdLap, 'QuView3', 'tanda',       'Tanda',    14, 'text',    'left',  0, 0),
    (@IdLap, 'QuView3', 'tipe',        'Tipe',     15, 'text',    'left',  0, 0),
    (@IdLap, 'QuView3', 'IsLRHPP',     'IsHPP',    16, 'text',    'left',  0, 0);
GO

-- =============================================
-- 4. dbparameterlaporan (filter parameters)
--    Param value 2 = Bulan + Tahun + Devisi form controls
--    Tipe_input: month + year + browse(1004=Devisi)
-- =============================================
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020502');

DELETE FROM dbparameterlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, konfigurasi, posisi) VALUES
    (@IdLap, 'Devisi',  'Divisi',       'browse',  0, NULL, '{"kode_browse":"1004"}', 1),
    (@IdLap, 'Bulan',   'Bulan',        'month',   1, NULL, NULL,                       2),
    (@IdLap, 'Tahun',   'Tahun',        'year',    1, NULL, NULL,                       3);
GO

-- =============================================
-- 5. DBMENUREPORT - sidebar menu entry
-- =============================================
IF NOT EXISTS (SELECT 1 FROM DBMENUREPORT WHERE KODEMENU = '020502')
INSERT INTO DBMENUREPORT (KODEMENU, Keterangan, L0, ACCESS, OL)
VALUES ('020502', 'Laba Rugi', 4, 20502, 0);
GO

-- =============================================
-- 6. DBFLMENUREPORT - user access (SA = developer)
-- =============================================
IF NOT EXISTS (SELECT 1 FROM DBFLMENUREPORT WHERE USERID = 'SA' AND L1 = '020502')
INSERT INTO DBFLMENUREPORT (USERID, L1, Access, IsDesign, Isexport)
VALUES ('SA', '020502', 1, 1, 1);
GO
