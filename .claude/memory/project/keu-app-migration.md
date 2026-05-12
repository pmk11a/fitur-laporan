---
name: keu-app-migration
description: Delphi 6 to Laravel migration project - Keu-app ERP
type: project
---

## Project Context
- **Goal:** Migrate ERP legacy Delphi 6 → Laravel + Next.js
- **Database:** SQL Server (Single Source of Truth, Zero Schema Change)
- **Scope:** Master Data, Transaksi Operasional, Laporan

## Architecture
- **Backend:** Laravel 10+, PHP 8.2+, Eloquent with PDO SQL Server
- **Frontend:** Next.js 16 (React 19), Zustand + TanStack Query
- **Migration Tool:** Claude Code with custom skills

## Key Constraints
1. **No migrations** - `php artisan migrate` disabled in production
2. **Model binding** - Must use `$table = 'TABLENAME'` & `$primaryKey = 'PK_NAME'`
3. **Data only** - SELECT/INSERT/UPDATE/DELETE only, no Schema:: operations
4. **Source of Truth** - Conflicts → SQL Server wins over docs

## Source Code Location
- **Delphi:** `pwt/` folder (forms, units)
- **Backend:** `be-keu/` Laravel project
- **Frontend:** `fe-keu/` Next.js project

## Current Status (2026-05-06)
- Backend: ~250 Models defined for existing tables
- Frontend: In progress
- Migration: Planning stage, skills ready

## Priority Modules
1. Master Data (Barang, Customer, Supplier)
2. Transaksi (Penjualan, Pembelian)
3. Laporan (Keuangan, Stok)