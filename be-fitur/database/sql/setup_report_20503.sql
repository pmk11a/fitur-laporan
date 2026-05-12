-- ================================================
-- Setup Report 20503 (Neraca) - Balance Sheet
-- ================================================

-- Step 1: Check if already exists
-- SELECT * FROM dbmasterlaporan WHERE KODEMENU = '20503';

-- Insert master laporan (skip if already exists)
IF NOT EXISTS (SELECT 1 FROM dbmasterlaporan WHERE KODEMENU = '20503')
BEGIN
    INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, deskripsi, status_aktif, footer_bands)
    VALUES ('20503', 'Neraca', 'Neraca Balance Sheet - Aktiva & Pasiva', 1, NULL);
END

DECLARE @IdLap INT = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '20503');

-- Step 2: Insert parameters
DELETE FROM dbparameterlaporan WHERE id_laporan = @IdLap;

INSERT INTO dbparameterlaporan (id_laporan, nama_filter, label, tipe_input, wajib_isi, posisi, nilai_default)
VALUES
(@IdLap, 'Divisi', 'Devisi', 'dropdown', 1, 1, 'SEMUA'),
(@IdLap, 'Bulan', 'Bulan', 'number', 1, 2, NULL),
(@IdLap, 'Tahun', 'Tahun', 'number', 1, 3, NULL);

-- Step 3: Insert query datasets (2 datasets: Aktiva + Pasiva)
DELETE FROM dbquerylaporan WHERE id_laporan = @IdLap;

INSERT INTO dbquerylaporan (id_laporan, nama_dataset, query_sumber_data, deskripsi, urutan)
VALUES
(@IdLap, 'QuView3', 'EXEC sp_ReportNeracaAktiva @Divisi, @Bulan, @Tahun', 'Dataset Aktiva - Neraca', 1),
(@IdLap, 'QuView4', 'EXEC sp_ReportNeracaPasiva @Divisi, @Bulan, @Tahun', 'Dataset Pasiva - Kewajiban dan Modal', 2);

-- Step 4: Insert columns for both datasets
-- Note: dbkolomlaporan uses id_laporan + nama_dataset (no id_query)
DELETE FROM dbkolomlaporan WHERE id_laporan = @IdLap;

INSERT INTO dbkolomlaporan (id_laporan, nama_dataset, nama_kolom, label_tampil, format_type, alignment, is_summable, is_visible, urutan_tampil)
VALUES
-- QuView3 (Aktiva)
(@IdLap, 'QuView3', 'keterangan', 'Keterangan', 'text', 'left', 0, 1, 1),
(@IdLap, 'QuView3', 'jumlah1', 'Jumlah', 'number', 'right', 1, 1, 2),
(@IdLap, 'QuView3', 'grupAP1', 'Grup AP1', 'text', 'left', 0, 0, 3),
(@IdLap, 'QuView3', 'grupAP2', 'Grup AP2', 'text', 'left', 0, 0, 4),
-- QuView4 (Pasiva)
(@IdLap, 'QuView4', 'keterangan', 'Keterangan', 'text', 'left', 0, 1, 1),
(@IdLap, 'QuView4', 'jumlah1', 'Jumlah', 'number', 'right', 1, 1, 2),
(@IdLap, 'QuView4', 'grupAP1', 'Grup AP1', 'text', 'left', 0, 0, 3),
(@IdLap, 'QuView4', 'grupAP2', 'Grup AP2', 'text', 'left', 0, 0, 4);

-- Step 5: Insert grouping configuration
-- Note: dbgrouplaporan uses id_laporan + group_field (no dataset_name)
DELETE FROM dbgrouplaporan WHERE id_laporan = @IdLap;

-- Level 1: grupAP1 (parent grouping - hidden)
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, sort_order, show_subtotal, style_config)
VALUES
(@IdLap, 1, 'grupAP1', '*', 'Header Grup AP1', 1, 0, '{"visible": false}');

-- Level 2: grupAP2 (sub-group with label mapping) - QuView3
INSERT INTO dbgrouplaporan (id_laporan, group_level, group_field, field_value, label, sort_order, show_subtotal, style_config)
VALUES
(@IdLap, 2, 'grupAP2', 'A1', 'AKTIVA LANCAR', 1, 1, '{"fontStyle": "bold"}'),
(@IdLap, 2, 'grupAP2', 'A2', 'AKTIVA TETAP', 2, 1, '{"fontStyle": "bold"}'),
(@IdLap, 2, 'grupAP2', 'A3', 'AKTIVA LAIN-LAIN', 3, 1, '{"fontStyle": "bold"}'),
(@IdLap, 2, 'grupAP2', 'P1', 'KEWAJIBAN LANCAR', 4, 1, '{"fontStyle": "bold"}'),
(@IdLap, 2, 'grupAP2', 'P2', 'KEWAJIBAN TIDAK LANCAR', 5, 1, '{"fontStyle": "bold"}'),
(@IdLap, 2, 'grupAP2', 'P3', 'EKUITAS ( MODAL )', 6, 1, '{"fontStyle": "bold"}');

-- Step 6: Verify
SELECT 'Master Laporan:' as info, * FROM dbmasterlaporan WHERE KODEMENU = '20503';
SELECT 'Parameters:' as info, * FROM dbparameterlaporan WHERE id_laporan = @IdLap;
SELECT 'Datasets:' as info, * FROM dbquerylaporan WHERE id_laporan = @IdLap;
SELECT 'Columns:' as info, * FROM dbkolomlaporan WHERE id_laporan = @IdLap ORDER BY nama_dataset, urutan_tampil;
SELECT 'Grouping:' as info, * FROM dbgrouplaporan WHERE id_laporan = @IdLap ORDER BY group_level, sort_order;