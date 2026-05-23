---
name: sidebar-menu-hierarchy
description: Reference untuk debugging menu hierarchy sidebar
type: reference
---

# Sidebar Menu Hierarchy - Quick Reference

## Sumber Data

Menu sidebar driven dari **dua tabel** di SQL Server:

| Tabel | Fungsi |
|-------|--------|
| `DBFLMENUREPORT` | Hak akses user per menu |
| `DBMENUREPORT` | Data master menu (KODEMENU, Keterangan, L0, ACCESS) |

## Flow Debugging

### 1. Cek API Endpoint
```
GET http://localhost:8080/api/menus/sidebar?userId=SA
```
Response harus ada `success: true` dan `data.menus` dengan hierarchy.

### 2. Cek Controller
**File:** `be-fitur/app/Http/Controllers/MenuController.php`

Method `sidebar()` memanggil `ReportService.getSidebarMenu()`:
```php
public function sidebar(Request $request): JsonResponse
{
    $userId = $request->input('userId', '');
    $tree = $this->reportService->getSidebarMenu($userId);
    // ...
}
```

### 3. Cek Service (Logic Utama)
**File:** `be-fitur/app/Services/ReportService.php`

Method `getSidebarMenu()` dan `buildMenuNode()`:

**Logika Hierarchy:**
- Items dikelompokkan berdasarkan **L0 level**
- Items dengan L0 sama = sibling (sejajar), BUKAN nested
- Children diambil dari level L0+1 dengan **KODEMENU prefix match**

**Contoh:**
```
010 (Master Accounting) L0=1 → parent
├── 0101 (Daftar Perkiraan) L0=2 → sibling
├── 0102 (Daftar Neraca) L0=2 → sibling
└── 0103 (Daftar Laba Rugi) L0=2 → sibling
```

### 4. Cek Frontend Store
**File:** `fe-fitur/stores/menu.ts`

Method `fetchMenus()` kirim `userId` ke API:
```typescript
const userId = authStore.user?.userId || authStore.user?.username || ''
const response = await $fetch(`/menus/sidebar?userId=${userId}`)
```

### 5. Cek Frontend Component
**File:** `fe-fitur/components/Sidebar.vue`
- Menggunakan `menuStore` untuk fetch dan render menu
- Menggunakan `MenuItem.vue` untuk recursive rendering

## Query Delphi Reference

Di `pwt/MyProject/FrmUtama.pas` baris 131-141:
```delphi
SQL.Add('Select A.USERID, B.L0, B.KodeMenu L1, B.Keterangan Caption, B.ACCESS, A.HASACCESS');
SQL.add('from DBFLMENU A');
Sql.add('left outer join dbMenu B on B.Kodemenu=A.L1');
Sql.Add('where A.Userid=:0');
Sql.Add('Order by A.Userid, A.L1');
```

**Penting:** Join `DBFLMENU` (hak akses) dengan `DBMENU` (data menu).

## Checklist Jika Menu Tidak Muncul

1. ✅ Cek `userId` sudah dikirim di request
2. ✅ Cek `DBFLMENUREPORT` punya record untuk user tersebut
3. ✅ Cek `Access = 1` di `DBFLMENUREPORT`
4. ✅ Cek `L0 > 0` di `DBMENUREPORT`
5. ✅ Cek KODEMENU prefix match untuk parent-child

## Checklist Jika Hierarchy Salah

1. ✅ Items dengan L0 sama harus sejajar (sibling)
2. ✅ Children harus punya L0 = parent L0 + 1
3. ✅ Children harus punya KODEMENU yang mulai dengan parent KODEMENU
4. ✅ Cek `buildMenuNode()` di ReportService.php untuk verify logic

## Files to Check

| File | Line | Cek Jika |
|------|------|----------|
| `be-fitur/app/Services/ReportService.php` | ~60-130 | Logic build tree hierarchy |
| `be-fitur/app/Http/Controllers/MenuController.php` | ~30-45 | Endpoint sidebar |
| `fe-fitur/stores/menu.ts` | ~80-115 | Frontend fetch dengan userId |
| `fe-fitur/components/Sidebar.vue` | full | Render menu |
| `fe-fitur/components/MenuItem.vue` | full | Recursive render |
