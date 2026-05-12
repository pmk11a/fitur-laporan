-- =====================================================
-- Report Buku Harian - Kode: 202021
-- Buku Tambahan Baru - ACCESS: 202021
-- =====================================================

-- Delete existing config first
DECLARE @ID_LAPORAN INT
SET @ID_LAPORAN = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '202021')
IF @ID_LAPORAN IS NOT NULL
BEGIN
    DELETE FROM dbgrouplaporan WHERE id_laporan = @ID_LAPORAN
    DELETE FROM dbkolomlaporan WHERE id_laporan = @ID_LAPORAN
    DELETE FROM dbquerylaporan WHERE id_laporan = @ID_LAPORAN
    DELETE FROM dbparameterlaporan WHERE id_laporan = @ID_LAPORAN
    DELETE FROM dbmasterlaporan WHERE id_laporan = @ID_LAPORAN
END

-- 1. Insert dbmasterlaporan
INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, status_aktif, footer_bands)
VALUES ('202021', 'Buku Harian', 'Laporan Buku Harian / Buku Tambahan', 1, '[{"type":"summary"}]')

SET @ID_LAPORAN = SCOPE_IDENTITY()

-- 2. Insert dbparameterlaporan
INSERT INTO dbparameterlaporan (id_laporan, posisi, nama_filter, label, tipe_input, wajib_isi, nilai_default)
VALUES (@ID_LAPORAN, 1, 'Perkiraan2', 'Perkiraan Awal', 'browse', 1, NULL)

INSERT INTO dbparameterlaporan (id_laporan, posisi, nama_filter, label, tipe_input, wajib_isi, nilai_default)
VALUES (@ID_LAPORAN, 2, 'Perkiraan3', 'Perkiraan Akhir', 'browse', 1, NULL)

INSERT INTO dbparameterlaporan (id_laporan, posisi, nama_filter, label, tipe_input, wajib_isi, nilai_default)
VALUES (@ID_LAPORAN, 3, 'Devisi', 'Divisi', 'combobox', 0, '01')

INSERT INTO dbparameterlaporan (id_laporan, posisi, nama_filter, label, tipe_input, wajib_isi, nilai_default)
VALUES (@ID_LAPORAN, 4, 'TglAwal', 'Tanggal Awal', 'date', 1, NULL)

INSERT INTO dbparameterlaporan (id_laporan, posisi, nama_filter, label, tipe_input, wajib_isi, nilai_default)
VALUES (@ID_LAPORAN, 5, 'TglAkhir', 'Tanggal Akhir', 'date', 1, NULL)

INSERT INTO dbparameterlaporan (id_laporan, posisi, nama_filter, label, tipe_input, wajib_isi, nilai_default)
VALUES (@ID_LAPORAN, 6, 'Jurnal', 'Jurnal', 'combobox', 0, '')

-- 3. Insert dbquerylaporan
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, deskripsi, query_sumber_data, urutan)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'Dataset Buku Harian', 'EXEC sp_ReportBukuTambahan @Perkiraan2, @Perkiraan3, @TglAwal, @TglAkhir, @Devisi, @IDUser, @Jurnal', 1)

-- 4. Insert dbkolomlaporan - field names CASE SENSITIVE from SP
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'Nobukti', 'No.Bukti', 'text', 'left', 0, 1, 1)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'Tanggal', 'Tanggal', 'date', 'left', 0, 1, 2)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'Note', 'Note', 'text', 'left', 0, 0, 3)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'Keterangan', 'Keterangan', 'text', 'left', 0, 1, 4)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahkan', 'Perkiraan', 'Perkiraan', 'text', 'left', 0, 1, 5)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'Lawan', 'Lawan', 'text', 'left', 0, 1, 6)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'DK', 'DK', 'text', 'center', 0, 0, 7)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'DebetD', 'Debet', 'number', 'right', 1, 1, 8)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'Debet', 'Debet Valas', 'number', 'right', 1, 0, 9)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'Valas', 'Valas', 'text', 'center', 0, 0, 10)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'Kurs', 'Kurs', 'number', 'right', 0, 0, 11)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'KreditD', 'Kredit', 'number', 'right', 1, 1, 12)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'Kredit', 'Kredit Valas', 'number', 'right', 1, 0, 13)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'SaldoAkhir', 'Saldo', 'number', 'right', 0, 1, 14)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'SaldoAkhirD', 'Saldo Valas', 'number', 'right', 0, 0, 15)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'Devisi', 'Devisi', 'text', 'center', 0, 0, 16)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'Nourut', 'No.Urut', 'text', 'center', 0, 0, 17)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'NoACC', 'Perk', 'text', 'left', 0, 1, 18)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'Nama', 'Nama Perkiraan', 'text', 'left', 0, 1, 19)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'urut', 'Urutan', 'text', 'center', 0, 0, 20)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'NamaLawan', 'Nama Lawan', 'text', 'left', 0, 0, 21)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'qnt', 'Qnt', 'number', 'right', 0, 1, 22)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'sat', 'Satuan', 'text', 'center', 0, 0, 23)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES (@ID_LAPORAN, 'BukuTambahan', 'dept', 'Dept', 'text', 'left', 0, 0, 24)

-- 5. Insert dbgrouplaporan
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, show_subtotal, sort_order)
VALUES (@ID_LAPORAN, 1, 'NoACC', '', 'NoACC', 1, 1)

-- Verify
PRINT 'Report 202021 berhasil diinsert!'
SELECT 'dbkolomlaporan' as tbl, nama_kolom, label_tampil, is_visible FROM dbkolomlaporan WHERE id_laporan = @ID_LAPORAN ORDER BY urutan_tampil