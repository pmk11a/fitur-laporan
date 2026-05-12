---
name: login-auth-patterns
description: Pattern login be-keu dengan base64 password comparison
type: project
---

# Login Authentication Patterns - be-keu

## Login Flow (be-keu vs be-dapenka-master)

### be-keu (ERP Keuangan)
- Password di-database: `base64_encode(password) === UID2`
- Model: `FLPASS extends Authenticatable implements JWTSubject`
- Pattern: Repository Pattern
- File: `app/RepositoryPattern/Auth/AuthRepository.php`

### be-dapenka-master (Reference)
- Password: `Auth::attempt(['userid' => ..., 'password' => ...])`
- Model: `DBFLPASS extends Authenticatable implements JWTSubject`
- Pattern: Repository Pattern
- File: `app/RepositoryPattern/Auth/AuthRepository.php`

## Struktur Repository Pattern

```
app/RepositoryPattern/Auth/
├── AuthInterface.php
└── AuthRepository.php
```

## Interface Methods
- `login(array $credentials)` - return token atau null
- `me()` - return user object
- `logout()` - return bool
- `refresh()` - return new token

## Model FLPASS Requirements
```php
extends Authenticatable
implements JWTSubject
- getAuthPassword() -> return $this->UID2
- getAuthIdentifierName() -> return 'USERID'
- getJWTIdentifier() -> return $this->getKey()
- getJWTCustomClaims() -> return ['USERID', 'FullName', 'TINGKAT', ...]
```

## Config Auth
```php
'guards' => [
    'api' => ['driver' => 'jwt', 'provider' => 'dbflpass']
],
'providers' => [
    'dbflpass' => ['driver' => 'eloquent', 'model' => App\Models\FLPASS::class]
]
```

## Provider Binding
File: `app/Providers/AppServiceProvider.php`
```php
$this->app->bind(AuthInterface::class, AuthRepository::class);
```

## Common Issues Fixed
1. **LOGFILE model** - tambah `public $timestamps = false` (tabel tidak punya created_at/updated_at)
2. **AuthorizationService orderBy** - `.orderBy('B.L0', 'asc')` (direction harus lowercase)
3. **CORS middleware** - handle OPTIONS preflight request
4. **User SA** - UID2 kosong, perlu special handling

## Login Credentials
- User ID: SA
- Password: masza1
- UID2 di database: `bWFzemEx` (base64 encoded)

## Login Auth - be-fitur (Fitur project)

### Table: DBFLPASS
| Field | Fungsi |
|-------|--------|
| USERID | Primary key |
| UID | Username alternatif |
| UID2 | Password (base64 encoded) |
| FullName | Nama lengkap user |
| TINGKAT | Level user (1-99) |
| STATUS | Access bitmask |
| kodeBag | Kode bagian |
| KodeKasir | Kode kasir |
| Kodegdg | Kode gudang |

### Password Verification (AuthController.php)
```php
// UID2 is base64 encoded plain password
$storedPassword = $user->UID2 ?? '';

// Try direct comparison
if ($input === $storedPassword) return true;

// Try base64 decode and compare
$decoded = base64_decode($storedPassword);
if ($decoded !== false && $decoded === $input) return true;
```

### Login Response
```json
{
  "user": {
    "id": "SA",
    "username": "...",
    "name": "FullName",
    "TINGKAT": 5,
    "STATUS": 31,
    "access": 31,
    "level": 5,
    "role": "user"
  },
  "token": "random64charstring"
}
```

## CORS Login Fix (Mei 2026)

### Problem
`Access to fetch at 'http://localhost:8080/api/auth/login' blocked by CORS`

### Cause
Server started tanpa `-t public` flag, routing Laravel tidak jalan.

### Fix
```bash
cd D:/TestLaB/Fitur/be-fitur
php -S 127.0.0.1:8080 -t public
```

### Verify CORS
```bash
curl -s -X OPTIONS -H "Origin: http://localhost:3000" -H "Access-Control-Request-Method: POST" -i http://localhost:8080/api/auth/login | grep "Access-Control"
```

Harus ada: `Access-Control-Allow-Origin: *`
