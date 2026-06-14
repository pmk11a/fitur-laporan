-- ============================================================
-- MIGRATION: Laporan Bank Harian (020102)
-- Delphi: ShowReportPreview(' Laporan Bank Harian',0)
-- FR3: ReportBankHarian.fr3
-- SP: Sp_LapBankHarian (@Perkiraan, @TglAwal, @TglAkhir, @Divisi)
-- Type: transactional - harian dengan filter tanggal
--
-- Strategi: DELETE dulu, baru INSERT fresh (idempotent reset)
-- Tabel: dbmasterlaporan, dbquerylaporan, dbkolomlaporan,
--        dbparameterlaporan, DBMENUREPORT, DBFLMENUREPORT
--
-- PENTING: Setiap `GO` membuat batch baru di SQL Server.
-- Variabel lokal (@IdLap, dll) harus dideklarasikan ulang
-- di setiap batch, atau gunakan query langsung (tanpa variabel).
-- ============================================================

-- =============================================
-- 0. DBMENUREPORT (sidebar menu) - reset + insert
-- =============================================
DELETE FROM DBMENUREPORT WHERE KODEMENU = '020102';
INSERT INTO DBMENUREPORT (KODEMENU, Keterangan, L0, ACCESS)
VALUES ('020102', 'Bank Harian', 2, 20102);
GO

-- =============================================
-- 0b. DBFLMENUREPORT (user access) - reset + insert
-- =============================================
DELETE FROM DBFLMENUREPORT WHERE L1 = '020102' AND USERID = 'SA';
INSERT INTO DBFLMENUREPORT (USERID, L1, Access)
VALUES ('SA', '020102', 1);
GO

-- =============================================
-- 1. dbmasterlaporan - reset + insert
-- =============================================
DELETE FROM dbmasterlaporan WHERE KODEMENU = '020102';
INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, query_sumber_data, status_aktif, footer_bands)
VALUES ('020102', 'Bank Harian', 'Laporan bank harian dengan mutasi transaksi bank', 'sp_LapBankHarian', 1,
'{"bands":{"title":{"enabled":true,"content":"LAPORAN BANK HARIAN","align":"center"},"pageHeader":{"enabled":true},"summary":{"enabled":true,"footer_table":{"rows":["Jumlah","Saldo Awal","Saldo Akhir","Kontrol"],"columns":["Penerimaan","Pengeluaran"]},"signatures":[{"label":"Pimpinan","position":"left"},{"label":"Kontrol","position":"center"},{"label":"Kasir","position":"right"}]}}}');
GO

-- =============================================
-- 2. dbquerylaporan (3 datasets: T1 Saldo Awal, T2 Mutasi, T3 Saldo Riil)
--    config_json berisi display_role: summary untuk T1, detail untuk T2/T3
-- =============================================
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020102');

DELETE FROM dbquerylaporan WHERE id_laporan = @IdLap;
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan, visible, config_json) VALUES
    (@IdLap, 'T1', 'EXEC Sp_LapSaldoAwal @Perkiraan, @TglAwal, @TglAkhir, @Divisi',     'Saldo awal sebelum periode',                   1, 1, '{"display_role":"summary","summary_layout":"grid_2col","summary_fields":["SaldoAwalD","SaldoAkhirD","TotalD","SaldoAwalK","SaldoAkhirK","TotalK"],"right_fields":["TotalK","SaldoAwalK","SaldoAkhirK"]}'),
    (@IdLap, 'T2', 'EXEC Sp_LapBankHarian @Perkiraan, @TglAwal, @TglAkhir, @Divisi',    'Mutasi transaksi bank (penerimaan/pengeluaran)', 2, 1, '{"display_role":"detail"}'),
    (@IdLap, 'T3', 'EXEC Sp_LapBankHarian @Perkiraan, @TglAwal, @TglAkhir, @Divisi',    'Saldo riil dan perubahan harian',              3, 1, '{"display_role":"detail"}');
GO

-- =============================================
-- 3. dbkolomlaporan (18 kolom: 6 T1 + 7 T2 + 5 T3)
--    nama_kolom HARUS case-sensitive match dengan field SP.
--    FR3 referensi (ReportBankHarian.fr3):
--      T2 DataField: Perkiraan|Tanggal|NoBukti|Keterangan|lawan|kredit|Debet
--      T3 DataField: SaldoUS|CHGB|SaldoRp|SaldoTotalRp
-- =============================================
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020102');

DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible) VALUES
    -- T1: Saldo Awal (6 kolom)
    (@IdLap, 'T1', 'SaldoAwalD',   'Saldo Awal',     1, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'SaldoAkhirD',  'Saldo Akhir',    2, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'TotalD',       'Total',          3, 'currency', 'right', 1, 1),
    (@IdLap, 'T1', 'SaldoAwalK',   'Saldo Awal (K)', 4, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'SaldoAkhirK',  'Saldo Akhir (K)',5, 'currency', 'right', 0, 1),
    (@IdLap, 'T1', 'TotalK',       'Total (K)',      6, 'currency', 'right', 1, 1),
    -- T2: Mutasi Transaksi (7 kolom)
    -- Urutan mengikuti ReportBankHarian.fr3 (DataField order)
    -- nama_kolom HARUS persis sama dengan field SP Sp_LapBankHarian (case-sensitive)
    (@IdLap, 'T2', 'Perkiraan',    'Perk. Bank',     1, 'text',     'left',   0, 1),
    (@IdLap, 'T2', 'Tanggal',      'Tanggal',        2, 'date',     'center', 0, 1),
    (@IdLap, 'T2', 'NoBukti',      'No. Bukti',      3, 'text',     'center', 0, 1),
    (@IdLap, 'T2', 'Keterangan',   'Uraian',         4, 'text',     'left',   0, 1),
    (@IdLap, 'T2', 'lawan',        'Perk.',          5, 'text',     'left',   0, 1),
    (@IdLap, 'T2', 'kredit',       'Pengeluaran',    6, 'currency', 'right',  1, 1),
    (@IdLap, 'T2', 'Debet',        'Penerimaan',     7, 'currency', 'right',  1, 1),
    -- T3: Saldo Riil (5 kolom)
    (@IdLap, 'T3', 'Perkiraan',    'Bank',           1, 'text',     'left',   0, 1),
    (@IdLap, 'T3', 'SaldoUS',      'Riil $',         2, 'currency', 'right',  1, 1),
    (@IdLap, 'T3', 'SaldoRp',      'Riil Rp',        3, 'currency', 'right',  1, 1),
    (@IdLap, 'T3', 'CHGB',         'CHGB',           4, 'currency', 'right',  1, 1),
    (@IdLap, 'T3', 'SaldoTotalRp', 'Total',          5, 'currency', 'right',  1, 1);
GO

-- =============================================
-- 4. dbgrouplaporan
-- Tidak ada group header di .fr3 - flat transactional list
-- =============================================
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020102');
DELETE FROM dbgrouplaporan WHERE id_laporan = @IdLap;
GO

-- =============================================
-- 5. dbparameterlaporan (filter parameters for stored procedures)
-- Parameters: @Perkiraan, @TglAwal, @TglAkhir, @Divisi
-- Konfigurasi browse:
--   - Perkiraan: default browse perkiraan (no kode_browse needed)
--   - Divisi:    KodeBrows=1004 (dbDevisi table, field 'Devisi')
--   - Delphi ref: FrmReportPreview.pas line 6956/6977: KodeBrows:=1004
-- =============================================
DECLARE @IdLap INT;
SET @IdLap = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020102');
DELETE FROM dbparameterlaporan WHERE id_laporan = @IdLap;
INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, konfigurasi, posisi) VALUES
    (@IdLap, 'Perkiraan',  'Perkiraan Bank', 'browse', 0, NULL, NULL,                        1),
    (@IdLap, 'TglAwal',   'Tanggal Awal',   'date',   1, NULL, NULL,                        2),
    (@IdLap, 'TglAkhir',  'Tanggal Akhir',  'date',   1, NULL, NULL,                        3),
    (@IdLap, 'Divisi',    'Divisi',         'browse', 0, NULL, '{"kode_browse":"1004"}',    4);
GO

-- ============================================================
-- DONE: Report 020102 - Bank Harian
-- ============================================================
