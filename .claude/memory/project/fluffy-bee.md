---
name: fluffy-bee-project
description: Fluffy Bee Dynamic Report Engine - frontend Nuxt.js project documentation
type: project
---

# Fluffy Bee Project

**Description:** Dynamic Form/Report Engine untuk ERP (Flutter Bee branding)

**Created:** 2026-05-08

## Architecture

| Layer | Technology | Port |
|-------|------------|------|
| Frontend | Nuxt.js 3 + Tailwind CSS | 3000 |
| Backend API | Laravel (Keu-app) | 8000 |
| Backend (new) | Laravel (be-fitur) | 8080 |
| Database | Microsoft SQL Server 2008 | - |

## Frontend Setup

```bash
cd fe-fitur && npm run dev  # Runs on http://localhost:3000
```

## API Configuration

### Current Mode: Keu-app (port 8000)

| Setting | Value |
|---------|-------|
| API Base | `http://localhost:8000/api` |
| Login Endpoint | `/api/auth/login` |
| Login Format | `{ userId, password }` |

### Alternative Mode: be-fitur (port 8080)

| Setting | Value |
|---------|-------|
| API Base | `http://localhost:8080/api` |
| Login Format | `{ username, password }` |

## Login Credentials (DBFLPASS table)

| Field | Value |
|-------|-------|
| UserID | `adminkarir` |
| Password | `masza1` |
| UID2 (DBFLPASS) | `bWFzemEx` (base64 encoded) |

**Note:** Password di UID2 field disimpan sebagai base64 encoded string dari Delphi.

## Response Format

### Keu-app API
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": {
      "USERID": "adminkarir",
      "FullName": "GUSA",
      "TINGKAT": 1,
      "STATUS": 1,
      "kodeBag": "008"
    },
    "access_token": "token123"
  }
}
```

## Role Determination

```typescript
isAdmin = (TINGKAT >= 99) || (STATUS >= 255)
```

## Key Files

| File | Description |
|------|-------------|
| `fe-fitur/stores/auth.ts` | Auth state management (login, logout, user) |
| `fe-fitur/stores/menu.ts` | Menu sidebar state management |
| `fe-fitur/pages/login.vue` | Login page |
| `fe-fitur/pages/dashboard.vue` | Main dashboard |
| `fe-fitur/components/Sidebar.vue` | Dynamic sidebar with menu |
| `fe-fitur/components/TopBar.vue` | Top navigation bar |
| `fe-fitur/composables/useApiConfig.ts` | API configuration helpers |
| `fe-fitur/docs/FRONTEND_CONFIG.md` | Detailed configuration docs |

## API Endpoints

### Authentication
- `POST /api/auth/login` - Login (userId, password)
- `POST /api/auth/logout` - Logout
- `GET /api/auth/me` - Get current user

### Menu
- `GET /api/menus/sidebar` - Get sidebar menus (Keu-app)
- `GET /api/menu` - Get menus (be-fitur alternative)

## Development Notes

1. Nuxt dev server runs on port 3000
2. Laravel backend runs on port 8000 (Keu-app) or 8080 (be-fitur)
3. CORS configured to allow Nuxt frontend
4. Auth token stored in localStorage
5. User data mapped to unified format in `auth.ts` store

## Working Features (Mei 2026)

### Login
- be-fitur API (port 8080) working with CORS
- Password: base64_decode(UID2)
- Start server: `php -S 127.0.0.1:8080 -t public` (use `-t public` flag)

### Sidebar Menu
- `/api/menus/sidebar?userId=SA` returns hierarchical menu
- Menu structure: L0-based hierarchy (same L0 = siblings)
- Response: `{ success: true, data: { menus: [...], permissions: [] } }`

### Report Page
- Filter based on ACCESS code (Delphi mapping)
- `-1` code (101, 1110, etc.) = no filter UI

### SQL Server Encoding Fix (Mei 2026)
**File:** `be-fitur/app/Services/ReportService.php` line 359-372

**Problem:** "Malformed UTF-8 characters" error - SQL Server uses CP1252 encoding, not UTF-8.

**Solution:**
```php
$value = mb_convert_encoding($value, 'UTF-8', 'CP1252');
$value = preg_replace('/[^a-zA-Z0-9 .,\-_\/()]/', '', $value);
```

### Report Display Fix (Mei 2026)
**File:** `fe-fitur/pages/reports/[kode].vue`

**Problem:** All columns from SQL query shown, not just columns defined in .fr3/dbkolomlaporan.

**Solution:**
- Headers use `label_tampil` from `dbkolomlaporan`
- Data keys use `nama_kolom` from `dbkolomlaporan`
- Filter by `is_visible` field

```typescript
const reportHeaders = computed(() => {
  // Use columns config from dbkolomlaporan
  if (reportStore.currentReport?.columns) {
    const cols = reportStore.currentReport.columns[mainDataset] || []
    return cols.filter(c => c.is_visible !== false).map(c => c.nama_kolom)
  }
})
```

This converts from CP1252 and keeps only clean ASCII text.