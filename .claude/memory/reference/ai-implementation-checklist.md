---
name: ai-implementation-checklist
description: Checklist sebelum implement - avoid hallucination & overlap
type: reference
---

# AI Implementation Checklist

## PRINSIP UTAMA

> **Memory = authoritative source untuk konteks project**
> Pattern/template harus VERIFIED against memory sebelum implement

## SEBELUM IMPLEMENTASI

### Step 1: Check Memory Index
```
1. Buka .claude/memory/MEMORY.md
2. Identifikasi relevant memory files untuk task ini
3. Baca memory files tersebut
```

### Step 2: Check Pattern (Jika Ada)
```
1. Cek apakah ada pattern/template untuk task ini
2. Pattern boleh dipakai, tapi VERIFY dengan memory
3. Kalau memory vs pattern konflik →优先 memory, tanya user
```

### Step 3: Implement
```
1. Pakai logic/config dari memory
2. Pakai pattern/template sebagai starter code
3. Verify output sesuai memory rules
```

### Step 4: Validate Output
```
1. Output sesuai memory rules?
2. Ada conflict dengan memory sebelumnya?
3. Kalau conflict → tanya user, jangan assume
```

## COMMON TRAPS TO AVOID

### Hallucination
- AI lupa check memory → implement pakai "best guess"
- Solution: Selalu read memory SEBELUM mulai

### Overlap
- AI implement sama task berbeda → tidak konsisten
- Solution: Check MEMORY.md selalu, terutama untuk:
  - Sidebar hierarchy (L0-based, bukan prefix)
  - Filter logic (Delphi mapping)
  - Auth patterns (UID2 for password)

### Conflicting Info
- Memory bilang A, pattern bilang B
- Solution:
  1. Prioritas: Memory > Pattern
  2. Tanya user untuk clarification
  3. Jangan pick salah satu sendiri

## QUICK REFERENCE: SIDEBAR HIERARCHY

```
✅ BENAR: L0 level menentukan hierarchy
   L0=1 → parent
   L0=2 → sibling (sejajar)
   L0=3 → children dari L0=2 yang prefix match

❌ SALAH: KODEMENU prefix menentukan semua
   (Ini hallucination yang sering terjadi)

📁 Files:
   - Backend: be-fitur/app/Services/ReportService.php (buildMenuNode)
   - Frontend: fe-fitur/components/Sidebar.vue
   - Store: fe-fitur/stores/menu.ts
```

## QUICK REFERENCE: FILTER MAPPING

```
KodeReport 101 (Daftar Perkiraan):
   - Delphi: ShowReportPreview(' Daftar Perkiraan ',-1)
   - Arti: -1 = tidak ada filter UI, generate langsung
   - Frontend: Skip filter section, langsung Generate button

KodeReport 20101 (Kas Harian):
   - Delphi: ShowReportPreview(' Kas Harian',0)
   - Arti: 0 = filter dengan Divisi, Perkiraan, Tanggal
   - Frontend: Render Divisi + Perkiraan + Tanggal inputs
```

## FILES TO CHECK (Always)

| Task | Check Memory | Check Pattern |
|------|--------------|---------------|
| Sidebar menu | sidebar-menu-hierarchy.md | - |
| Report filter | Fluffy Bee docs | - |
| Auth/login | login-auth-patterns.md | - |
| Migration | migration-patterns.md | delphi-laravel-impl/ |
| General | MEMORY.md index | - |

## VERIFICATION SCRIPT

```bash
# Check if memory was applied correctly
cat .claude/memory/MEMORY.md
cat .claude/memory/reference/sidebar-menu-hierarchy.md
```