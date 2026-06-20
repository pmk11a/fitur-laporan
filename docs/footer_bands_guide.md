# Footer Bands - Configuration Guide

`footer_bands` is a JSON field stored in `dbmasterlaporan.footer_bands` that controls the header title, summary footer table, and signature sections of a report.

## Stored Location

| Table | Column |
|---|---|
| `dbmasterlaporan` | `footer_bands` (JSON) |

## Full JSON Structure

```json
{
  "bands": {
    "title": {
      "enabled": true,
      "content": "LAPORAN BANK HARIAN",
      "align": "center"
    },
    "pageHeader": {
      "enabled": true,
      "content": ""
    },
    "pageFooter": {
      "enabled": true,
      "content": ""
    },
    "summary": {
      "enabled": true,
      "layout": {
        "columns": 3,
        "alignment": "spread"
      },
      "footer_table": {
        "rows": ["Jumlah", "Saldo Awal", "Saldo Akhir", "Kontrol"],
        "columns": ["Penerimaan", "Pengeluaran"]
      },
      "signatures": [
        { "label": "Pimpinan", "position": "left" },
        { "label": "Kontrol", "position": "center" },
        { "label": "Kasir", "position": "right" }
      ]
    }
  }
}
```

## Field Reference

### bands.title
Controls the report title banner at the top.

| Field | Type | Default | Description |
|---|---|---|---|
| `enabled` | boolean | `true` | Show/hide title |
| `content` | string | `""` | Title text |
| `align` | string | `"center"` | `"left"`, `"center"`, `"right"` |

### bands.pageHeader
Optional page header text.

| Field | Type | Default | Description |
|---|---|---|---|
| `enabled` | boolean | `true` | Show/hide page header |
| `content` | string | `""` | Header text content |

### bands.pageFooter
Optional page footer text.

| Field | Type | Default | Description |
|---|---|---|---|
| `enabled` | boolean | `true` | Show/hide page footer |
| `content` | string | `""` | Footer text content |

### bands.summary
Controls the summary section at the bottom of the report.

#### summary.enabled
Boolean. Show/hide the entire summary section (footer table + signatures).

#### summary.layout
| Field | Type | Default | Description |
|---|---|---|---|
| `columns` | number | `3` | Number of signature columns |
| `alignment` | string | `"spread"` | Signature alignment mode |

#### summary.footer_table
Controls the matrix-style footer table.

| Field | Type | Required | Description |
|---|---|---|---|
| `rows` | string[] | Yes | Row labels (display names) |
| `columns` | string[] | Yes | Column labels (display names) |

**Row data mapping logic** (in `[kode].vue` line 631-635):

| Row Label | Data Source |
|---|---|
| `"Jumlah"` | Sum of T2 detail column matching `columns[i]` |
| `"Saldo Awal"` | `t1.SaldoAwalD` (for D column) / `t1.SaldoAwalK` (for K column) |
| `"Saldo Akhir"` | `t1.SaldoAkhirD` / `t1.SaldoAkhirK` |
| `"Kontrol"` | `t1.TotalD` / `t1.TotalK` |
| Any other | `0` |

**Column data mapping logic**: Column label is matched against `dbkolomlaporan.label_tampil` for detail datasets. The matched `nama_kolom` is used to sum values from T2 rows.

#### summary.signatures
Array of signature positions at the bottom.

| Field | Type | Description |
|---|---|---|
| `label` | string | Signature label text (e.g., "Kasir") |
| `position` | string | `"left"`, `"center"`, `"right"` |

Position controls text alignment:
- `"left"` -> aligned left
- `"center"` -> aligned center
- `"right"` -> aligned right (default if omitted)

## How to Edit

### Method 1: Visual Editor (Admin UI)
Navigate to Admin > Reports > select report > General Tab > Footer Bands section.

Fields available:
- **Title**: Title text input
- **Page Header**: Header text input
- **Page Footer**: Footer text input
- **Jumlah Kolom TTD**: Number of signature columns
- **Tanda Tangan**: Dynamic list - add/remove with position dropdown and label input

Save with "Simpan Footer" button.

### Method 2: Raw JSON Editor
Toggle to "Mode JSON" in the same tab. Paste/edit raw JSON. Save with "Simpan JSON" button.

### Method 3: SQL (Initial Setup)
Insert into `dbmasterlaporan.footer_bands`:

```sql
INSERT INTO dbmasterlaporan (KODEMENU, nama_laporan, footer_bands)
VALUES ('020102', 'Bank Harian',
'{"bands":{"title":{"enabled":true,"content":"LAPORAN BANK HARIAN","align":"center"},"pageHeader":{"enabled":true},"summary":{"enabled":true,"footer_table":{"rows":["Jumlah","Saldo Awal","Saldo Akhir","Kontrol"],"columns":["Penerimaan","Pengeluaran"]},"signatures":[{"label":"Pimpinan","position":"left"},{"label":"Kontrol","position":"center"},{"label":"Kasir","position":"right"}]}}}');
```

## Example Configurations

### Minimal (no footer)
```json
{
  "bands": {
    "title": { "enabled": true, "content": "My Report", "align": "center" },
    "pageHeader": { "enabled": false },
    "pageFooter": { "enabled": false },
    "summary": { "enabled": false }
  }
}
```

### With footer table only (3 columns)
```json
{
  "bands": {
    "title": { "enabled": true, "content": "Sales Report", "align": "center" },
    "summary": {
      "enabled": true,
      "footer_table": {
        "rows": ["Total", "Qty", "Rata-rata"],
        "columns": ["Jan", "Feb", "Mar", "Total"]
      },
      "signatures": []
    }
  }
}
```

### With signatures only
```json
{
  "bands": {
    "title": { "enabled": true, "content": "Approval Report", "align": "center" },
    "summary": {
      "enabled": true,
      "signatures": [
        { "label": "Disetujui oleh", "position": "left" },
        { "label": "Diverifikasi oleh", "position": "center" },
        { "label": "Direktur", "position": "right" }
      ]
    }
  }
}
```

## Code Reference

| File | Line | Purpose |
|---|---|---|
| `fe-fitur/pages/reports/[kode].vue` | 393-437 | Footer table + signatures rendering |
| `fe-fitur/pages/reports/[kode].vue` | 620-668 | `footerTable` computed + `signatureItems` computed |
| `fe-fitur/components/admin/tabs/GeneralTab.vue` | 55-118 | Visual + JSON editor UI |
| `fe-fitur/components/admin/tabs/GeneralTab.vue` | 152-163 | Default reactive structure |
| `fe-fitur/components/admin/tabs/GeneralTab.vue` | 202-207 | Save visual editor |
| `fe-fitur/components/admin/tabs/GeneralTab.vue` | 217-225 | Save raw JSON |
