-- =====================================================
-- Tambah kolom config_json dan update dataset 020101
-- =====================================================

USE dbwbcp2;

-- Step 1: Tambah kolom config_json
ALTER TABLE dbquerylaporan ADD config_json NVARCHAR(MAX) NULL;
PRINT 'Kolom config_json berhasil ditambahkan';

-- Step 2: Update T1 = summary
UPDATE dbquerylaporan
SET config_json = '{"display_role": "summary", "summary_layout": "grid_2col"}'
WHERE id_laporan = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020101')
  AND nama_dataset = 'T1';
PRINT 'T1 diupdate: display_role=summary, summary_layout=grid_2col';

-- Step 3: Update T2 = detail
UPDATE dbquerylaporan
SET config_json = '{"display_role": "detail"}'
WHERE id_laporan = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020101')
  AND nama_dataset = 'T2';
PRINT 'T2 diupdate: display_role=detail';

-- Step 4: Verify
SELECT nama_dataset, config_json
FROM dbquerylaporan
WHERE id_laporan = (SELECT id_laporan FROM dbmasterlaporan WHERE KODEMENU = '020101');
GO