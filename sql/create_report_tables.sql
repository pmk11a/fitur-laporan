-- ============================================================
-- Database Tables for Report Admin System
-- ============================================================

-- 1. dbLabelGrup - Label Group Mapping
IF OBJECT_ID('dbLabelGrup', 'U') IS NOT NULL DROP TABLE dbLabelGrup;
CREATE TABLE dbLabelGrup (
    id INT IDENTITY(1,1) PRIMARY KEY,
    field_name NVARCHAR(100) NOT NULL,
    field_value NVARCHAR(255) NOT NULL,
    label NVARCHAR(255) NOT NULL,
    aktif BIT DEFAULT 1,
    sort_order INT DEFAULT 0
);

-- 2. dbmasterlaporan - Master Report Data
IF OBJECT_ID('dbmasterlaporan', 'U') IS NOT NULL DROP TABLE dbmasterlaporan;
CREATE TABLE dbmasterlaporan (
    id_laporan INT IDENTITY(1,1) PRIMARY KEY,
    KODEMENU NVARCHAR(50) NULL,
    nama_laporan NVARCHAR(255) NOT NULL,
    deskripsi NVARCHAR(MAX) NULL,
    query_sumber_data NVARCHAR(MAX) NULL,
    status_aktif BIT DEFAULT 1,
    footer_bands NVARCHAR(MAX) NULL,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE()
);

-- 3. PARAMETER_LAPORAN - Report Parameters/Filters
IF OBJECT_ID('PARAMETER_LAPORAN', 'U') IS NOT NULL DROP TABLE PARAMETER_LAPORAN;
CREATE TABLE PARAMETER_LAPORAN (
    id_parameter INT IDENTITY(1,1) PRIMARY KEY,
    id_laporan INT NOT NULL,
    nama_filter NVARCHAR(100) NOT NULL,
    label NVARCHAR(255) NULL,
    tipe_input NVARCHAR(50) NOT NULL DEFAULT 'text',
    wajib_isi BIT DEFAULT 0,
    nilai_default NVARCHAR(MAX) NULL,
    posisi INT DEFAULT 0,
    konfigurasi NVARCHAR(MAX) NULL,
    deskripsi NVARCHAR(MAX) NULL,
    created_at DATETIME DEFAULT GETDATE()
);

-- 4. dbquerylaporan - Report Datasets
IF OBJECT_ID('dbquerylaporan', 'U') IS NOT NULL DROP TABLE dbquerylaporan;
CREATE TABLE dbquerylaporan (
    id_query INT IDENTITY(1,1) PRIMARY KEY,
    id_laporan INT NOT NULL,
    nama_dataset NVARCHAR(100) NOT NULL,
    query_sumber_data NVARCHAR(MAX) NOT NULL,
    deskripsi NVARCHAR(MAX) NULL,
    urutan INT DEFAULT 0
);

-- 5. dbkolomlaporan - Report Columns Configuration
IF OBJECT_ID('dbkolomlaporan', 'U') IS NOT NULL DROP TABLE dbkolomlaporan;
CREATE TABLE dbkolomlaporan (
    id_kolom INT IDENTITY(1,1) PRIMARY KEY,
    id_laporan INT NOT NULL,
    nama_dataset NVARCHAR(100) NOT NULL,
    nama_kolom NVARCHAR(100) NOT NULL,
    label_tampil NVARCHAR(255) NOT NULL,
    urutan_tampil INT DEFAULT 0,
    format_type NVARCHAR(50) DEFAULT 'text',
    alignment NVARCHAR(20) DEFAULT 'left',
    is_summable BIT DEFAULT 0,
    is_visible BIT DEFAULT 1,
    deskripsi NVARCHAR(MAX) NULL
);

-- 6. dbgrouplaporan - Report Grouping Configuration
IF OBJECT_ID('dbgrouplaporan', 'U') IS NOT NULL DROP TABLE dbgrouplaporan;
CREATE TABLE dbgrouplaporan (
    id_group INT IDENTITY(1,1) PRIMARY KEY,
    id_laporan INT NOT NULL,
    group_level INT NOT NULL,
    group_field NVARCHAR(100) NULL,
    field_value NVARCHAR(255) NULL,
    label NVARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    show_subtotal BIT DEFAULT 1,
    style_config NVARCHAR(MAX) NULL,
    special_handling NVARCHAR(50) DEFAULT 'default',
    config_json NVARCHAR(MAX) NULL,
    deskripsi NVARCHAR(MAX) NULL
);

-- 7. KOMPONEN_LAPORAN - Report Layout Components
IF OBJECT_ID('KOMPONEN_LAPORAN', 'U') IS NOT NULL DROP TABLE KOMPONEN_LAPORAN;
CREATE TABLE KOMPONEN_LAPORAN (
    id_komponen INT IDENTITY(1,1) PRIMARY KEY,
    id_laporan INT NOT NULL,
    tipe_band NVARCHAR(50) NOT NULL,
    konfigurasi_layout NVARCHAR(MAX) NULL,
    urutan_tampil INT DEFAULT 0,
    deskripsi NVARCHAR(MAX) NULL
);

-- ============================================================
-- Foreign Keys & Indexes
-- ============================================================

ALTER TABLE PARAMETER_LAPORAN ADD CONSTRAINT FK_PARAMETER_LAPORAN_MASTER
    FOREIGN KEY (id_laporan) REFERENCES dbmasterlaporan(id_laporan) ON DELETE CASCADE;

ALTER TABLE dbquerylaporan ADD CONSTRAINT FK_DBQUERY_LAPORAN_MASTER
    FOREIGN KEY (id_laporan) REFERENCES dbmasterlaporan(id_laporan) ON DELETE CASCADE;

ALTER TABLE dbkolomlaporan ADD CONSTRAINT FK_DBKOLOM_LAPORAN_MASTER
    FOREIGN KEY (id_laporan) REFERENCES dbmasterlaporan(id_laporan) ON DELETE CASCADE;

ALTER TABLE dbgrouplaporan ADD CONSTRAINT FK_DBGROUP_LAPORAN_MASTER
    FOREIGN KEY (id_laporan) REFERENCES dbmasterlaporan(id_laporan) ON DELETE CASCADE;

ALTER TABLE KOMPONEN_LAPORAN ADD CONSTRAINT FK_KOMPONEN_LAPORAN_MASTER
    FOREIGN KEY (id_laporan) REFERENCES dbmasterlaporan(id_laporan) ON DELETE CASCADE;

-- Indexes
CREATE INDEX IX_PARAMETER_LAPORAN_ID_LAPORAN ON PARAMETER_LAPORAN(id_laporan);
CREATE INDEX IX_DBQUERY_LAPORAN_ID_LAPORAN ON dbquerylaporan(id_laporan);
CREATE INDEX IX_DBKOLOM_LAPORAN_ID_LAPORAN ON dbkolomlaporan(id_laporan);
CREATE INDEX IX_DBGROUP_LAPORAN_ID_LAPORAN ON dbgrouplaporan(id_laporan);
CREATE INDEX IX_KOMPONEN_LAPORAN_ID_LAPORAN ON KOMPONEN_LAPORAN(id_laporan);
CREATE INDEX IX_DBLABELGRUP_FIELD_NAME ON dbLabelGrup(field_name);

-- ============================================================
-- Sample Data: dbLabelGrup
-- ============================================================
INSERT INTO dbLabelGrup (field_name, field_value, label, aktif, sort_order) VALUES
-- Format types
('format_type', 'text', 'Text', 1, 1),
('format_type', 'number', 'Number', 1, 2),
('format_type', 'currency', 'Currency', 1, 3),
('format_type', 'date', 'Date', 1, 4),
('format_type', 'datetime', 'DateTime', 1, 5),
('format_type', 'percentage', 'Percentage', 1, 6),
-- Alignment
('alignment', 'left', 'Left', 1, 1),
('alignment', 'center', 'Center', 1, 2),
('alignment', 'right', 'Right', 1, 3),
-- Input types
('tipe_input', 'text', 'Text', 1, 1),
('tipe_input', 'number', 'Number', 1, 2),
('tipe_input', 'date', 'Date', 1, 3),
('tipe_input', 'daterange', 'Date Range', 1, 4),
('tipe_input', 'select', 'Select', 1, 5),
('tipe_input', 'multiselect', 'Multi Select', 1, 6),
('tipe_input', 'browse', 'Browse', 1, 7),
('tipe_input', 'checkbox', 'Checkbox', 1, 8),
-- Band types
('tipe_band', 'title', 'Title', 1, 1),
('tipe_band', 'header', 'Header', 1, 2),
('tipe_band', 'detail', 'Detail', 1, 3),
('tipe_band', 'group_footer', 'Group Footer', 1, 4),
('tipe_band', 'summary', 'Summary', 1, 5),
('tipe_band', 'footer', 'Footer', 1, 6),
-- Special handling
('special_handling', 'default', 'Default', 1, 1),
('special_handling', 'page_break', 'Page Break', 1, 2),
('special_handling', 'cumulative', 'Cumulative', 1, 3),
('special_handling', 'percentage_of_total', 'Percentage of Total', 1, 4),
('special_handling', 'running_balance', 'Running Balance', 1, 5),
('special_handling', 'debit_credit', 'Debit/Credit', 1, 6);

-- ============================================================
-- Sample Data: dbmasterlaporan (Reports)
-- ============================================================
INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, query_sumber_data, status_aktif, footer_bands) VALUES
('NERACA', 'Neraca', 'Laporan Neraca', NULL, 1, '[{"type":"summary"},{"type":"footer"}]'),
('LABA_RUGI', 'Laba Rugi', 'Laporan Laba Rugi', NULL, 1, '[{"type":"summary"}]'),
('ARUS_KAS', 'Arus Kas', 'Laporan Arus Kas', NULL, 1, '[{"type":"summary"},{"type":"footer"}]'),
('BUKU_BESAR', 'Buku Besar', 'Laporan Buku Besar', NULL, 1, '[{"type":"group_footer"}]'),
('JURNAL', 'Jurnal', 'Daftar Jurnal', NULL, 1, NULL);

-- ============================================================
-- Sample Data: PARAMETER_LAPORAN (Filters)
-- ============================================================
-- Neraca filters
INSERT INTO PARAMETER_LAPORAN (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi, konfigurasi, deskripsi) VALUES
(1, 'tanggal', 'Tanggal', 'date', 1, NULL, 1, '{"format":"YYYY-MM-DD"}', 'Tanggal saldo neraca'),
(1, 'unit', 'Unit', 'browse', 0, NULL, 2, '{"browse_type":"unit","multiple":false}', 'Unit/Bagian'),
(1, 'matauang', 'Mata Uang', 'select', 0, 'IDR', 3, '{"options":[{"value":"IDR","label":"Rupiah"},{"value":"USD","label":"Dollar"}]}', 'Mata uang pelaporan');

-- Laba Rugi filters
INSERT INTO PARAMETER_LAPORAN (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi, konfigurasi, deskripsi) VALUES
(2, 'tanggal_awal', 'Tanggal Awal', 'date', 1, NULL, 1, NULL, 'Tanggal awal periode'),
(2, 'tanggal_akhir', 'Tanggal Akhir', 'date', 1, NULL, 2, NULL, 'Tanggal akhir periode'),
(2, 'unit', 'Unit', 'browse', 0, NULL, 3, '{"browse_type":"unit"}', 'Unit/Bagian');

-- Buku Besar filters
INSERT INTO PARAMETER_LAPORAN (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi, konfigurasi, deskripsi) VALUES
(4, 'tanggal_awal', 'Tanggal Awal', 'date', 1, NULL, 1, NULL, 'Tanggal awal periode buku besar'),
(4, 'tanggal_akhir', 'Tanggal Akhir', 'date', 1, NULL, 2, NULL, 'Tanggal akhir periode buku besar'),
(4, 'akun', 'Akun', 'browse', 1, NULL, 3, '{"browse_type":"akun","multiple":true}', 'Kode akun yang ditampilkan');

-- Jurnal filters
INSERT INTO PARAMETER_LAPORAN (id_laporan, nama_filter, label, tipe_input, wajib_isi, nilai_default, posisi, konfigurasi, deskripsi) VALUES
(5, 'tanggal_awal', 'Tanggal Awal', 'date', 1, NULL, 1, NULL, 'Tanggal awal pencarian jurnal'),
(5, 'tanggal_akhir', 'Tanggal Akhir', 'date', 1, NULL, 2, NULL, 'Tanggal akhir pencarian jurnal'),
(5, 'nomor', 'Nomor Jurnal', 'text', 0, NULL, 3, NULL, 'Filter nomor jurnal (opsional)');

-- ============================================================
-- Sample Data: dbquerylaporan (Datasets)
-- ============================================================
-- Neraca datasets
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan) VALUES
(1, 'neraca_umum', 'SELECT KODEAKUN, NAMA AKUN, MAX(DEBIT) AS DEBIT, MAX(KREDIT) AS KREDIT FROM JURNAL_DETAIL GROUP BY KODEAKUN, NAMA', 'Data Neraca Umum', 1),
(1, 'neraca_konsolidasi', 'SELECT KODEAKUN, NAMA AKUN, SUM(DEBIT) AS DEBIT, SUM(KREDIT) AS KREDIT FROM JURNAL_DETAIL GROUP BY KODEAKUN, NAMA', 'Data Konsolidasi', 2);

-- Laba Rugi datasets
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan) VALUES
(2, 'laba_rugi', 'SELECT KODEAKUN, NAMA AKUN, SUM(DEBIT-KREDIT) AS NILAI FROM JURNAL_DETAIL GROUP BY KODEAKUN, NAMA', 'Data Laba Rugi', 1);

-- Buku Besar datasets
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan) VALUES
(4, 'buku_besar', 'SELECT TANGGAL, NOMOR, URAIAN, DEBIT, KREDIT, SALDO FROM JURNAL_DETAIL', 'Detail Buku Besar', 1);

-- Jurnal datasets
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan) VALUES
(5, 'jurnal', 'SELECT TANGGAL, NOMOR, URAIAN, KODEAKUN, DEBIT, KREDIT FROM JURNAL', 'Header Jurnal', 1),
(5, 'jurnal_detail', 'SELECT NOMOR, KODEAKUN, NAMA AKUN, DEBIT, KREDIT FROM JURNAL_DETAIL', 'Detail Jurnal', 2);

-- ============================================================
-- Sample Data: dbkolomlaporan (Columns)
-- ============================================================
-- Neraca columns
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible, deskripsi) VALUES
(1, 'neraca_umum', 'KODEAKUN', 'Kode Akun', 1, 'text', 'left', 0, 1, 'Kode akun perkiraan'),
(1, 'neraca_umum', 'AKUN', 'Nama Akun', 2, 'text', 'left', 0, 1, 'Nama akun perkiraan'),
(1, 'neraca_umum', 'DEBIT', 'Debit', 3, 'currency', 'right', 1, 1, 'Saldo debet'),
(1, 'neraca_umum', 'KREDIT', 'Kredit', 4, 'currency', 'right', 1, 1, 'Saldo kredit');

-- Laba Rugi columns
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible, deskripsi) VALUES
(2, 'laba_rugi', 'KODEAKUN', 'Kode Akun', 1, 'text', 'left', 0, 1, 'Kode akun perkiraan'),
(2, 'laba_rugi', 'AKUN', 'Nama Akun', 2, 'text', 'left', 0, 1, 'Nama akun perkiraan'),
(2, 'laba_rugi', 'NILAI', 'Nilai', 3, 'currency', 'right', 1, 1, 'Nilai pendpatan/beban');

-- Buku Besar columns
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible, deskripsi) VALUES
(4, 'buku_besar', 'TANGGAL', 'Tanggal', 1, 'date', 'center', 0, 1, 'Tanggal transaksi'),
(4, 'buku_besar', 'NOMOR', 'Nomor', 2, 'text', 'center', 0, 1, 'Nomor bukti'),
(4, 'buku_besar', 'URAIAN', 'Uraian', 3, 'text', 'left', 0, 1, 'Uraian transaksi'),
(4, 'buku_besar', 'DEBIT', 'Debit', 4, 'currency', 'right', 1, 1, 'Jumlah debet'),
(4, 'buku_besar', 'KREDIT', 'Kredit', 5, 'currency', 'right', 1, 1, 'Jumlah kredit'),
(4, 'buku_besar', 'SALDO', 'Saldo', 6, 'currency', 'right', 1, 1, 'Saldo berjalan');

-- Jurnal columns
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, urutan_tampil, format_type, alignment, is_summable, is_visible, deskripsi) VALUES
(5, 'jurnal', 'TANGGAL', 'Tanggal', 1, 'date', 'center', 0, 1, 'Tanggal jurnal'),
(5, 'jurnal', 'NOMOR', 'Nomor', 2, 'text', 'center', 0, 1, 'Nomor jurnal'),
(5, 'jurnal', 'URAIAN', 'Uraian', 3, 'text', 'left', 0, 1, 'Uraian jurnal'),
(5, 'jurnal_detail', 'KODEAKUN', 'Akun', 4, 'text', 'left', 0, 1, 'Kode akun'),
(5, 'jurnal_detail', 'DEBIT', 'Debit', 5, 'currency', 'right', 1, 1, 'Jumlah debet'),
(5, 'jurnal_detail', 'KREDIT', 'Kredit', 6, 'currency', 'right', 1, 1, 'Jumlah kredit');

-- ============================================================
-- Sample Data: dbgrouplaporan (Groups)
-- ============================================================
-- Neraca grouping by account type
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, sort_order, show_subtotal, special_handling, deskripsi) VALUES
(1, 1, 'TIPE', 'AKTIVA', 'AKTIVA', 1, 1, 'default', 'Group utama aktiva'),
(1, 1, 'TIPE', 'PASSIVA', 'PASSIVA', 2, 1, 'default', 'Group utama passiva'),
(1, 2, 'KELOMPOK', 'LANCAR', 'Aktiva Lancar', 1, 1, 'default', 'Sub-group aktiva lancar'),
(1, 2, 'KELOMPOK', 'TETAP', 'Aktiva Tetap', 2, 1, 'default', 'Sub-group aktiva tetap');

-- Laba Rugi grouping
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, sort_order, show_subtotal, special_handling, deskripsi) VALUES
(2, 1, 'TIPE', 'PENDAPATAN', 'PENDAPATAN', 1, 1, 'default', 'Group pendapatan'),
(2, 1, 'TIPE', 'BEBAN', 'BEBAN', 2, 1, 'default', 'Group beban');

-- ============================================================
-- Sample Data: KOMPONEN_LAPORAN (Layout Components)
-- ============================================================
-- Neraca layout
INSERT INTO KOMPONEN_LAPORAN (id_laporan, tipe_band, konfigurasi_layout, urutan_tampil, deskripsi) VALUES
(1, 'title', '{"height":50,"align":"center","font_size":14,"bold":true}', 1, 'Judul laporan'),
(1, 'header', '{"height":30,"repeat_on_page":true}', 2, 'Header kolom'),
(1, 'detail', '{"height":20}', 3, 'Detail baris data'),
(1, 'summary', '{"height":30,"bold":true}', 4, 'Summary total'),
(1, 'footer', '{"height":25}', 5, 'Footer laporan');

-- Laba Rugi layout
INSERT INTO KOMPONEN_LAPORAN (id_laporan, tipe_band, konfigurasi_layout, urutan_tampil, deskripsi) VALUES
(2, 'title', '{"height":50,"align":"center","font_size":14,"bold":true}', 1, 'Judul laporan'),
(2, 'header', '{"height":30,"repeat_on_page":true}', 2, 'Header kolom'),
(2, 'detail', '{"height":20}', 3, 'Detail baris data'),
(2, 'summary', '{"height":30,"bold":true}', 4, 'Summary total');

-- Buku Besar layout
INSERT INTO KOMPONEN_LAPORAN (id_laporan, tipe_band, konfigurasi_layout, urutan_tampil, deskripsi) VALUES
(4, 'title', '{"height":50}', 1, 'Judul laporan'),
(4, 'header', '{"height":30}', 2, 'Header kolom'),
(4, 'group_footer', '{"height":25,"show_saldo":true}', 3, 'Footer per grup (saldo)'),
(4, 'detail', '{"height":20}', 4, 'Detail baris transaksi'),
(4, 'summary', '{"height":30}', 5, 'Summary total');

-- Jurnal layout
INSERT INTO KOMPONEN_LAPORAN (id_laporan, tipe_band, konfigurasi_layout, urutan_tampil, deskripsi) VALUES
(5, 'title', '{"height":50}', 1, 'Judul laporan'),
(5, 'header', '{"height":30,"repeat_on_page":true}', 2, 'Header kolom'),
(5, 'detail', '{"height":20}', 3, 'Detail baris jurnal'),
(5, 'footer', '{"height":25}', 4, 'Footer laporan');

-- ============================================================
-- Verification
-- ============================================================
SELECT 'dbLabelGrup' as [Table], COUNT(*) as [Count] FROM dbLabelGrup
UNION ALL SELECT 'dbmasterlaporan', COUNT(*) FROM dbmasterlaporan
UNION ALL SELECT 'PARAMETER_LAPORAN', COUNT(*) FROM PARAMETER_LAPORAN
UNION ALL SELECT 'dbquerylaporan', COUNT(*) FROM dbquerylaporan
UNION ALL SELECT 'dbkolomlaporan', COUNT(*) FROM dbkolomlaporan
UNION ALL SELECT 'dbgrouplaporan', COUNT(*) FROM dbgrouplaporan
UNION ALL SELECT 'KOMPONEN_LAPORAN', COUNT(*) FROM KOMPONEN_LAPORAN;