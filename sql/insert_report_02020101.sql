-- =====================================================
-- Report: Penerimaan Kas
-- KODEMENU (web URL): 02020101
-- Stored Procedure: Sp_LapJurnal 'BKM'
-- Filter: Devisi, TglAwal, TglAkhir
-- =====================================================

DECLARE @ID_LAPORAN INT

-- 1. Cek apakah 02020101 sudah ada di dbmasterlaporan
IF EXISTS (SELECT 1 FROM dbmasterlaporan WHERE KODEMENU = '02020101')
BEGIN
    SET @ID_LAPORAN = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '02020101')
END
ELSE
BEGIN
    -- Insert baru jika belum ada
    INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, status_aktif, footer_bands)
    VALUES ('02020101', 'Penerimaan Kas', 'Laporan Jurnal Penerimaan Kas (BKM)', 1, '[{"type":"summary"}]')

    SET @ID_LAPORAN = SCOPE_IDENTITY()
END

-- 2. Hapus data lama jika ada (untuk clean update)
DELETE FROM dbgrouplaporan WHERE id_laporan = @ID_LAPORAN
DELETE FROM dbkolomlaporan WHERE id_laporan = @ID_LAPORAN
DELETE FROM dbquerylaporan WHERE id_laporan = @ID_LAPORAN
DELETE FROM dbparameterlaporan WHERE id_laporan = @ID_LAPORAN

-- 3. Insert dbparameterlaporan (3 params)
INSERT INTO dbparameterlaporan (id_laporan, posisi, nama_filter, label, tipe_input, wajib_isi, nilai_default)
VALUES (@ID_LAPORAN, 1, 'Devisi', 'Divisi', 'combobox', 1, '01')

INSERT INTO dbparameterlaporan (id_laporan, posisi, nama_filter, label, tipe_input, wajib_isi, nilai_default)
VALUES (@ID_LAPORAN, 2, 'TglAwal', 'Tgl Awal', 'date', 1, NULL)

INSERT INTO dbparameterlaporan (id_laporan, posisi, nama_filter, label, tipe_input, wajib_isi, nilai_default)
VALUES (@ID_LAPORAN, 3, 'TglAkhir', 'Tgl Akhir', 'date', 1, NULL)

-- 4. Insert dbquerylaporan
INSERT INTO dbquerylaporan (id_laporan, nama_dataset, deskripsi, query_sumber_data, urutan)
VALUES (@ID_LAPORAN, 'JurnalPenerimaan', 'Dataset Jurnal Penerimaan Kas', 'EXEC Sp_LapJurnal ''BKM'', @Devisi, @TglAwal, @TglAkhir', 1)

-- 5. Insert dbkolomlaporan (8 columns)
INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, urutan_tampil)
VALUES (@ID_LAPORAN, 'JurnalPenerimaan', 'NoBukti', 'No Bukti', 'text', 'left', 0, 1)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, urutan_tampil)
VALUES (@ID_LAPORAN, 'JurnalPenerimaan', 'Tanggal', 'Tanggal', 'date', 'center', 0, 2)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, urutan_tampil)
VALUES (@ID_LAPORAN, 'JurnalPenerimaan', 'Devisi', 'Devisi', 'text', 'center', 0, 3)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, urutan_tampil)
VALUES (@ID_LAPORAN, 'JurnalPenerimaan', 'Perkiraan', 'Perkiraan', 'text', 'left', 0, 4)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, urutan_tampil)
VALUES (@ID_LAPORAN, 'JurnalPenerimaan', 'Lawan', 'Lawan', 'text', 'left', 0, 5)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, urutan_tampil)
VALUES (@ID_LAPORAN, 'JurnalPenerimaan', 'Keterangan', 'Keterangan', 'text', 'left', 0, 6)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, urutan_tampil)
VALUES (@ID_LAPORAN, 'JurnalPenerimaan', 'Debet', 'Debet', 'number', 'right', 1, 7)

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, urutan_tampil)
VALUES (@ID_LAPORAN, 'JurnalPenerimaan', 'Kredit', 'Kredit', 'number', 'right', 1, 8)

-- 6. Insert dbgrouplaporan
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, show_subtotal, sort_order)
VALUES (@ID_LAPORAN, 1, 'NoBukti', '', 'Bukti Kas Masuk', 1, 1)

PRINT 'Report 02020101 berhasil!'
PRINT 'URL: /reports/02020101'