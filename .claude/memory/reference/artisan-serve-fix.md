---
name: artisan-serve-fix
description: Fix untuk php artisan serve - crash + antivirus interference
type: reference
---

# Fix: php artisan serve di be-fitur

## Problem 1: Crash setelah 1 menit

Laravel 11.46.1 dengan missing `server.php`.

### Solution

```bash
cd D:/TestLaB/Fitur/be-fitur
php -S 127.0.0.1:8080 -t public
```

## Problem 2: CORS Error - No 'Access-Control-Allow-Origin' header

### Cause
Server started tanpa `-t public` flag, sehingga routing Laravel tidak jalan + CORS middleware error.

### Solution

**1. Pastikan server berjalan dengan document root:**
```bash
cd D:/TestLaB/Fitur/be-fitur
php -S 127.0.0.1:8080 -t public
```

**2. Jika tetap error, cek apakah ada proses php lain di port 8080:**
```bash
netstat -ano | findstr :8080
# Kill semua PID yang LISTENING
taskkill //F //PID <PID>
# Restart server
php -S 127.0.0.1:8080 -t public
```

**3. Verifikasi CORS headers:**
```bash
curl -s -X OPTIONS -H "Origin: http://localhost:3000" -H "Access-Control-Request-Method: POST" -i http://localhost:8080/api/auth/login | grep "Access-Control"
```

Harus ada output: `Access-Control-Allow-Origin: *`

### Verified Commands (Mei 2026)

```bash
cd D:/TestLaB/Fitur/be-fitur
"C:/xampp/php/php.exe" -S 127.0.0.1:8080 -t public
```

### Key Points
- **Selalu gunakan `-t public`** agar routing Laravel jalan
- **CORS middleware** ada di `app/Http/Middleware/Cors.php`
- **Middleware di-register** di `bootstrap/app.php`