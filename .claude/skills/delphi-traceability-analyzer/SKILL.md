---
name: delphi-traceability-analyzer
description: Scan Delphi codebase (.pas files) to extract functions, procedures, and business logic. Generate traceability matrix (JSON + Markdown) mapping Delphi code to Laravel equivalents. Use this skill when migrating Delphi to Laravel, analyzing legacy code, or creating audit trails. This skill ensures NO business logic is missed during migration.
triggers:
  - user scans Delphi files (.pas, .dfm)
  - user asks for traceability matrix
  - user types "scan" with .pas file path
  - user asks to analyze Delphi code for migration
---

# Delphi Traceability Analyzer

This skill helps you migrate Delphi applications to Laravel by creating a comprehensive traceability matrix of all business logic.

## Posisi Skill

**Skill ini adalah PREPARATION SKILL untuk migrasi.**

Hubungan dengan skill lain:
```
Step 1: delphi-traceability-analyzer (SCAN)
    │
    └── Output: traceability.json
            │
            ▼
Step 2: delphi-laravel-impl (IMPLEMENT)
    │
    ├── Backend → delphi-laravel-generator patterns
    └── Frontend → React patterns
```

**Kapan Gunakan:**
- SEBELUM migrasi modul → WAJIB scan dulu dengan skill ini
- Migrasi modul → `ksp-module-impl` akan memanggil skill ini otomatis
- Audit Delphi codebase → Gunakan skill ini langsung
    │

## When to Use

Use this skill whenever:
- Migrating Delphi 6/7 to Laravel/React
- Auditing legacy Delphi codebase before migration
- Creating inventory of Delphi functions and procedures
- Validating that all business logic has been migrated
- Analyzing Delphi code patterns for documentation

## Core Workflow

### Step 1: Scan Delphi Files

Scan the Delphi source directory to extract all functions and procedures:

```
Scan directory: D:\Koperasi\KSP\Application\Unit\
Output: traceability.json
```

**⚡ AUTOMATIC DEPENDENCY TRACKING (NEW)**

The skill now automatically tracks and scans dependent forms:
- Parses `uses` clause to extract form/unit dependencies
- Parses `Application.CreateForm()` calls to find dynamically created forms
- Searches the entire project directory for dependent forms
- Builds a complete dependency tree
- **Ensures NO business logic is missed during migration**

Example output with dependency tracking:
```
[*] Dependency Tracking enabled (search root: D:\ykka\Keu-app)
[*] Scanning for form dependencies...
  [Dependency] FrmBarang -> FrmHarga (pwt/Master/Harga/FrmHarga.pas)
  [Dependency] FrmBarang -> FrmStok (pwt/Master/Stok/FrmStok.pas)

[*] Found 2 additional dependent form(s):
  + pwt/Master/Harga/FrmHarga.pas
  + pwt/Master/Stok/FrmStok.pas

[*] Dependency Tree Summary:
  FrmBarang -> [FrmHarga, FrmStok, FrmSatuan, FrmTypeBarang]
```

For each `.pas` file, extract:
- Function signatures (`function Name(params): ReturnType;`)
- Procedure signatures (`procedure Name(params);`)
- Parameter types and names
- Return types
- Line numbers
- Function body (for categorization)

### ⚡ CRITICAL: UI Event Handlers Extraction

**For forms with tabs, buttons, grids - MUST extract these event handlers FIRST:**

```bash
# Priority event handlers to extract:
- dxPageControl*Change    → Tab switching behavior
- TampilValidClick        → Tab 0 configuration
- TampilBatalClick        → Tab 1 configuration
- ToolButton*Click        → Button click handlers
- bt*Click                → Alternative button naming
- FormShow / FormCreate   → Initial state setup
- FormClose               → Cleanup logic
- GetData                 → Data loading queries
- *Exit / *Enter          → Field-level validation
- Grid*DblClick           → Row double-click actions
```

**Extract these as separate category: `ui_event`**

```json
{
  "name": "dxPageControl1Change",
  "type": "procedure",
  "line": 957,
  "category": "ui_event",
  "business_logic": "Tab switching: sets button states per tab (ToolButton3.Caption, ToolButton1.Enabled, ToolButton13.Enabled)",
  "laravel_target": {
    "file": "FE: PengajuanKreditPage.tsx",
    "method": "activeTab state + conditional rendering",
    "status": "pending"
  }
}
```

**Why this is CRITICAL:**
Real example - Pengajuan Kredit (2026-04-24):
- Without event handler analysis: Wrong button states, wrong captions
- `dxPageControl1Change` defines: ToolButton3.Caption="Hapus" vs "Batal"
- `TampilBatalClick` defines: ToolButton2.Enabled=false in Tab 1

### Step 2: Categorize Logic Types

Classify each function/procedure into one of these categories:

| Category | Pattern Indicators | Laravel Target |
|----------|-------------------|----------------|
| **UI Event** | `*Click`, `*Change`, `*Exit`, `*Enter`, `Form*`, `Tampil*`, `ToolButton*`, `dx*Change` | React handlers, conditional rendering |
| **Validation** | Names: `Cek*`, `Check*`, `Validate*`, `Is*`, `Can*` | FormRequest rules, Service validation |
| **Business Logic** | Calculations, workflow, domain rules | Service class methods |
| **Database Operations** | Uses: `TADOQuery`, `SQL.Add`, `Open`, `ExecSQL` | Eloquent/Query Builder |
| **Utility Functions** | Formatting, conversion, string manipulation | Helper/Utility classes |
| **Logging Functions** | Names: `*Log*`, `*History*`, `*Audit*` | Log facade/Activity Log |

**⚠️ UI Event category is CRITICAL for forms with tabs/buttons/grid:**
- Extract button enabled/disabled states
- Extract caption changes per tab
- Extract conditional visibility
- These define frontend behavior, not backend!

### Step 3: Generate Traceability Matrix

Create TWO output files:

#### 1. JSON Format (for automation)

```json
{
  "project": "KSP",
  "scan_date": "2026-04-10",
  "modules": [
    {
      "name": "MyProcedure",
      "delphi_file": "Application/Unit/MyProcedure.pas",
      "functions_count": 60,
      "functions": [
        {
          "name": "CekPeriode",
          "type": "function",
          "line": 650,
          "params": ["Nama:string", "tgl:Tdatetime"],
          "returns": "Boolean",
          "category": "validation",
          "business_logic": "Validasi periode berdasarkan user, bulan, tahun",
          "laravel_target": {
            "file": "App/Services/PeriodeService.php",
            "method": "validatePeriode",
            "status": "pending"
          }
        }
      ]
    }
  ],
  "summary": {
    "total_functions": 60,
    "validation": 15,
    "business_logic": 25,
    "database": 12,
    "utility": 8,
    "migrated": 0,
    "pending": 60
  }
}
```

#### 2. Markdown Format (for documentation)

```markdown
# MyProcedure Traceability Matrix

## Summary
- Total Functions: 60
- Migrated: 0 (0%)
- Pending: 60 (100%)

## Functions Checklist

### Validation Functions

| Function | Parameters | Returns | Laravel Target | Status |
|----------|------------|---------|----------------|--------|
| CekPeriode | Nama:string, tgl:Tdatetime | Boolean | PeriodeService@validatePeriode |  |
```

### Step 4: Validate Migration

Use the generated checklist to track migration progress:

1. Update `laravel_target` for each function
2. Change `status` from "pending" to "migrated" when done
3. Run validation to verify completeness

## Advanced Features

### Dependency Tracking

**NEW: Automatic dependency tracking ensures NO business logic is missed**

The skill now automatically:
1. Parses `uses` clause to find form dependencies
2. Parses `Application.CreateForm()` calls for dynamic forms
3. Searches the entire project for dependent forms
4. Builds a complete dependency tree

**Command-line options:**
```bash
# Dependency tracking is ENABLED by default
python scan_delphi.py pwt/Master/Barang/

# Disable dependency tracking (only scan specified folder)
python scan_delphi.py pwt/Master/Barang/ --no-deps

# Specify custom search root for dependencies
python scan_delphi.py pwt/Master/Barang/ --search-root=D:/ykka/Keu-app
```

**What gets tracked:**
| Pattern | Example | Resolved To |
|---------|---------|-------------|
| `uses FrMenuReport` | FrMenuReport | `pwt/ReportPreview/FrMenuReport.pas` |
| `Application.CreateForm(TFrmBarang, ...)` | TFrmBarang | `pwt/Master/Barang/FrmBarang.pas` |

**Excluded from tracking:**
- System units (SysUtils, Classes, Forms, Dialogs, etc.)
- Namespace units (units with dots in name)
- Already-scanned files in source directory

### Auto-Assign Laravel Targets

Automatically assign Laravel targets to functions without them:

```bash
# Dry run - show suggestions without modifying
python scripts/auto_assign_targets.py docs/traceability/frmmodule.json --dry-run

# Apply assignments
python scripts/auto_assign_targets.py docs/traceability/frmmodule.json
```

Pattern-based auto-assignment:
- `Cek*`, `Check*`, `Validate*` → ValidationService
- `btTambahClick`, `btEditClick`, `btHapusClick` → Controller CRUD
- `FormShow`, `FormClose`, `FormKeyDown` → not_needed (Livewire/Alpine.js)
- `*Enter`, `*Exit`, `*KeyDown` → not_needed (field events)
- `GetData`, `Tampil` → QueryService
- `Export*`, `Cetak*` → ExportService/ReportService

### Interactive Target Assignment

Manually assign targets with suggestions:

```bash
python scripts/interactive_assign.py docs/traceability/frmmodule.json
```

Commands:
- `1-5` - Choose from suggestions
- `service@method` - Assign custom target
- `n` / `-` - Next function
- `p` / `b` - Previous function
- `fn` - Mark all Enter/KeyDown/Change as not_needed
- `q` - Save and quit

### Batch Scan Multiple Forms

Scan all forms in a directory at once:

```bash
# Scan all forms in a directory
python scripts/batch_scan.py "pwt/Master/*"

# Scan with auto-assign
python scripts/batch_scan.py "pwt/Trasaksi/*" --auto-assign

# Show what will be scanned
python scripts/batch_scan.py "pwt/**/*.pas" --summary-only
```

Output: Individual traceability file for each form + overall summary.

### Migration Progress Dashboard

Track overall migration progress:

```bash
# Show progress report
python scripts/migration_progress.py

# Output as JSON
python scripts/migration_progress.py --json

# Custom paths
python scripts/migration_progress.py --memory=D:/Koperasi/memory --source=D:/Koperasi/KSP
```

**Output:**
```
OVERALL PROGRESS
----------------------------------------
  Total Modules:  113
  Migrated:       9 (8%)
  In Progress:    1
  Pending:        103

  [##----------------------------] 8%

BY CATEGORY
----------------------------------------
  Master        8/ 8 (100%)
  Setup         1/ 1 (100%)
  Other         1/ 1 (100%)

PENDING HIGH-PRIORITY MODULES
----------------------------------------
  1. Unit                 (Other     ) - 12 files
  2. Pemakai              (Master    ) - 9 files
  ...
```

The dashboard analyzes:
- Memory folder for `scan_*.md` and `generation_*.md`
- Delphi source directory for all .pas files
- Categorizes modules by type (Master, Transaksi, Report, Setup, System)
- Shows recent activity

## Delphi Pattern Reference

### Function/Procedure Declaration

```delphi
// Function with return type
function FunctionName(Param1: Type1; Param2: Type2): ReturnType;

// Procedure (no return)
procedure ProcedureName(Param1: Type1; var Param2: Type2);
```

### Database Query Pattern

```delphi
with DM.QuCari do
begin
  Close;
  SQL.Clear;
  SQL.Add('SELECT * FROM table WHERE field = :0');
  Prepared;
  Parameters[0].Value := someValue;
  Open;
end;
```

### Common Patterns to Extract

| Pattern | Example |
|---------|---------|
| TADOQuery | `DM.QuCari`, `DM.QuNomor` |
| SQL Building | `SQL.Add`, `SQL.Clear` |
| Parameters | `Parameters[0].Value`, `Prepared` |
| Execution | `Open`, `ExecSQL` |
| Global Variables | `iduser`, `PeriodThn`, `PeriodBln` |

## Usage Example

```
User: Scan file KSP\Project\FrmUtama.pas dan buat traceability matrix
```

The skill will:
1. Resolve absolute path to the .pas file
2. Read the .pas file
3. Extract all functions and procedures
4. Categorize each function
5. Generate `{filename}.json` (e.g., `FrmUtama.json`)
6. Generate `{filename}.csv` (e.g., `FrmUtama.csv`)
7. Generate `{filename}.md` (e.g., `FrmUtama.md`)

**⚠️ IMPORTANT: Path Resolution**
- Always convert relative paths to absolute paths before scanning
- Use `Path.resolve()` to get the full path
- Output goes to `D:\Koperasi\docs\traceability\` (NOT skill folder)
- Scan should be run from project root `D:\Koperasi\`

**Example:**
```bash
# From project root (D:\ykka\Keu-app):
python .claude/skills/delphi-traceability-analyzer/scripts/scan_delphi.py pwt/Master/Barang/FrmBarang.pas

# Output: D:\ykka\Keu-app\docs\traceability\frmbarang.json
```

## Output Files

After scanning `MyProcedure.pas`, you'll get:

| File | Purpose |
|------|---------|
| `myprocedure.json` | Machine-readable matrix (NAMA FILE OTOMATIS DARI INPUT!) |
| `myprocedure.csv` | CSV untuk spreadsheet |
| `myprocedure.md` | Human-readable documentation |

**PENTING**: Nama output file OTOMATIS mengikuti nama input file:
- `FrmUtama.pas` → `frmutama.json`
- `MyProcedure.pas` → `myprocedure.json`
- `FrmReportPreview.pas` → `frmreportpreview.json`

## Preventing Filename Errors

**JANGAN pernah manual rename output file!** Nama file harus match dengan source:
1. Cek `source_dir` di JSON - harus match dengan file yang di-scan
2. Jika mismatch, delete dan re-scan dengan benar

## Important Notes

- This skill does NOT generate Laravel code (that's Phase 2)
- Focus on tracking and documentation
- Use the JSON output for automation/scripting
- Use the Markdown output for manual review
- Update the Laravel target fields as you migrate

## CRITICAL: Migration Workflow

**Saat generate Laravel code, WAJIB update status di traceability.json:**

```json
{
  "name": "NamaFunction",
  "laravel_target": {
    "status": "migrated",  // ← UPDATE dari "pending"
    "verified_date": "2026-04-19"
  }
}
```

**Status flow:**
1. `pending` - Belum di-migrate (default)
2. `migrated` - Code sudah dibuat di Laravel
3. `verified` - Sudah ditest dan verified
4. `not_needed` - Tidak perlu di-migrate (UI only, etc.)

**Verifikasi:**
```bash
cd docs/traceability
python verify_migration.py ../../be-ksp/
```

---

## Skill Improvement (Feedback Loop)

### ⚡ IMMEDIATE: Update Patterns (CRITICAL!)

**SAAT KETEMU parsing error atau missing function → LANGSUNG tawarkan tambah ke patterns!**

> "Ini pola function Delphi yang belum ter-cover. Mau saya tambahkan ke skill?"

**Trigger untuk auto-suggest:**
- Function/procedure tidak ter-scan
- Event handler terlewat (dxPageControl*Change, dll)
- SQL query tidak ter-extract
- Form dependency tidak ter-detect

### Kapan Improve Skill Ini

**Update Parser:**
- Delphi pattern baru ditemukan → Tambah ke regex patterns
- Event handler baru → Tambah ke priority patterns list
- SQL query pattern baru → Update extraction logic

**Update Output Format:**
- Field baru dibutuhkan → Tambah ke JSON schema
- Format baru dibutuhkan → Tambah ke output options
- User request format lain → Tambah ke supported formats

**Update Documentation:**
- Category baru ditemukan → Tambah ke categorization
- Best practice baru → Tambah ke documentation
- Workflow improvement → Update section yang relevan

Jika status tidak di-update, function akan muncul sebagai "pending" di report verifikasi!
