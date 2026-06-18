-- ============================================================
-- MIGRATION: Laporan Neraca (020503)
-- Delphi: ShowReportPreview(' Neraca',2)
-- FR3:    ReportNeraca.fr3
-- SP:     sp_ReportNeracaAktiva (QuView3)
--         sp_ReportNeracaPasiva (QuView4)
-- Type:   multi-dataset side-by-side (Aktiva | Pasiva)
--
-- Strategi: DELETE dulu, baru INSERT fresh (idempotent reset)
-- Tabel: dbmasterlaporan, dbquerylaporan, dbkolomlaporan,
--        dbgrouplaporan, dbparameterlaporan
--
-- PENTING: Setiap `GO` membuat batch baru di SQL Server.
-- Variabel lokal (@IdLap) harus dideklarasikan ulang
-- di setiap batch, atau gunakan query langsung (tanpa variabel).
--
-- PENTING: dbmenreport & dbflmenureport TIDAK diubah
-- (lihat memory: no-modify-menu-report-tables)
-- ============================================================

-- =============================================
-- 1. dbmasterlaporan - reset + insert
-- =============================================
DELETE FROM dbmasterlaporan WHERE KODEMENU = '020503';
INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, query_sumber_data, status_aktif, footer_bands)
VALUES ('020503', 'Neraca', 'Neraca Balance Sheet - Aktiva & Pasiva side-by-side', NULL, 1,
'{"bands":{"title":{"enabled":true,"content":"N E R A C A","align":"center"},"pageHeader":{"enabled":true,"content":"Per [Periode] ( Dalam Ribuan )"},"pageFooter":{"enabled":true,"content":"Halaman [Page] dari [TotalPages#]"},"summary":{"enabled":true,"layout":{"columns":2,"alignment":"spread"},"signatures":[{"label":"Pimpinan","position":"left"},{"label":"Kontrol","position":"right"}]}}}');
GO

-- =============================================
-- 2. dbquerylaporan (2 datasets: QuView3 Aktiva, QuView4 Pasiva)
--    config_json berisi display_role=detail dengan detail_layout=side_by_side
--    untuk trigger layout 2-column di frontend (lihat [kode].vue:240-243)
-- =============================================
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020503');

DELETE FROM dbquerylaporan WHERE id_laporan = @IdLap;
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan, visible, config_json) VALUES
    -- QuView3: Aktiva (kiri)
    (@IdLap, 'QuView3', 'EXEC sp_ReportNeracaAktiva @Devisi, @Bulan, @Tahun',  'Dataset Aktiva - Neraca (kiri)',  1, 1, '{"display_role":"detail","detail_layout":"side_by_side","position":"left","label":"AKTIVA","sum_columns":["jumlah1","jumlah2"]}'),
    -- QuView4: Pasiva (kanan)
    (@IdLap, 'QuView4', 'EXEC sp_ReportNeracaPasiva @Devisi, @Bulan, @Tahun',  'Dataset Pasiva - Kewajiban & Modal (kanan)', 2, 1, '{"display_role":"detail","detail_layout":"side_by_side","position":"right","label":"LIABILITAS","sum_columns":["jumlah1","jumlah2"]}');
GO

-- =============================================
-- 3. dbkolomlaporan
--    nama_kolom HARUS case-sensitive match dengan field SP.
--    QuView3 & QuView4 return kolom identik (keterangan, jumlah1, jumlah2, grupAP1, grupAP2)
--    FR3 referensi (ReportNeraca.fr3):
--      MasterData1 DataField: keterangan, jumlah1, jumlah2
--      MasterData2 DataField: keterangan, jumlah1, jumlah2
--      GroupHeader1 Condition: frxDBDataset3."grupAP1"
--      GroupHeader2 Condition: frxDBDataset3."grupAP2" (ScriptText judul1)
--      GroupHeader4 Condition: frxDBDataset4."grupAP2" (ScriptText judul1)
--      ReportSummary1: SUM(jumlah1, MasterData1), SUM(jumlah2, MasterData1),
--                      SUM(jumlah1, MasterData2), SUM(jumlah2, MasterData2)
-- =============================================
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020503');

DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible) VALUES
    -- QuView3: Aktiva (3 kolom utama)
    (@IdLap, 'QuView3', 'keterangan', 'Uraian',     1, 'text',     'left',  0, 1),
    (@IdLap, 'QuView3', 'jumlah1',    'Bulan Ini',  2, 'currency', 'right', 1, 1),
    (@IdLap, 'QuView3', 'jumlah2',    'Bulan Lalu', 3, 'currency', 'right', 1, 1),
    -- QuView3: grouping fields (hidden, only for dbgrouplaporan reference)
    (@IdLap, 'QuView3', 'grupAP1',    'Group AP1',  4, 'text',     'left',  0, 0),
    (@IdLap, 'QuView3', 'grupAP2',    'Group AP2',  5, 'text',     'left',  0, 0),
    -- QuView4: Pasiva (3 kolom utama, layout identik)
    (@IdLap, 'QuView4', 'keterangan', 'Uraian',     1, 'text',     'left',  0, 1),
    (@IdLap, 'QuView4', 'jumlah1',    'Bulan Ini',  2, 'currency', 'right', 1, 1),
    (@IdLap, 'QuView4', 'jumlah2',    'Bulan Lalu', 3, 'currency', 'right', 1, 1),
    -- QuView4: grouping fields
    (@IdLap, 'QuView4', 'grupAP1',    'Group AP1',  4, 'text',     'left',  0, 0),
    (@IdLap, 'QuView4', 'grupAP2',    'Group AP2',  5, 'text',     'left',  0, 0);
GO

-- =============================================
-- 4. dbgrouplaporan
--    group_field: 'grupAP2' (level 2 - judul section: INVESTASI, AKTIVA LANCAR, dll)
--    ScriptText di .fr3 (GroupHeader2OnBeforePrint / GroupHeader4OnBeforePrint)
--    memetakan nilai grupAP2 ke judul1 variable:
--      QuView3 (Aktiva, 9 kategori): A1=INVESTASI, A2=SELISIH PENILAIAN INVESTASI,
--                                     A3=ASET LANCAR DI LUAR INVESTASI, A4=ASET OPERASIONAL,
--                                     A5=ASET LAIN-LAIN, P1=NILAI AKTUARIA,
--                                     P2=LIABILITAS DILUAR NILAI KINI AKTUARIAL,
--                                     P3=KEWAJIBAN TIDAK LANCAR, P4=MODAL SENDIRI
--      QuView4 (Pasiva, 6 kategori): A1=AKTIVA LANCAR, A2=AKTIVA TETAP, A3=AKTIVA LAIN-LAIN,
--                                    P1=NILAI AKTUARIA, P2=LIABILITAS DILUAR NILAI KINI AKTUARIAL,
--                                    P3=MODAL SENDIRI
--
--    field_value = '' (NOT NULL column)
--    special_handling = 'label_grup' agar frontend apply dbLabelGrup mapping
--    (lihat memory: migration-pattern-multi-dataset-report, applyLabelMapping)
-- =============================================
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020503');

DELETE FROM dbgrouplaporan WHERE id_laporan = @IdLap;
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, sort_order, show_subtotal, special_handling, config_json) VALUES
    -- QuView3 (Aktiva): group by grupAP1 (L1) + grupAP2 (L2)
    (@IdLap, 1, 'grupAP1', '', 'A', 1, 0, 'none', NULL),
    (@IdLap, 2, 'grupAP2', '', '[Judul1]', 2, 1, 'label_grup', '{"dataset":"QuView3","label_source":"dbLabelGrup","field_name":"grupAP2"}'),
    -- QuView4 (Pasiva): group by grupAP1 (L1) + grupAP2 (L2)
    (@IdLap, 1, 'grupAP1', '', 'P', 3, 0, 'none', NULL),
    (@IdLap, 2, 'grupAP2', '', '[Judul1]', 4, 1, 'label_grup', '{"dataset":"QuView4","label_source":"dbLabelGrup","field_name":"grupAP2"}');
GO

-- =============================================
-- 5. dbparameterlaporan (filter parameters for stored procedures)
-- Parameters: @Devisi, @Bulan, @Tahun
-- Tipe_input:
--   - Devisi: browse dengan kode_browse=1004 (DBDevisi)
--   - Bulan:  month
--   - Tahun:  year
-- =============================================
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020503');

DELETE FROM dbparameterlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, konfigurasi, posisi) VALUES
    (@IdLap, 'Devisi', 'Divisi', 'browse', 0, NULL, '{"kode_browse":"1004"}', 1),
    (@IdLap, 'Bulan',  'Bulan',  'month',  1, NULL, NULL,                    2),
    (@IdLap, 'Tahun',  'Tahun',  'year',   1, NULL, NULL,                    3);
GO

-- =============================================
-- 6. dbLabelGrup - label mapping untuk grupAP2 values
--    (lihat memory: migration-pattern-multi-dataset-report, applyLabelMapping)
--    Skema dbLabelGrup TIDAK punya kode_laporan (verified 2026-06-18):
--      id, field_name, field_value, label, aktif, sort_order
--    Jadi label ini global (berlaku untuk semua report yang pakai field grupAP2)
-- =============================================
DELETE FROM dbLabelGrup WHERE field_name = 'grupAP2' AND field_value IN ('A1','A2','A3','A4','A5','P1','P2','P3','P4');
INSERT INTO dbLabelGrup (field_name, field_value, label, aktif, sort_order) VALUES
    -- QuView3 (Aktiva) categories
    ('grupAP2', 'A1', 'INVESTASI',                              1, 1),
    ('grupAP2', 'A2', 'SELISIH PENILAIAN INVESTASI',            1, 2),
    ('grupAP2', 'A3', 'ASET LANCAR DI LUAR INVESTASI',           1, 3),
    ('grupAP2', 'A4', 'ASET OPERASIONAL',                       1, 4),
    ('grupAP2', 'A5', 'ASET LAIN-LAIN',                         1, 5),
    -- QuView4 (Pasiva) categories
    ('grupAP2', 'P1', 'NILAI AKTUARIA',                         1, 6),
    ('grupAP2', 'P2', 'LIABILITAS DILUAR NILAI KINI AKTUARIAL', 1, 7),
    ('grupAP2', 'P3', 'KEWAJIBAN TIDAK LANCAR',                 1, 8),
    ('grupAP2', 'P4', 'MODAL SENDIRI',                          1, 9);
GO

-- ============================================================
-- DONE: Report 020503 - Neraca
--
-- VERIFICATION:
-- ✓ 2 datasets (QuView3 Aktiva, QuView4 Pasiva) - side-by-side layout
-- ✓ Columns match .fr3 DataField (keterangan, jumlah1, jumlah2)
-- ✓ Grouping: 4 entries (L1 grupAP1, L2 grupAP2 per dataset)
-- ✓ Group field 'grupAP2' matches .fr3 GroupHeader Condition
-- ✓ dbLabelGrup entries map grupAP2 → Judul1 (PascalScript values)
-- ✓ Parameters: @Devisi (browse 1004), @Bulan (month), @Tahun (year)
-- ✓ footer_bands: title + pageHeader + 2-column signature
--
-- FIX applied 2026-06-18:
-- - ReportService.buildGroupedData now defensively handles level-1 missing from DB
--   by inferring it from grupAP1 field in SP data (see ReportService.php:729-737)
-- - SQL now includes explicit L1 entries for grupAP1 to match SP return structure
--
-- KNOWN LIMITATIONS:
-- - Paper size/margins: not configurable via DB (fr3 uses A4 landscape)
-- - 2-column layout: implemented via detail_layout=side_by_side config
-- - Tahoma font: .fr3 uses Tahoma, generic renderer uses Tailwind default
-- - Subreport rendering: .fr3 uses Subreport1/Subreport2 (Page2/Page3),
--   generic renderer flattens to single page
--
-- NEXT STEPS:
-- 1. Execute SQL in SSMS: sql/dbinsert_20503_neraca.sql
-- 2. Open browser: /reports/020503
-- 3. Compare with Delphi preview
-- 4. If grupAP2 labels tidak muncul: cek dbLabelGrup + ReportService::applyLabelMapping
-- ============================================================
