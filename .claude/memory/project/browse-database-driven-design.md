---
name: browse-database-driven-design
description: Design document untuk browse autocomplete database-driven (DB-driven config, multi-select, query management)
type: project
---

## Status: Draft (Belum Implementasi)

---

## Context

Report system sudah DB-driven (config dari `dbmasterlaporan`, `dbquerylaporan`, `dbkolomlaporan`). Browse autocomplete masih hardcoded di frontend (`useBrowseSearch.ts`, `useBrowseConfig.ts`). Goal: buat browse juga DB-driven seperti reports.

---

## Arsitektur Desain

### 1. Tabel: `dbbrowseconfig`

```sql
CREATE TABLE dbbrowseconfig (
  kode_browse   VARCHAR(50) PRIMARY KEY,
  mode          ENUM('single','tags','checkbox') DEFAULT 'single',
  key_field     VARCHAR(100) NOT NULL,
  label_field   VARCHAR(100) NOT NULL,
  placeholder   VARCHAR(255),
  additional_fields JSON,        -- ["Sat1", "Sat2", "HargaJual"]
  config        JSON,           -- {maxSelect, groupBy, checkAll, allowFreeText}
  is_active     BOOLEAN DEFAULT 1,
  created_at    TIMESTAMP,
  updated_at    TIMESTAMP
);
```

**Mode**:
- `single` — 1 kode, validate on blur
- `tags` — multiple kode, chip display
- `checkbox` — multiple kode, dialog dengan checklist

**Config JSON** (optional):
```json
{
  "maxSelect": 10,
  "groupBy": "Kategori",
  "checkAll": true,
  "allowFreeText": true,
  "searchMode": "infix",  // "prefix" | "infix" | "both"
  "freeTextLabel": "atau ketik kode baru..."
}
```

### 2. Tabel: `dbbrowsequery`

```sql
CREATE TABLE dbbrowsequery (
  kode_browse   VARCHAR(50) PRIMARY KEY,
  query_select  TEXT NOT NULL,  -- SELECT ... WHERE ... LIKE @q ...
  query_count   TEXT,           -- untuk pagination (optional)
  order_by      VARCHAR(255),
  -- Company filter: auto dari auth/koneksi user di backend
  is_active     BOOLEAN DEFAULT 1,
  updated_at    TIMESTAMP
);
```

**Contoh query_select:**
```sql
SELECT KodeBrg, NamaBrg, Sat1, Sat2, HargaJual
FROM dbbarang
WHERE (KodeBrg LIKE @q OR NamaBrg LIKE @q)
  AND is_active = 1
  AND KodeComp = @kodecomp
ORDER BY KodeBrg
```

**Notes:**
- `@q` = search parameter (user input)
- `@kodecomp` = auto dari auth session di backend, tidak perlu di-pass dari FE
- `@kodeuser` = untuk ACCESS control

### 3. API Endpoint

```
GET  /api/browse/{kode_browse}          → search (query param: q, limit)
GET  /api/browse/{kode_browse}/config  → return dbbrowseconfig record
GET  /api/browse/types                 → return all browse types (for FE dynamic dropdown)
POST /api/browse/{kode_browse}/validate → validate single code
POST /api/browse/{kode_browse}/validate-batch → validate multiple codes
```

---

## Design Options yang Dibahas

### Option A — dbbrowsemaster + dbbrowsefield (Two-table, complex)
- Master = metadata browse
- Field = konfigurasi kolom per browse
- Fleksibel untuk browse dengan banyak field berbeda
- Effort: High

### Option B — dbbrowseconfig + dbbrowsequery (Recommended)
- Config = display config (key, label, mode)
- Query = SQL per browse type
- Clean, maintainable, tidak perlu JSON encode/decode
- Effort: Medium

### Option C — Keep hardcoded, add /browse/types endpoint
- Frontend still has hardcoded fallback
- Minimal effort, tidak solve root problem

**Dipilih: Option B** — YAGNI, mulai dari yang sederhana.

---

## Frontend Changes Needed

### Before (hardcoded)
```typescript
// useBrowseSearch.ts
function getKeyField(): string {
  const keyFieldMap: Record<string, string> = {
    '10141': 'KodeCustSupp',
    '911': 'KodeBrg',
    'perkiraan': 'Perkiraan',
    // ... hardcoded
  }
  return keyFieldMap[browseType] || 'Perkiraan'
}
```

### After (DB-driven)
```typescript
// useBrowseConfig.ts — getConfig already calls API
// useBrowseSearch.ts — use config.keyField, config.labelField from API
// Remove buildDefaultConfig() fallback setelah API reliable
```

### Migration Path
```
Phase 1: Backend populate dbbrowseconfig + dbbrowsequery
         FE: call API, fallback to hardcoded if fail

Phase 2: FE remove hardcoded fallback
         (graceful degradation: fallback only when API truly unreachable)

Phase 3: Add advanced features (grouping, cascade, etc.)
```

---

## Open Questions / Decisions Needed

| Item | Question | Options |
|------|----------|---------|
| **SQL Injection** | Query construction safe? | Use parameterized queries only |
| **Null handling** | Field NULL di result | COALESCE di query, atau JS null check |
| **Company filter** | KodeComp dari mana? | From auth session, auto-inject di backend |
| **Soft delete** | Include is_active=0? | Exclude in query; periodic orphan scan |
| **Search algorithm** | Prefix vs infix? | Configurable per browse (searchMode) |
| **Orphan codes** | Saved filter reference deleted item? | Soft delete; periodic validate job |
| **ACCESS control** | Browse query ikut ACCESS? | Auto-filter by user ACCESS in query |
| **Pagination** | Checkbox getAll(500) — 10k items? | Add server-side pagination |
| **Caching** | Browse result cached? | Redis / in-memory / none |
| **Mobile UX** | Checkbox dialog di mobile viewport | Simplified UI atau pagination |

---

## Additional Considerations (Not Yet Prioritized)

1. **Multi-source browse** — 1 filter search dari 2+ tables sekaligus (e.g., "Akun" = Kas + Bank + Piutang)
2. **Dependent auto-fill** — pilih KodeBrg → auto fill Sat1, HargaJual
3. **Cascade filters** — filter B depends on filter A
4. **Versioning / Audit trail** — siapa ubah dbbrowseconfig kapan
5. **Testing tooling** — seed/test browse config tanpa SQL langsung
6. **Documentation** — ERD, usage guide, migration checklist

---

## Related Files

- `fe-fitur/components/BrowseAutocomplete.vue` — UI component
- `fe-fitur/composables/useBrowseSearch.ts` — search + validate logic (remove hardcoded)
- `fe-fitur/composables/useBrowseConfig.ts` — config fetching (already calls API, need populate DB)
- `fe-fitur/stores/reports.ts` — report store (reference for DB-driven pattern)
- `be-fitur/app/Services/BrowseService.php` — backend browse service (to be extended)
- `be-fitur/app/Http/Controllers/BrowseController.php` — endpoint controller

---

## Status Checklist

- [x] Design discussion (done: 2026-05-16)
- [ ] Backend: Create dbbrowseconfig table
- [ ] Backend: Create dbbrowsequery table
- [ ] Backend: Seed initial data (migrate from hardcoded keyFieldMap)
- [ ] Backend: Update /browse/{type}/config to read from DB
- [ ] Backend: Update /browse/{type} search to use dbbrowsequery.query_select
- [ ] Backend: Add parameterized query (prevent SQL injection)
- [ ] Backend: Auto-inject KodeComp/KodeUser from auth session
- [ ] FE: Remove hardcoded getKeyFieldMap/getLabelFieldMap
- [ ] FE: Remove buildDefaultConfig() fallback
- [ ] FE: Graceful degradation during migration
- [ ] Testing: Multi-select (tags + checkbox modes)
- [ ] Testing: Orphan code handling
- [ ] Testing: ACCESS control