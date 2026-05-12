# Delphi Traceability Analyzer

Skill untuk menganalisis codebase Delphi dan membuat traceability matrix untuk migrasi ke Laravel.

## Penggunaan

### Via Claude Code

Skill ini akan otomatis aktif saat bekerja di folder `D:\Koperasi`. Ketik:

```
Scan file MyProcedure.pas dan buat traceability matrix
```

### Via Python Script (Manual)

```bash
# Scan Delphi files
python scan_delphi.py "D:\Koperasi\KSP\Application\Unit" -o traceability.json -m

# Generate Markdown only
python generate_markdown.py traceability.json -o traceability.md

# Validate migration
python validate_migration.py traceability.json
```

## Output

### JSON Format

```json
{
  "project": "KSP",
  "scan_date": "2026-04-10T10:00:00",
  "modules": [...],
  "summary": {
    "total_functions": 60,
    "validation": 15,
    "business_logic": 25,
    "database": 12,
    "utility": 8
  }
}
```

### Markdown Format

File markdown berisi:
- Summary statistics
- Category breakdown
- Functions checklist dengan status

## Kategori Function

| Kategori | Pattern |
|----------|---------|
| Validation | `Cek*`, `Check*`, `Validate*`, `Is*`, `Can*` |
| Business Logic | Default untuk perhitungan & workflow |
| Database | Menggunakan `TADOQuery`, `SQL.Add` |
| Utility | Formatting, conversion (`*Format*`, `*Convert*`) |
| Logging | `*Log*`, `*History*`, `*Audit*` |

## File Location

Skill ini terinstall di:
```
D:\Koperasi\.claude\skills\delphi-traceability-analyzer\
```

## Referensi

- `references/pattern-mapping.md` - Mapping Delphi ke Laravel
- `references/delphi-patterns.md` - Pattern Delphi yang umum
