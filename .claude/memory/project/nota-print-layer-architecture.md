---
name: nota-print-layer-architecture
description: Print Layer B2 architecture — Hybrid (1 master Blade + JSON config) for Nota documents
metadata:
  type: project
---

# Nota Print Layer Architecture

## Decision (2026-06-24)

Print Layer untuk Nota (.fr3 migration) menggunakan **Hybrid approach**: 1 master Blade template + JSON config per Nota di database.

## Why Hybrid

- 152 Nota files dengan layout varied (logo, materai, thermal/A4, multi-bahasa)
- Opsi 1 (config-driven 100%) over-engineered untuk variasi ini
- Opsi 3 (hybrid) balance: Blade yang powerful, config yang manageable
- Effort 1-2 hari vs 3-4 minggu untuk full config-driven

## Key Files

| File | Peran |
|------|-------|
| `be-fitur/app/Models/NotaTemplate.php` | Eloquent model untuk `dbnotatemplate` |
| `be-fitur/app/Services/NotaRenderer.php` | Format: currency (Rp), date, line#, aggregate |
| `be-fitur/app/Services/NotaService.php` | Load config + run SQL + apply conditional |
| `be-fitur/app/Http/Controllers/NotaController.php` | Endpoint `GET /api/nota/{kode}/print` |
| `be-fitur/resources/views/nota/master.blade.php` | Master template |
| `be-fitur/resources/views/nota/partials/*.blade.php` | 6 partials (header, kepada-yth, info-baris, table, footer-summary, terbilang, signature) |
| `sql/dbcreate_dbnotatemplate.sql` | Schema tabel template |
| `sql/dbinsert_nota_jual_template.sql` | Config + SQL untuk Nota Jual |

## Library

`barryvdh/laravel-dompdf` v3.1.2

## Endpoint Pattern

```
GET /api/nota/{kode}/print?NOBUKTI=X
```

## Adding New Nota

Cukup INSERT ke `dbnotatemplate` — tidak perlu edit code:
- `kode_nota` (unique): identifier seperti 'NOTA_PO'
- `query_header`: SELECT header data dengan @NOBUKTI binding
- `query_detail`: SELECT detail rows
- `config_json`: header/columns/footer_summary/terbilang/signatures

Effort: ~30-60 menit per Nota baru.

## Schema Tabel dbnotatemplate

```sql
CREATE TABLE dbnotatemplate (
    id_template INT IDENTITY(1,1) PRIMARY KEY,
    kode_nota VARCHAR(50) UNIQUE NOT NULL,
    nama_nota VARCHAR(100) NOT NULL,
    paper_size VARCHAR(20) DEFAULT 'A4',
    orientation VARCHAR(20) DEFAULT 'portrait',
    margins VARCHAR(20) DEFAULT '10mm',
    font_family VARCHAR(50) DEFAULT 'Tahoma',
    font_size VARCHAR(10) DEFAULT '10pt',
    config_json NVARCHAR(MAX) NOT NULL,
    query_header NVARCHAR(MAX) NULL,
    query_detail NVARCHAR(MAX) NULL,
    query_params NVARCHAR(MAX) NULL,
    aktif BIT DEFAULT 1
);
```

## Related

- Source: [[report-setup-pattern]] (config-driven pattern reference)
- Original .fr3 files: `pwt/MyProject/Nota/*.fr3` (152 files)
- See also: [[fluffy-bee-report-engine]]
