-- Seeder: Nota Jual template (mirrors NOTAJUAL1.fr3 layout)
-- Template: NOTA_JUAL
-- Source tables: DBJual (header) + DBJualDet (detail) + DBCustomer + dbPerusahaan
-- Parameters: @NOBUKTI

USE dbwbcp2;
GO

DECLARE @config_json NVARCHAR(MAX) = N'{
  "header": {
    "logo_path": "/images/logo.png",
    "title": "NOTA PENJUALAN",
    "title_align": "center",
    "company_field": "perusahaan.NAMA",
    "company_address_field": "perusahaan.Alamat",
    "right_block": [
      {"label": "Nomor", "value_field": "NOBUKTI"},
      {"label": "Tanggal", "value_field": "TANGGAL", "format": "DD/MM/YYYY"},
      {"label": "Gudang", "value_field": "KODEGDG"}
    ]
  },
  "kepada_yth": {
    "label": "Kepada Yth. :",
    "fields": ["NAMACUST", "ALAMAT", "KOTA"]
  },
  "info_baris": [
    {"label": "Sales", "value_field": "NAMASLS"},
    {"label": "Jatuh Tempo", "value_field": "TGLJATUHTEMPO", "format": "DD/MM/YYYY", "suffix": ", ", "suffix2_field": "HARI", "suffix2": " hari"}
  ],
  "columns": [
    {"field": "URUT", "label": "No", "width": "8mm", "align": "center", "type": "line_number"},
    {"field": "QNT", "label": "Jml", "width": "15mm", "align": "right", "type": "number", "decimals": 0},
    {"field": "KODEBRG", "label": "Kode", "width": "20mm", "align": "center", "type": "text"},
    {"field": "NAMABRG", "label": "Nama Barang", "width": "55mm", "align": "left", "type": "text"},
    {"field": "SAT1", "label": "SAT", "width": "10mm", "align": "center", "type": "text"},
    {"field": "HARGA", "label": "Harga", "width": "20mm", "align": "right", "type": "currency"},
    {"field": "DISCTOT", "label": "Disc(Rp)", "width": "15mm", "align": "right", "type": "currency"},
    {"field": "HRGNETTO", "label": "Hrg Netto", "width": "20mm", "align": "right", "type": "currency"},
    {"field": "TOTAL", "label": "Total", "width": "25mm", "align": "right", "type": "currency"}
  ],
  "footer_summary": [
    {"label": "DISKON {DISC}%", "aggregate": {"field": "NDISKON", "op": "sum"}, "format": "currency", "prefix_field": "DISC"},
    {"label": "GRAND TOTAL", "aggregate": {"field": "TOTAL", "op": "sum"}, "format": "currency", "bold": true}
  ],
  "terbilang": {
    "label": "Terbilang",
    "field": "TERBILANG",
    "position": "left"
  },
  "signatures": [
    {"label": "Penerima", "position": "left"},
    {"label": "Hormat Kami", "position": "right"}
  ]
}';

DECLARE @query_header NVARCHAR(MAX) = N'
SELECT
    j.NOBUKTI,
    j.TANGGAL,
    j.KODEGDG,
    j.NAMACUST,
    j.KODECUST,
    ISNULL(j.ALAMAT, '''') AS ALAMAT,
    ISNULL(j.KOTA, '''') AS KOTA,
    j.NAMASLS,
    j.HARI,
    j.TGLJATUHTEMPO,
    j.DISC,
    j.TIPEBAYAR,
    j.TERBILANG,
    j.NNET,
    j.NDISKON
FROM DBJual j
WHERE j.NOBUKTI = @NOBUKTI
';

DECLARE @query_detail NVARCHAR(MAX) = N'
SELECT
    d.URUT,
    d.KODEBRG,
    b.NAMABRG,
    d.SAT1,
    d.QNT,
    d.HARGA,
    d.DISC1,
    d.DISC2,
    d.DISCTOT,
    d.HRGNETTO,
    d.TOTAL
FROM DBJualDet d
LEFT JOIN DBBARANG b ON b.KODEBRG = d.KODEBRG
WHERE d.NOBUKTI = @NOBUKTI
ORDER BY d.URUT
';

DECLARE @query_params NVARCHAR(MAX) = N'["NOBUKTI"]';

IF NOT EXISTS (SELECT 1 FROM dbnotatemplate WHERE kode_nota = 'NOTA_JUAL')
BEGIN
    INSERT INTO dbnotatemplate (
        kode_nota, nama_nota, paper_size, orientation, margins,
        font_family, font_size, config_json, query_header, query_detail,
        query_params, aktif
    )
    VALUES (
        'NOTA_JUAL', 'Nota Penjualan', 'A4', 'portrait', '10mm',
        'Tahoma', '10pt', @config_json, @query_header, @query_detail,
        @query_params, 1
    );
    PRINT 'Nota Jual template inserted';
END
ELSE
BEGIN
    UPDATE dbnotatemplate
    SET nama_nota = 'Nota Penjualan',
        config_json = @config_json,
        query_header = @query_header,
        query_detail = @query_detail,
        query_params = @query_params,
        updated_at = GETDATE()
    WHERE kode_nota = 'NOTA_JUAL';
    PRINT 'Nota Jual template updated';
END
GO
