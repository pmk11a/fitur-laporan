-- ============================================================
-- LAPORAN: 202021, 20203, 20204, 20205, 202051, 202052, 202053, 20206
-- Source: FrmReportPreview.pas case blocks
-- Type: Transactional/Summary (stored procedures)
-- ============================================================

USE dbwbcp2;
GO

-- ============================================================
-- 1. dbmasterlaporan
-- ============================================================
DELETE FROM dbmasterlaporan WHERE KODEMENU IN ('202021','20203','20204','20205','202051','202052','202053','20206');

INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, query_sumber_data, footer_bands, status_aktif, created_at)
VALUES
('202021', 'Buku Tambahan Baru', 'sp_ReportBukuTambahan', 1, 1, GETDATE()),
('20203',  'Mutasi',              'Sp_ReportMutasi',      1, 1, GETDATE()),
('20204',  'Laporan Biaya',        'Sp_LapBiaya',          1, 1, GETDATE()),
('20205',  'Laporan Aktiva',       'sp_LapAktiva',         1, 1, GETDATE()),
('202051', 'Laporan Aktiva Tahunan',    'sp_LapAktivaTahunan',        1, 1, GETDATE()),
('202052', 'Laporan Aktiva Pajak',      'sp_LapAktivaPajak',          1, 1, GETDATE()),
('202053', 'Laporan Aktiva Pajak Kendaraan', 'sp_LapAktivaPajakKendaraan', 1, 1, GETDATE()),
('20206',  'Laporan Biaya Penyusutan', 'sp_LapSusutAktiva', 1, 1, GETDATE());
GO

-- ============================================================
-- 2. dbquerylaporan
-- ============================================================
DECLARE @IdLap INT;

-- 202021 Buku Tambahan Baru (param=1: Perkiraan range + Tanggal range + Devisi + Jurnal)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '202021');
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan, config_json)
VALUES (@IdLap, 'QuView1', 'EXEC sp_ReportBukuTambahan @PerkiraanA, @PerkiraanB, @TanggalAwal, @TanggalAkhir, @Devisi, @UserID, @Jurnal', 'Buku Tambahan Baru', 1, '{}');

-- 20203 Mutasi (param=4: Bulan + Tahun + Devisi)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20203');
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan, config_json)
VALUES (@IdLap, 'QuView1', 'EXEC Sp_ReportMutasi @Bulan, @Tahun, @Devisi, @UserID', 'Mutasi Perkiraan', 1, '{}');

-- 20204 Laporan Biaya (param=5: Devisi + Bulan + Tahun + Perkiraan range)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20204');
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan, config_json)
VALUES (@IdLap, 'QuView1', 'EXEC Sp_LapBiaya @Devisi, @Bulan, @Tahun, @PerkiraanA, @PerkiraanB', 'Laporan Biaya', 1, '{}');

-- 20205 Laporan Aktiva (param=4: Bulan + Tahun + Devisi)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20205');
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan, config_json)
VALUES (@IdLap, 'QuView1', 'EXEC sp_LapAktiva @Bulan, @Tahun, @Devisi', 'Laporan Aktiva Tetap', 1, '{}');

-- 202051 Laporan Aktiva Tahunan (param=4: BulanAwal + BulanAkhir + Tahun + Devisi)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '202051');
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan, config_json)
VALUES (@IdLap, 'QuView1', 'EXEC sp_LapAktivaTahunan @BulanAwal, @BulanAkhir, @Tahun, @Devisi', 'Laporan Aktiva Tahunan', 1, '{}');

-- 202052 Laporan Aktiva Pajak (param=4: BulanAwal + BulanAkhir + Tahun + Devisi)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '202052');
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan, config_json)
VALUES (@IdLap, 'QuView1', 'EXEC sp_LapAktivaPajak @BulanAwal, @BulanAkhir, @Tahun, @Devisi', 'Laporan Aktiva Pajak', 1, '{}');

-- 202053 Laporan Aktiva Pajak Kendaraan (param=4: BulanAwal + BulanAkhir + Tahun + Devisi)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '202053');
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan, config_json)
VALUES (@IdLap, 'QuView1', 'EXEC sp_LapAktivaPajakKendaraan @BulanAwal, @BulanAkhir, @Tahun, @Devisi', 'Laporan Aktiva Pajak Kendaraan', 1, '{}');

-- 20206 Laporan Biaya Penyusutan (param=4: Bulan + Tahun + Devisi)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20206');
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan, config_json)
VALUES (@IdLap, 'QuView1', 'EXEC sp_LapSusutAktiva @Bulan, @Tahun, @Devisi', 'Laporan Biaya Penyusutan', 1, '{}');
GO

-- ============================================================
-- 3. dbkolomlaporan
-- ============================================================
DECLARE @IdLap INT;

-- 202021 Buku Tambahan Baru (ReportBukuTambahan1.fr3)
-- Columns: Tanggal, NoBukti, Perkiraan, Keterangan, MD(Debet), MK(Kredit), Saldo
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '202021');
DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible)
VALUES
(@IdLap, 'QuView1', 'Tanggal',   'Tanggal',    1, 'date',     'center', 0, 1),
(@IdLap, 'QuView1', 'NoBukti',   'No. Bukti',  2, 'text',     'left',   0, 1),
(@IdLap, 'QuView1', 'Perkiraan', 'Perkiraan',  3, 'text',     'left',   0, 1),
(@IdLap, 'QuView1', 'Keterangan','Keterangan', 4, 'text',     'left',   0, 1),
(@IdLap, 'QuView1', 'Debet',     'Debet (Rp)', 5, 'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'Kredit',    'Kredit (Rp)',6, 'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'Saldo',     'Saldo',      7, 'currency', 'right',  0, 1);
GO

DECLARE @IdLap INT;

-- 20203 Mutasi (ReportMutasi.fr3)
-- Columns: Perkiraan, Keterangan, SaldoAwal, MD, MK, JPD, JPK
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20203');
DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible)
VALUES
(@IdLap, 'QuView1', 'Perkiraan', 'Perkiraan',    1, 'text',     'left',   0, 1),
(@IdLap, 'QuView1', 'Keterangan','Keterangan',   2, 'text',     'left',   0, 1),
(@IdLap, 'QuView1', 'SaldoAwal', 'Saldo Awal',   3, 'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'MD',        'Mutasi (D)',   4, 'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'MK',        'Mutasi (K)',   5, 'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'JPD',       'JPD',          6, 'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'JPK',       'JPK',          7, 'currency', 'right',  1, 1);

-- 20204 Laporan Biaya (ReportBiaya.fr3)
-- Columns: perkiraan, keterangan, BulanLalu, BulanKini, sdBulanini, Persen
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20204');
DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible)
VALUES
(@IdLap, 'QuView1', 'Perkiraan',  'Perkiraan',       1, 'text',     'left',   0, 1),
(@IdLap, 'QuView1', 'Keterangan',  'Keterangan',      2, 'text',     'left',   0, 1),
(@IdLap, 'QuView1', 'BulanLalu',   'Bulan Lalu (Rp)', 3, 'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'BulanKini',   'Bulan Ini (Rp)',  4, 'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'sdBulanini',  's/d Bulan Ini',   5, 'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'Persen',      'Naik/Turun (%)',  6, 'number',   'right',  0, 1);
GO

DECLARE @IdLap INT;

-- 20205 Laporan Aktiva (ReportAktivaTetap.fr3)
-- Columns: perkiraan, keterangan, Tanggal, Quantity, Persen, awal, MD, MK, akhir, awalSusut, SK, SD, AkhirSusut, NilaiAk
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20205');
DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible)
VALUES
(@IdLap, 'QuView1', 'Perkiraan',  'No. Aktiva',              1,  'text',     'left',   0, 1),
(@IdLap, 'QuView1', 'Keterangan', 'Keterangan',              2,  'text',     'left',   0, 1),
(@IdLap, 'QuView1', 'Tanggal',    'Tgl. Perolehan',         3,  'date',     'center', 0, 1),
(@IdLap, 'QuView1', 'Quantity',   'Qnt',                     4,  'number',   'right',  1, 1),
(@IdLap, 'QuView1', 'Persen',     'Susut (%)',               5,  'number',   'right',  0, 1),
(@IdLap, 'QuView1', 'Awal',       'Perolehan',              6,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'MD',         'Penambahan',             7,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'MK',         'Pengurangan',            8,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'Akhir',      'Nilai Perolehan',        9,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'AwalSusut',  'Akm. Bulan Lalu',       10,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'SK',         'Penambahan',            11,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'SD',         'Akm. s/d Bulan Ini',   12,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'AkhirSusut', 'Nilai Buku',            13,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'NilaiAk',     'Nilai Buku',            14,  'currency', 'right',  1, 1);

-- 202051 Laporan Aktiva Tahunan (same columns as 20205)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '202051');
DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible)
VALUES
(@IdLap, 'QuView1', 'Perkiraan',  'No. Aktiva',              1,  'text',     'left',   0, 1),
(@IdLap, 'QuView1', 'Keterangan', 'Keterangan',              2,  'text',     'left',   0, 1),
(@IdLap, 'QuView1', 'Tanggal',    'Tgl. Perolehan',         3,  'date',     'center', 0, 1),
(@IdLap, 'QuView1', 'Quantity',   'Qnt',                     4,  'number',   'right',  1, 1),
(@IdLap, 'QuView1', 'Persen',     'Susut (%)',               5,  'number',   'right',  0, 1),
(@IdLap, 'QuView1', 'Awal',       'Perolehan',              6,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'MD',         'Penambahan',             7,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'MK',         'Pengurangan',            8,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'Akhir',      'Nilai Perolehan',        9,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'AwalSusut',  'Akm. Bulan Lalu',       10,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'SK',         'Penambahan',            11,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'SD',         'Akm. s/d Bulan Ini',   12,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'AkhirSusut', 'Nilai Buku',            13,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'NilaiAk',     'Nilai Buku',            14,  'currency', 'right',  1, 1);
GO

DECLARE @IdLap INT;

-- 202052 Laporan Aktiva Pajak (same columns as 20205)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '202052');
DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible)
VALUES
(@IdLap, 'QuView1', 'Perkiraan',  'No. Aktiva',              1,  'text',     'left',   0, 1),
(@IdLap, 'QuView1', 'Keterangan', 'Keterangan',              2,  'text',     'left',   0, 1),
(@IdLap, 'QuView1', 'Tanggal',    'Tgl. Perolehan',         3,  'date',     'center', 0, 1),
(@IdLap, 'QuView1', 'Quantity',   'Qnt',                     4,  'number',   'right',  1, 1),
(@IdLap, 'QuView1', 'Persen',     'Susut (%)',               5,  'number',   'right',  0, 1),
(@IdLap, 'QuView1', 'Awal',       'Perolehan',              6,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'MD',         'Penambahan',             7,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'MK',         'Pengurangan',            8,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'Akhir',      'Nilai Perolehan',        9,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'AwalSusut',  'Akm. Bulan Lalu',       10,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'SK',         'Penambahan',            11,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'SD',         'Akm. s/d Bulan Ini',   12,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'AkhirSusut', 'Nilai Buku',            13,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'NilaiAk',     'Nilai Buku',            14,  'currency', 'right',  1, 1);

-- 202053 Laporan Aktiva Pajak Kendaraan (same columns as 20205)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '202053');
DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible)
VALUES
(@IdLap, 'QuView1', 'Perkiraan',  'No. Aktiva',              1,  'text',     'left',   0, 1),
(@IdLap, 'QuView1', 'Keterangan', 'Keterangan',              2,  'text',     'left',   0, 1),
(@IdLap, 'QuView1', 'Tanggal',    'Tgl. Perolehan',         3,  'date',     'center', 0, 1),
(@IdLap, 'QuView1', 'Quantity',   'Qnt',                     4,  'number',   'right',  1, 1),
(@IdLap, 'QuView1', 'Persen',     'Susut (%)',               5,  'number',   'right',  0, 1),
(@IdLap, 'QuView1', 'Awal',       'Perolehan',              6,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'MD',         'Penambahan',             7,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'MK',         'Pengurangan',            8,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'Akhir',      'Nilai Perolehan',        9,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'AwalSusut',  'Akm. Bulan Lalu',       10,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'SK',         'Penambahan',            11,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'SD',         'Akm. s/d Bulan Ini',   12,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'AkhirSusut', 'Nilai Buku',            13,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'NilaiAk',     'Nilai Buku',            14,  'currency', 'right',  1, 1);

-- 20206 Laporan Biaya Penyusutan (ReportSusutAktiva.fr3)
-- Columns: perkiraan, keterangan, Tanggal, Quantity, Persen, awal, NilaiAk_, MD, akhir, awalSusut, AkhirSusut, NilaiAk
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20206');
DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible)
VALUES
(@IdLap, 'QuView1', 'Perkiraan',   'No. Aktiva',              1,  'text',     'left',   0, 1),
(@IdLap, 'QuView1', 'Keterangan',  'Keterangan',              2,  'text',     'left',   0, 1),
(@IdLap, 'QuView1', 'Tanggal',     'Tgl. Perolehan',         3,  'date',     'center', 0, 1),
(@IdLap, 'QuView1', 'Quantity',    'Qnt',                     4,  'number',   'right',  1, 1),
(@IdLap, 'QuView1', 'Persen',      'Susut (%)',               5,  'number',   'right',  0, 1),
(@IdLap, 'QuView1', 'Awal',        'Perolehan s/d Bulan Lalu',6,'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'NilaiAk_',    'Nilai Buku s/d Bulan Lalu',7,'currency','right',  1, 1),
(@IdLap, 'QuView1', 'MD',          'Penambahan Bulan Ini',    8,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'Akhir',       'Nilai Buku s/d Bulan Ini',9, 'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'AwalSusut',   'Akm. s/d Bulan Lalu',   10,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'AkhirSusut',  'Akm. s/d Bulan Ini',   11,  'currency', 'right',  1, 1),
(@IdLap, 'QuView1', 'NilaiAk',     'Nilai Buku s/d Bulan Ini',12,'currency','right',  1, 1);
GO

-- ============================================================
-- 4. dbgrouplaporan
-- ============================================================
DECLARE @IdLap INT;

-- 202021 Buku Tambahan Baru (group by NoACC)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '202021');
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, sort_order, show_subtotal)
VALUES (@IdLap, 1, 'NoACC', '', 'Perkiraan: [Nama] ([noacc])', 1, 1);

-- 20203 Mutasi (group by Perkiraan)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20203');
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, sort_order, show_subtotal)
VALUES (@IdLap, 1, 'Perkiraan', '', '[Perkiraan] - Subtotal', 1, 1);

-- 20204 Laporan Biaya (group by Perkiraan)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20204');
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, sort_order, show_subtotal)
VALUES (@IdLap, 1, 'Perkiraan', '', '[Perkiraan]', 1, 1);

-- 20205, 202051, 202052, 202053 Laporan Aktiva (group by GrpPerkiraan)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20205');
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, sort_order, show_subtotal)
VALUES (@IdLap, 1, 'GrpPerkiraan', '', '[NamaPerkiraan] ([GrpPerkiraan])', 1, 1);

SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '202051');
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, sort_order, show_subtotal)
VALUES (@IdLap, 1, 'GrpPerkiraan', '', '[NamaPerkiraan] ([GrpPerkiraan])', 1, 1);

SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '202052');
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, sort_order, show_subtotal)
VALUES (@IdLap, 1, 'GrpPerkiraan', '', '[NamaPerkiraan] ([GrpPerkiraan])', 1, 1);

SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '202053');
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, sort_order, show_subtotal)
VALUES (@IdLap, 1, 'GrpPerkiraan', '', '[NamaPerkiraan] ([GrpPerkiraan])', 1, 1);

-- 20206 Laporan Biaya Penyusutan (group by GrpPerkiraan)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20206');
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, sort_order, show_subtotal)
VALUES (@IdLap, 1, 'GrpPerkiraan', '', '[NamaPerkiraan] ([GrpPerkiraan])', 1, 1);
GO

-- ============================================================
-- 5. dbparameterlaporan
-- ============================================================
DECLARE @IdLap INT;

-- 202021 Buku Tambahan Baru (param=1: Perkiraan range + Tanggal + Devisi + Jurnal)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '202021');
DELETE FROM dbparameterlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi, konfigurasi)
VALUES
(@IdLap, 'PerkiraanA', 'Perkiraan Awal', 'browse', 1, '', 1, '{"kode_browse":"1005","mode":"tags"}'),
(@IdLap, 'PerkiraanB', 'Perkiraan Akhir', 'browse', 1, '', 2, '{"kode_browse":"1005","mode":"tags"}'),
(@IdLap, 'TanggalAwal', 'Tanggal Awal', 'date', 1, '', 3, '{}'),
(@IdLap, 'TanggalAkhir', 'Tanggal Akhir', 'date', 1, '', 4, '{}'),
(@IdLap, 'Devisi', 'Devisi', 'browse', 1, '', 5, '{"kode_browse":"1004","mode":"tags"}'),
(@IdLap, 'Jurnal', 'Jenis Jurnal', 'text', 0, '', 6, '{}');
GO

DECLARE @IdLap INT;

-- 20203 Mutasi (param=4: Bulan + Tahun + Devisi)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20203');
DELETE FROM dbparameterlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi, konfigurasi)
VALUES
(@IdLap, 'Bulan', 'Bulan', 'number', 1, '', 1, '{"min":1,"max":12}'),
(@IdLap, 'Tahun', 'Tahun', 'number', 1, '', 2, '{}'),
(@IdLap, 'Devisi', 'Devisi', 'browse', 1, '', 3, '{"kode_browse":"1004","mode":"tags"}');
GO

DECLARE @IdLap INT;

-- 20204 Laporan Biaya (param=5: Devisi + Bulan + Tahun + Perkiraan range)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20204');
DELETE FROM dbparameterlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi, konfigurasi)
VALUES
(@IdLap, 'Devisi', 'Devisi', 'browse', 1, '', 1, '{"kode_browse":"1004","mode":"tags"}'),
(@IdLap, 'Bulan', 'Bulan', 'number', 1, '', 2, '{"min":1,"max":12}'),
(@IdLap, 'Tahun', 'Tahun', 'number', 1, '', 3, '{}'),
(@IdLap, 'PerkiraanA', 'Perkiraan Awal', 'browse', 1, '', 4, '{"kode_browse":"1005","mode":"single"}'),
(@IdLap, 'PerkiraanB', 'Perkiraan Akhir', 'browse', 1, '', 5, '{"kode_browse":"1005","mode":"single"}');
GO

DECLARE @IdLap INT;

-- 20205 Laporan Aktiva (param=4: Bulan + Tahun + Devisi)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20205');
DELETE FROM dbparameterlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi, konfigurasi)
VALUES
(@IdLap, 'Bulan', 'Bulan', 'number', 1, '', 1, '{"min":1,"max":12}'),
(@IdLap, 'Tahun', 'Tahun', 'number', 1, '', 2, '{}'),
(@IdLap, 'Devisi', 'Devisi', 'browse', 1, '', 3, '{"kode_browse":"1004","mode":"tags"}');
GO

DECLARE @IdLap INT;

-- 202051 Laporan Aktiva Tahunan (param=4: BulanAwal + BulanAkhir + Tahun + Devisi)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '202051');
DELETE FROM dbparameterlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi, konfigurasi)
VALUES
(@IdLap, 'BulanAwal', 'Bulan Awal', 'number', 1, '1', 1, '{"min":1,"max":12}'),
(@IdLap, 'BulanAkhir', 'Bulan Akhir', 'number', 1, '', 2, '{"min":1,"max":12}'),
(@IdLap, 'Tahun', 'Tahun', 'number', 1, '', 3, '{}'),
(@IdLap, 'Devisi', 'Devisi', 'browse', 1, '', 4, '{"kode_browse":"1004","mode":"tags"}');
GO

DECLARE @IdLap INT;

-- 202052 Laporan Aktiva Pajak (param=4: BulanAwal + BulanAkhir + Tahun + Devisi)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '202052');
DELETE FROM dbparameterlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi, konfigurasi)
VALUES
(@IdLap, 'BulanAwal', 'Bulan Awal', 'number', 1, '1', 1, '{"min":1,"max":12}'),
(@IdLap, 'BulanAkhir', 'Bulan Akhir', 'number', 1, '', 2, '{"min":1,"max":12}'),
(@IdLap, 'Tahun', 'Tahun', 'number', 1, '', 3, '{}'),
(@IdLap, 'Devisi', 'Devisi', 'browse', 1, '', 4, '{"kode_browse":"1004","mode":"tags"}');
GO

DECLARE @IdLap INT;

-- 202053 Laporan Aktiva Pajak Kendaraan (param=4: BulanAwal + BulanAkhir + Tahun + Devisi)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '202053');
DELETE FROM dbparameterlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi, konfigurasi)
VALUES
(@IdLap, 'BulanAwal', 'Bulan Awal', 'number', 1, '1', 1, '{"min":1,"max":12}'),
(@IdLap, 'BulanAkhir', 'Bulan Akhir', 'number', 1, '', 2, '{"min":1,"max":12}'),
(@IdLap, 'Tahun', 'Tahun', 'number', 1, '', 3, '{}'),
(@IdLap, 'Devisi', 'Devisi', 'browse', 1, '', 4, '{"kode_browse":"1004","mode":"tags"}');
GO

DECLARE @IdLap INT;

-- 20206 Laporan Biaya Penyusutan (param=4: Bulan + Tahun + Devisi)
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20206');
DELETE FROM dbparameterlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi, konfigurasi)
VALUES
(@IdLap, 'Bulan', 'Bulan', 'number', 1, '', 1, '{"min":1,"max":12}'),
(@IdLap, 'Tahun', 'Tahun', 'number', 1, '', 2, '{}'),
(@IdLap, 'Devisi', 'Devisi', 'browse', 1, '', 3, '{"kode_browse":"1004","mode":"tags"}');
GO

-- ============================================================
-- 6. DBMENUREPORT (sidebar menu) -- INSERT OR UPDATE pattern
-- L0: 2 = Laporan/Keuangan (matching existing 20201xx pattern)
-- ============================================================

-- 202021: Buku Tambahan Baru
IF NOT EXISTS (SELECT 1 FROM DBMENUREPORT WHERE KODEMENU = '202021')
BEGIN
    INSERT INTO DBMENUREPORT (KODEMENU, Keterangan, L0, ACCESS) VALUES ('202021', 'Buku Tambahan Baru', 2, 202021);
END
ELSE
BEGIN
    UPDATE DBMENUREPORT SET Keterangan = 'Buku Tambahan Baru', ACCESS = 202021, L0 = 2 WHERE KODEMENU = '202021';
END

-- 20203: Mutasi
IF NOT EXISTS (SELECT 1 FROM DBMENUREPORT WHERE KODEMENU = '20203')
BEGIN
    INSERT INTO DBMENUREPORT (KODEMENU, Keterangan, L0, ACCESS) VALUES ('20203', 'Mutasi', 2, 20203);
END
ELSE
BEGIN
    UPDATE DBMENUREPORT SET Keterangan = 'Mutasi', ACCESS = 20203, L0 = 2 WHERE KODEMENU = '20203';
END

-- 20204: Laporan Biaya
IF NOT EXISTS (SELECT 1 FROM DBMENUREPORT WHERE KODEMENU = '20204')
BEGIN
    INSERT INTO DBMENUREPORT (KODEMENU, Keterangan, L0, ACCESS) VALUES ('20204', 'Laporan Biaya', 2, 20204);
END
ELSE
BEGIN
    UPDATE DBMENUREPORT SET Keterangan = 'Laporan Biaya', ACCESS = 20204, L0 = 2 WHERE KODEMENU = '20204';
END

-- 20205: Laporan Aktiva
IF NOT EXISTS (SELECT 1 FROM DBMENUREPORT WHERE KODEMENU = '20205')
BEGIN
    INSERT INTO DBMENUREPORT (KODEMENU, Keterangan, L0, ACCESS) VALUES ('20205', 'Laporan Aktiva', 2, 20205);
END
ELSE
BEGIN
    UPDATE DBMENUREPORT SET Keterangan = 'Laporan Aktiva', ACCESS = 20205, L0 = 2 WHERE KODEMENU = '20205';
END

-- 202051: Laporan Aktiva Tahunan
IF NOT EXISTS (SELECT 1 FROM DBMENUREPORT WHERE KODEMENU = '202051')
BEGIN
    INSERT INTO DBMENUREPORT (KODEMENU, Keterangan, L0, ACCESS) VALUES ('202051', 'Laporan Aktiva Tahunan', 2, 202051);
END
ELSE
BEGIN
    UPDATE DBMENUREPORT SET Keterangan = 'Laporan Aktiva Tahunan', ACCESS = 202051, L0 = 2 WHERE KODEMENU = '202051';
END

-- 202052: Laporan Aktiva Pajak
IF NOT EXISTS (SELECT 1 FROM DBMENUREPORT WHERE KODEMENU = '202052')
BEGIN
    INSERT INTO DBMENUREPORT (KODEMENU, Keterangan, L0, ACCESS) VALUES ('202052', 'Laporan Aktiva Pajak', 2, 202052);
END
ELSE
BEGIN
    UPDATE DBMENUREPORT SET Keterangan = 'Laporan Aktiva Pajak', ACCESS = 202052, L0 = 2 WHERE KODEMENU = '202052';
END

-- 202053: Laporan Aktiva Pajak Kendaraan
IF NOT EXISTS (SELECT 1 FROM DBMENUREPORT WHERE KODEMENU = '202053')
BEGIN
    INSERT INTO DBMENUREPORT (KODEMENU, Keterangan, L0, ACCESS) VALUES ('202053', 'Laporan Aktiva Pajak Kendaraan', 2, 202053);
END
ELSE
BEGIN
    UPDATE DBMENUREPORT SET Keterangan = 'Laporan Aktiva Pajak Kendaraan', ACCESS = 202053, L0 = 2 WHERE KODEMENU = '202053';
END

-- 20206: Laporan Biaya Penyusutan
IF NOT EXISTS (SELECT 1 FROM DBMENUREPORT WHERE KODEMENU = '20206')
BEGIN
    INSERT INTO DBMENUREPORT (KODEMENU, Keterangan, L0, ACCESS) VALUES ('20206', 'Laporan Biaya Penyusutan', 2, 20206);
END
ELSE
BEGIN
    UPDATE DBMENUREPORT SET Keterangan = 'Laporan Biaya Penyusutan', ACCESS = 20206, L0 = 2 WHERE KODEMENU = '20206';
END
GO

-- ============================================================
-- 7. DBFLMENUREPORT (user access)
-- ============================================================

-- 202021
IF NOT EXISTS (SELECT 1 FROM DBFLMENUREPORT WHERE USERID = 'SA' AND L1 = '202021')
BEGIN
    INSERT INTO DBFLMENUREPORT (USERID, L1, Access) VALUES ('SA', '202021', 1);
END
ELSE
BEGIN
    UPDATE DBFLMENUREPORT SET Access = 1 WHERE USERID = 'SA' AND L1 = '202021';
END

-- 20203
IF NOT EXISTS (SELECT 1 FROM DBFLMENUREPORT WHERE USERID = 'SA' AND L1 = '20203')
BEGIN
    INSERT INTO DBFLMENUREPORT (USERID, L1, Access) VALUES ('SA', '20203', 1);
END
ELSE
BEGIN
    UPDATE DBFLMENUREPORT SET Access = 1 WHERE USERID = 'SA' AND L1 = '20203';
END

-- 20204
IF NOT EXISTS (SELECT 1 FROM DBFLMENUREPORT WHERE USERID = 'SA' AND L1 = '20204')
BEGIN
    INSERT INTO DBFLMENUREPORT (USERID, L1, Access) VALUES ('SA', '20204', 1);
END
ELSE
BEGIN
    UPDATE DBFLMENUREPORT SET Access = 1 WHERE USERID = 'SA' AND L1 = '20204';
END

-- 20205
IF NOT EXISTS (SELECT 1 FROM DBFLMENUREPORT WHERE USERID = 'SA' AND L1 = '20205')
BEGIN
    INSERT INTO DBFLMENUREPORT (USERID, L1, Access) VALUES ('SA', '20205', 1);
END
ELSE
BEGIN
    UPDATE DBFLMENUREPORT SET Access = 1 WHERE USERID = 'SA' AND L1 = '20205';
END

-- 202051
IF NOT EXISTS (SELECT 1 FROM DBFLMENUREPORT WHERE USERID = 'SA' AND L1 = '202051')
BEGIN
    INSERT INTO DBFLMENUREPORT (USERID, L1, Access) VALUES ('SA', '202051', 1);
END
ELSE
BEGIN
    UPDATE DBFLMENUREPORT SET Access = 1 WHERE USERID = 'SA' AND L1 = '202051';
END

-- 202052
IF NOT EXISTS (SELECT 1 FROM DBFLMENUREPORT WHERE USERID = 'SA' AND L1 = '202052')
BEGIN
    INSERT INTO DBFLMENUREPORT (USERID, L1, Access) VALUES ('SA', '202052', 1);
END
ELSE
BEGIN
    UPDATE DBFLMENUREPORT SET Access = 1 WHERE USERID = 'SA' AND L1 = '202052';
END

-- 202053
IF NOT EXISTS (SELECT 1 FROM DBFLMENUREPORT WHERE USERID = 'SA' AND L1 = '202053')
BEGIN
    INSERT INTO DBFLMENUREPORT (USERID, L1, Access) VALUES ('SA', '202053', 1);
END
ELSE
BEGIN
    UPDATE DBFLMENUREPORT SET Access = 1 WHERE USERID = 'SA' AND L1 = '202053';
END

-- 20206
IF NOT EXISTS (SELECT 1 FROM DBFLMENUREPORT WHERE USERID = 'SA' AND L1 = '20206')
BEGIN
    INSERT INTO DBFLMENUREPORT (USERID, L1, Access) VALUES ('SA', '20206', 1);
END
ELSE
BEGIN
    UPDATE DBFLMENUREPORT SET Access = 1 WHERE USERID = 'SA' AND L1 = '20206';
END
GO

-- ============================================================
-- 8. Verification
-- ============================================================
SELECT 'dbmasterlaporan' AS tbl, COUNT(*) AS cnt FROM dbmasterlaporan WHERE KODEMENU IN ('202021','20203','20204','20205','202051','202052','202053','20206')
UNION ALL SELECT 'dbquerylaporan', COUNT(*) FROM dbquerylaporan WHERE id_laporan IN (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU IN ('202021','20203','20204','20205','202051','202052','202053','20206'))
UNION ALL SELECT 'dbkolomlaporan', COUNT(*) FROM dbkolomlaporan WHERE id_laporan IN (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU IN ('202021','20203','20204','20205','202051','202052','202053','20206'))
UNION ALL SELECT 'dbgrouplaporan', COUNT(*) FROM dbgrouplaporan WHERE id_laporan IN (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU IN ('202021','20203','20204','20205','202051','202052','202053','20206'))
UNION ALL SELECT 'dbparameterlaporan', COUNT(*) FROM dbparameterlaporan WHERE id_laporan IN (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU IN ('202021','20203','20204','20205','202051','202052','202053','20206'))
UNION ALL SELECT 'DBMENUREPORT', COUNT(*) FROM DBMENUREPORT WHERE KODEMENU IN ('202021','20203','20204','20205','202051','202052','202053','20206')
UNION ALL SELECT 'DBFLMENUREPORT', COUNT(*) FROM DBFLMENUREPORT WHERE L1 IN ('202021','20203','20204','20205','202051','202052','202053','20206');

SELECT m.KODEMENU, m.nama_laporan, q.nama_dataset, q.query_sumber_data
FROM dbmasterlaporan m
JOIN dbquerylaporan q ON q.id_laporan = m.id_laporan
WHERE m.KODEMENU IN ('202021','20203','20204','20205','202051','202052','202053','20206')
ORDER BY m.KODEMENU;
GO
