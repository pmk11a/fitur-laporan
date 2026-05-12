# Traceability JSON Schema

Dokumentasi struktur traceability JSON yang dihasilkan oleh `delphi-traceability-analyzer`.

## Lokasi File

```
D:\Koperasi\docs\traceability\
├── {modul}.json     # Data traceability
├── {modul}.md       # Dokumentasi human-readable
└── combined.json    # Semua modul dalam satu file
```

## Struktur JSON

```json
{
  "project": "KSP",
  "scan_date": "2026-04-21",
  "module": "frmSetupperiode",
  "delphi_file": "KSP\\Application\\Periode\\frmSetupperiode.pas",
  "functions_count": 10,
  "functions": [
    {
      "name": "ViewPeriode",
      "type": "function",
      "line": 47,
      "params": ["Nama:string"],
      "returns": "Boolean",
      "category": "database",
      "business_logic": "Query dbPeriode by UserID dan set global PeriodBln, PeriodThn",
      "laravel_target": {
        "file": "App/Services/PeriodService.php",
        "method": "getCurrentPeriod",
        "status": "pending"
      }
    }
  ],
  "summary": {
    "total_functions": 10,
    "validation": 2,
    "business_logic": 5,
    "database": 3,
    "utility": 0,
    "migrated": 0,
    "pending": 10
  }
}
```

## Field Descriptions

### Root Level
| Field | Type | Description |
|-------|------|-------------|
| `project` | string | Project name (usually "KSP") |
| `scan_date` | string | Date when scan was performed (YYYY-MM-DD) |
| `module` | string | Delphi form/module name (e.g., "frmSetupperiode") |
| `delphi_file` | string | Full path to Delphi source file |
| `functions_count` | number | Total number of functions/procedures found |
| `functions` | array | List of all functions/procedures |
| `summary` | object | Summary statistics |

### Function Object
| Field | Type | Description |
|-------|------|-------------|
| `name` | string | Function/procedure name |
| `type` | string | "function" or "procedure" |
| `line` | number | Line number in Delphi file |
| `params` | array | List of parameters (e.g., ["Nama:string", "tgl:Tdatetime"]) |
| `returns` | string | Return type (for functions) |
| `category` | string | Category: validation, business_logic, database, utility, logging |
| `business_logic` | string | Description of what the function does |
| `laravel_target` | object | Laravel implementation target |

### Laravel Target Object
| Field | Type | Description |
|-------|------|-------------|
| `file` | string | Target Laravel file path |
| `method` | string | Target method name |
| `status` | string | "pending", "migrated", "verified", "not_needed" |
| `verified_date` | string | (Optional) Date when migration was verified |

## Categories

| Category | Description | Laravel Target |
|----------|-------------|----------------|
| `validation` | Input validation checks | FormRequest rules |
| `business_logic` | Core business rules | Service methods |
| `database` | DB operations | Eloquent/Query Builder |
| `utility` | Helper functions | Helper/Utility classes |
| `logging` | Logging/audit | Log facade |

## Status Values

| Status | Description |
|--------|-------------|
| `pending` | Not yet migrated (default) |
| `migrated` | Code created in Laravel |
| `verified` | Tested and verified |
| `not_needed` | UI only or handled by framework |

## Workflow Penggunaan

### 1. Sebelum Migrasi
```bash
# Baca traceability JSON
cat docs/traceability/frmsetupperiode.json

# Identifikasi function yang akan di-migrate
# Cek status: "pending" → perlu di-migrate
```

### 2. Setelah Migrasi
```json
{
  "laravel_target": {
    "file": "App/Services/PeriodService.php",
    "method": "getCurrentPeriod",
    "status": "migrated",  // ← Update dari "pending"
    "verified_date": "2026-04-21"
  }
}
```

### 3. Verifikasi
```bash
# Cek progress migrasi
python scripts/migration_progress.py
```

## Contoh Mapping

### Delphi → Laravel

| Delphi Pattern | Laravel Target |
|----------------|----------------|
| `function CekXxx(): Boolean` | ValidationService@validateXxx |
| `procedure SimpanXxx` | Service@saveXxx |
| `Query.Open/ExecSQL` | Eloquent/DB::table() |
| `FormShow` | useEffect/useQuery |
| `BitBtn1Click` | Form submit handler |
| `Application.MessageBox` | toast/notification |
