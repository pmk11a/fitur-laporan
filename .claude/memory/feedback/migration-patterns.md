---
name: migration-patterns
description: Critical patterns and gotchas for Delphi to Laravel migration
type: feedback
---

## COLUMN NAMES UPPERCASE
**Why:** SQL Server legacy schema uses uppercase column names
**How to apply:** All Eloquent queries MUST use UPPERCASE column names

## USERID vs userid
**Why:** Legacy Users table uses `USERID` (all caps), not `userid`
**How to apply:** Use `$request->user()->USERID`, NOT `$request->user()->userid`

## noBukti Slash Parameter
**Why:** Format like `2025/01/0001` contains forward slashes
**How to apply:**
- Route: `->where('nobukti', '.*')` to match slashes
- Frontend: `encodeURIComponent(nobukti)` when passing in URL

## Auth Property Name
**Why:** Legacy Users table has `USERID` column
**How to apply:** In JWT auth, access via `$request->user()->USERID`

## DFM ComboBox Items
**Why:** ComboBox dropdown items are defined in .dfm files, not .pas
**How to apply:** Check .dfm for `Items.Strings` to extract dropdown options

## API Response Pattern
**Why:** Frontend expects consistent format
**How to apply:** Always return `{ success: true, data: [...] }` structure

## Direct Import for DynamicPage
**Why:** Code splitting doesn't work well with dynamic imports in React
**How to apply:** Use `import { PageName } from 'path'` not `lazy(() => import('path'))`

## React Hook: res.data
**Why:** TanStack Query wraps response in `data` property
**How to apply:** Access data as `res.data.data` → use `res.data` directly

## Event Handlers FIRST
**Why:** Form behavior defined by page control change handlers
**How to apply:** Read `dxPageControl1Change` first before other event handlers

## API Resource Parameter
**Why:** Laravel resource routes use singular naming
**How to apply:** Route: `api/resource/{id}` not `api/resources/{id}`

## Folder Structure Matches ROUTE
**Why:** Organized by menu access from `dbflmenu` table
**How to apply:** Match folder structure to route paths in database

## Sidebar Hierarchy: L0 Level NOT Prefix
**Why:** Items with same L0 are siblings, not nested. Children come from L0+1 with KODEMENU prefix match.
**How to apply:**
- L0=1 → parent (Master Accounting)
- L0=2 → sibling (Daftar Perkiraan, Daftar Neraca, Daftar Laba Rugi - SEJAJAR)
- L0=3 → children dari L0=2 yang KODEMENU prefix match
**Common hallucination:** Using KODEMENU prefix only without L0 level grouping

## Filter -1 (Delphi): No UI Filter
**Why:** Delphi `ShowReportPreview(' Daftar Perkiraan ',-1)` with -1 means no filter UI
**How to apply:**
- ACCESS code 101 (Daftar Perkiraan) → render Generate button, hide filter section
- ACCESS code 20101 (Kas Harian) → 0 = filter with Divisi, Perkiraan, Tanggal
**Reference:** See sidebar-menu-hierarchy.md for full filter mapping