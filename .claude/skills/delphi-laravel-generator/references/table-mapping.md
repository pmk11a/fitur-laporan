# Table Mapping Guide

Multi-layer validation for mapping Delphi forms to database tables.

## Table of Contents

- [Overview](#overview)
- [⚠️ CRITICAL: Database Schema First](#-critical-database-schema-first)
- [Multi-Layer Validation Workflow](#multi-layer-validation-workflow)
- [Config File: table_mappings.json](#config-file-table_mappingsjson)
  - [Structure](#structure)
  - [Fields](#fields)
- [Conflict Detection](#conflict-detection)
- [Resolution Strategy](#resolution-strategy)
- [Example Mappings](#example-mappings)
  - [FrmPengajuan](#frmpengajuan)
  - [FrmRealisasi (Shares Table)](#frmrealisasi-shares-table)
  - [FrLogin (Auth Form)](#frlogin-auth-form)
  - [FrmMain (Menu Only)](#frmmain-menu-only)
- [Adding New Mappings](#adding-new-mappings)

---

## Overview

Table mapping determines which database table a Delphi form uses. This is critical for generating correct Models.

## ⚠️ CRITICAL: Database Schema First

**BEFORE any table mapping or code generation:**

1. **Run schema reader** to get ACTUAL database structure:
   ```bash
   cd .claude/skills/delphi-laravel-generator/scripts
   read_schema.bat
   ```

2. **Output**: `database_schema.json` with REAL table structures:
   - Table names (AS THEY EXIST in database)
   - Column names and types
   - Primary keys
   - Foreign keys

3. **⚠️ RULE**: Database schema is SOURCE OF TRUTH. Config is secondary.

## Multi-Layer Validation Workflow

```
⚠️ PREREQUISITE: read_schema.bat MUST be run FIRST
       │
       ▼
┌─────────────────────────────────────────┐
│  Layer 0: DATABASE SCHEMA (SOURCE)      │
│  - Read from database_schema.json       │
│  - Contains ACTUAL table structures     │
│  - This is the TRUSTED source           │
└─────────────────────────────────────────┘
       │
       ▼
INPUT: FrmPengajuan.pas + FrmPengajuan.dfm + database_schema.json
       │
       ▼
┌─────────────────────────────────────────┐
│  Layer 1: DFM Scan                      │
│  - Parse TADOQuery.SQL.Strings          │
│  - Parse TADOTable.TableName            │
│  Output: [dbpengajuan, dbjaminan]       │
└─────────────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  Layer 2: .pas SQL Scan                 │
│  - Find "FROM xxx", "INSERT INTO xxx"   │
│  - Extract table names                  │
│  Output: [dbpengajuan, dbcustomer]      │
└─────────────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  Layer 3: Cross-Reference               │
│  Primary: dbpengajuan (in both layers)  │
│  Related: dbjaminan, dbcustomer         │
└─────────────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  Layer 4: DATABASE VERIFICATION ← NEW  │
│  - Verify against database_schema.json  │
│  - Get ACTUAL columns, types, PK, FK    │
│  - If config ≠ database: ERROR         │
└─────────────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  Layer 5: Config Override (OPTIONAL)    │
│  - Only AFTER database verification     │
│  - Manual overrides for edge cases      │
│  Result: FINAL MAPPING ✓                │
└─────────────────────────────────────────┘
```

## Config File: table_mappings.json

### Structure

```json
{
  "mappings": {
    "FrmPengajuan": {
      "primary": "dbpengajuan",
      "related": ["dbjaminan", "dbcustomer", "dbproduk"],
      "model": "Pengajuan",
      "primary_key": "NoBukti",
      "validated": true,
      "notes": "Auto-confirmed via DFM+SQL scan"
    }
  }
}
```

### Fields

| Field | Description | Example |
|-------|-------------|---------|
| primary | Main table this form uses | dbpengajuan |
| related | Other tables accessed (joins) | [dbjaminan, dbcustomer] |
| model | Laravel model class name | Pengajuan |
| primary_key | Primary key column | NoBukti |
| validated | Has mapping been verified? | true |
| notes | Additional context | Auto-confirmed |
| auth | Is this an auth form? | true (for FrLogin) |

## Conflict Detection

When layers disagree:

```
WARNING: FrmPengajuan mapping conflict
  DFM scan:       [dbpengajuan, dbjaminan]
  SQL scan:       [dbpengajuan, dbcustomer]
  Config manual:  [dbpengajuan, dbjaminan, dbproduk]

  Action: Using config manual as source
  Flag: dbcustomer only in SQL scan - review needed
```

## Resolution Strategy

1. **Database schema is SOURCE OF TRUTH** - All mappings must verify against database_schema.json
2. **Primary table**: Table appearing in ≥2 layers AND exists in database
3. **Related tables**: Tables appearing in only 1 layer but verified in database
4. **Config override**: Manual config can ONLY override AFTER database verification
5. **Validation**: If config ≠ database, flag as ERROR and require user resolution

## Example Mappings

### FrmPengajuan

```json
{
  "FrmPengajuan": {
    "primary": "dbpengajuan",
    "related": ["dbjaminan", "dbcustomer", "dbproduk"],
    "model": "Pengajuan",
    "primary_key": "NoBukti",
    "validated": true
  }
}
```

**Detection:**
- DFM: `TADOQuery.SQL.Strings = "SELECT * FROM dbpengajuan"`
- SQL: `SQL.Add('INSERT INTO dbpengajuan')`
- Config: Confirmed

### FrmRealisasi (Shares Table)

```json
{
  "FrmRealisasi": {
    "primary": "dbpengajuan",
    "related": ["dbangsuran", "dbcustomer"],
    "model": "Pengajuan",
    "primary_key": "NoBukti",
    "validated": true,
    "notes": "Share table dengan FrmPengajuan - update status"
  }
}
```

**Detection:**
- Same table as FrmPengajuan but different operation (update vs insert)

### FrLogin (Auth Form)

```json
{
  "FrLogin": {
    "primary": "dbUser",
    "related": [],
    "model": "User",
    "primary_key": "UserID",
    "validated": true,
    "notes": "Login form - uses dbUser for authentication",
    "auth": true
  }
}
```

**Detection:**
- No direct table operations (authentication only)
- References MyCariUserName function

### FrmMain (Menu Only)

```json
{
  "FrmMain": {
    "primary": null,
    "related": [],
    "model": null,
    "primary_key": null,
    "validated": true,
    "notes": "Main menu form - UI only, no direct table mapping"
  }
}
```

**Detection:**
- UI-only form (no database operations)

## Adding New Mappings

When scanning a new form:

**⚠️ MANDATORY FIRST STEP:**
1. **Run schema reader** - `read_schema.bat` to get database_schema.json
2. **Verify table exists** - Check database_schema.json for the table
3. **Get actual columns** - Use column names from database_schema.json

**Then proceed with mapping:**
4. **Run DFM scan** - extract TADOQuery.SQL.Strings
5. **Run SQL scan** - find FROM/INSERT INTO/UPDATE
6. **Cross-reference** - identify primary vs related
7. **VERIFY against database** - Ensure tables/columns exist in database_schema.json
8. **Update config** - add to table_mappings.json (ONLY after verification)
9. **Validate** - confirm with user if conflicts

**⚠️ ERROR CONDITIONS:**
- Config specifies table that doesn't exist in database → ERROR
- Config specifies primary_key that doesn't exist → ERROR
- Config specifies column that doesn't exist in table → WARNING

## See Also

- `config/table_mappings.json` - Configuration file
- `db_schema_reader.py` - Database schema scanner
