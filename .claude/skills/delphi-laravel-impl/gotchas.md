# Gotchas — Keu-app Migration

> Catatan: File ini originally dari KSP project. KSP-specific gotchas disimpan sebagai referensi di folder `references/`.

**Error pattern yang sudah terbukti >15 menit debug.**

---

## 1. Column Names UPPERCASE

```php
// ❌ SALAH
->where('userid', $userId)

// ✅ BENAR
->where('UserID', $userId)
```

**Common:** `UserID`, `CAPTION`, `L0`, `L1`, `ACCESS`

---

## 2. Auth: `$request->user()->USERID`

```php
// ❌ SALAH
$userId = $request->user()->userid;

// ✅ BENAR
$userId = $request->user()->USERID;
```

---

## 3. Auth State Must Be Persisted

**Symptom:** Login → klik menu → redirect login lagi (loop)

**Fix** di `authStore.ts`:
```typescript
partialize: (state) => ({
  isAuthenticated: state.isAuthenticated,  // ← WAJIB ada
})
```

---

## 4. DynamicPage: Direct Import Only

```typescript
// ❌ SALAH
const SetPeriodePage = lazy(() => import('@/features/file/period'));

// ✅ BENAR
import { SetPeriodePage } from '@/features/file/period';
```

---

## 5. Hook: `res.data` (bukan `res.data.data`)

```typescript
// ❌ SALAH
queryFn: () => userService.getData().then(res => res.data.data)

// ✅ BENAR
queryFn: async () => {
  const res = await userService.getData();
  return res?.data ?? [];
}
```

---

## 6. ComboBox Items di .dfm (bukan .pas)

```
FrmPerkiraan.pas    ← Logic
FrmPerkiraan.dfm    ← ComboBox Items.Strings
```

**WAJIB baca DUA file**, bukan cuma .pas.

---

## 7. apiResource Parameter Singular

```php
// Route::apiResource('v1/accounts', ...)
// Parameter: {account} (SINGULAR!)

// ❌ SALAH
$code = $this->route('code');

// ✅ BENAR
$code = $this->route('account');
```

Check: `php artisan route:list --path=v1/accounts`

---

## 8. Folder Must Match dbFlMenu.ROUTE

```
ROUTE: /keu/barang
Folder: features/keu/barang/
File:   BarangPage.tsx
```

---

## 9. Read Event Handlers FIRST

**Sebelum implement UI:**
```bash
grep -n "dxPageControl1Change\|TampilValidClick\|ToolButton" Frm.pas
```

**Cek juga @variable di SQL:**
```bash
grep -n "@Pembulatan\|declare @" Frm.pas
```

---

## 10. Traceability JSON: Absolute Path

```bash
# ❌ SALAH - relative path dari skill folder
python scripts/scan.py ../../pwt/...

# ✅ BENAR - dari project root
cd D:\ykka\Keu-app
python .claude/skills/.../scan.py pwt/...
```

Verifikasi: `cat docs/traceability/{module}.json | grep total_functions` harus > 0

---

## 11. Backend Validation Must Match Frontend

**Setiap tambah field baru, WAJIB:**

| Step | File | Check |
|------|------|-------|
| 1 | Frontend Form | Input field |
| 2 | TypeScript Interface | Type |
| 3 | Frontend Service | Field di request |
| 4 | **Backend Controller** | **Validation rule** ← SERING MISS |
| 5 | Model | `$fillable` |

---

## 12. auth:sanctum → Memory Exhausted

**Symptom:** Tinker works, HTTP call returns 500 + memory exhausted

**Fix:** Custom middleware `AuthenticateToken` (bukan `auth:sanctum`)

```php
// ❌ Gagal
Route::middleware('auth:sanctum')

// ✅ Workaround
Route::middleware('auth.token')
```

---

## Pre-Migration Checklist

- [ ] Traceability JSON valid (`total_functions > 0`)
- [ ] Mapping table dari event handlers
- [ ] .dfm dibaca untuk ComboBox items
- [ ] Backend: Column UPPERCASE
- [ ] Backend: Validation match frontend
- [ ] Frontend: `isAuthenticated` persisted
- [ ] Folder match ROUTE
