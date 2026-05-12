---
name: no-hardcode-rule
description: Aturan anti-hardcoding untuk migrasi Delphi→Laravel
type: feedback
---

## Rule: Database-First, No Hardcoded Values

**Why:** AI sering hardcode nilai yang sudah ada di database (status codes, category names, dll), menyebabkan inconsistency antara app dan database.

**How to apply:** Setiap kali AI akan menulis string literal (terutama di conditionals, dropdown options, mapping), WAJIB cek apakah nilai tersebut sudah ada di database. Jika ya → gunakan Repository/Model lookup.

**Contoh yang salah:**
```php
if ($row['status'] === 'ACT')  // ❌ hardcoded
$options = ['Active', 'Inactive']  // ❌ hardcoded
```

**Contoh yang benar:**
```php
if ($row['status'] === Status::ACTIVE_CODE)  // ✅ dari enum/config
$options = $statusRepository->pluck('name', 'code')->toArray()  // ✅ dari DB
```
