# Delphi to Laravel Pattern Mapping

Reference guide for mapping Delphi patterns to Laravel equivalents.

## Function Category Mapping

### Validation Functions

| Delphi Pattern | Laravel Equivalent |
|----------------|-------------------|
| `CekPeriode` | FormRequest `rules()` method |
| `IsLockPeriode` | Service method returning bool |
| `Check_Nomor` | Validation in Service or Request |
| `Validate*` | FormRequest or custom validator |

**Laravel Implementation Examples:**

```php
// FormRequest validation
public function rules(): array
{
    return [
        'periode' => 'required|date|after:start_date',
        'bulan' => 'required|integer|min:1|max:12',
        'tahun' => 'required|integer|min:2020|max:2100',
    ];
}

// Service validation
public function isLocked(int $bulan, int $tahun): bool
{
    return LockPeriod::where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->where('is_locked', true)
        ->exists();
}

// Custom validation logic
public function validateNoBukti(string $noBukti): bool
{
    return DB::table('dbtrans')
        ->where('NoBukti', $noBukti)
        ->exists();
}
```

### Database Operations

| Delphi Pattern | Laravel Equivalent |
|----------------|-------------------|
| `TADOQuery.SQL.Add` | `DB::table()->where()` |
| `Parameters[0].Value` | Parameter binding (`->where('field', $value)`) |
| `Open` | `->get()` |
| `ExecSQL` | `->insert()`, `->update()`, `->delete()` |
| `Close; SQL.Clear` | Query chaining |

**Laravel Implementation Examples:**

```php
// Delphi: SELECT with parameters
// with DM.QuCari do
//   SQL.Add('SELECT * FROM dbPeriode WHERE UserID=:0 AND Bulan=:1');
//   Parameters[0].Value := Nama;
//   Parameters[1].Value := Bulan;
//   Open;

// Laravel: Equivalent
$results = DB::table('dbPeriode')
    ->where('UserID', $nama)
    ->where('Bulan', $bulan)
    ->get();

// Delphi: INSERT
// SQL.Add('INSERT INTO dbLogFile (Tahun, Bulan, Pemakai) VALUES (:0, :1, :2)');
// Parameters[0].Value := tahun;
// Parameters[1].Value := bulan;
// Parameters[2].Value := pemakai;
// ExecSQL;

// Laravel: Equivalent
DB::table('dbLogFile')->insert([
    'Tahun' => $tahun,
    'Bulan' => $bulan,
    'Pemakai' => $pemakai,
    'Tanggal' => now(),
]);

// Delphi: UPDATE
// SQL.Add('UPDATE dbflpass SET status=:0 WHERE userid=:1');
// ExecSQL;

// Laravel: Equivalent
DB::table('dbflpass')
    ->where('userid', $userId)
    ->update(['status' => $status]);
```

### Stored Procedures

| Delphi Pattern | Laravel Equivalent |
|----------------|-------------------|
| `Exec SP_Name :0, :1` | `DB::statement('EXEC SP_Name ?, ?', [$p1, $p2])` |

```php
// Delphi:
// sql.Add('Exec SP_UrutNoKAS :0, :1, :2, :3, :4');
// Parameters[0].Value := Tipe;
// Parameters[1].Value := Tipetrans;
// ...

// Laravel:
$result = DB::statement('EXEC SP_UrutNoKAS ?, ?, ?, ?, ?', [
    $tipe,
    $tipetrans,
    $param3,
    $bulan,
    $tahun
]);
```

### Utility Functions

| Delphi Pattern | Laravel Equivalent |
|----------------|-------------------|
| `NewNo` (zero-padding) | `Str::padLeft($no, $digit, '0')` |
| `BulanRomawi` | Custom helper or use library |
| `IntToStr` | Type casting `(string)$int` or `strval()` |
| `StrToInt` | Type casting `(int)$str` or `intval()` |
| `QuotedStr` | Usually not needed (Eloquent handles) |
| `Copy` (substring) | `substr()` or `Str::substr()` |
| `Pos` (find) | `str_pos()` or `Str::strpos()` |

**Laravel Implementation Examples:**

```php
use Illuminate\Support\Str;

// Zero-padding (Delphi: NewNo)
$number = Str::padLeft($no, $digit, '0');

// Substring (Delphi: Copy)
$substring = Str::substr($string, $start, $length);

// String position (Delphi: Pos)
$position = Str::strpos($haystack, $needle);

// String conversion (Delphi: IntToStr)
$string = (string) $integer;

// Integer conversion (Delphi: StrToInt)
$integer = (int) $string;

// Roman numerals - use helper
function toRomanNumerals(int $month): string
{
    $romans = ['I', 'II', 'III', 'IV', 'V', 'VI',
               'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    return $romans[$month - 1] ?? '';
}
```

### Logging Functions

| Delphi Pattern | Laravel Equivalent |
|----------------|-------------------|
| `LoggingData` | `Log::info()` or Activity Log |
| Custom log table | Laravel Activitylog package |
| ShowMessage | Not applicable (backend) |

**Laravel Implementation Examples:**

```php
// Simple logging
use Illuminate\Support\Facades\Log;

Log::info('User action', [
    'user' => $pemakai,
    'action' => $aktivitas,
    'source' => $sumber,
    'no_bukti' => $noBukti,
]);

// Database logging (similar to Delphi's custom table)
DB::table('dblogfile')->insert([
    'Tahun' => $periodThn,
    'Bulan' => $periodBln,
    'Tanggal' => now(),
    'Pemakai' => $pemakai,
    'Aktivitas' => $aktivitas,
    'Sumber' => $sumber,
    'NoBukti' => $noBukti,
    'Keterangan' => $keterangan,
]);

// Activity logging (with spatie/laravel-activitylog)
activity()
    ->causedBy(auth()->user())
    ->withProperties(['no_bukti' => $noBukti])
    ->log($aktivitas);
```

### Date/Time Functions

| Delphi Pattern | Laravel Equivalent |
|----------------|-------------------|
| `Now` | `now()` or `Carbon::now()` |
| `Date` | `->toDate()` or `->startOfDay()` |
| `DecodeDate` | `$date->year`, `$date->month`, `$date->day` |
| `EncodeDate` | `Carbon::create($y, $m, $d)` |
| `DayOfWeek` | `$date->dayOfWeek` |
| `FormatDateTime` | `$date->format('d/m/Y')` |

```php
use Carbon\Carbon;

// Current date/time
$now = now();
// or
$now = Carbon::now();

// Decode date (extract components)
$year = $date->year;
$month = $date->month;
$day = $date->day;

// Encode date (create from components)
$date = Carbon::create($year, $month, $day);

// Format date
$formatted = $date->format('d/m/Y');
```

## Global Variables Mapping

| Delphi Global | Laravel Equivalent |
|---------------|-------------------|
| `iduser` | `Auth::user()->NIK` or `Auth::id()` |
| `PeriodThn` | Session: `session('period_thn')` or Config |
| `PeriodBln` | Session: `session('period_bln')` or Config |
| `DM` (DataModule) | Service classes or Repositories |
| `Application` | Not applicable (console/api) |

## Naming Convention Mapping

| Delphi | Laravel | Notes |
|--------|---------|-------|
| PascalCase columns | PascalCase | `NoBukti` → `NoBukti` (exact match) |
| camelCase methods | camelCase | `getUserName()` → `getUserName()` |
| `T` prefix types | No prefix | `TUser` → `User` |
| `F` prefix fields | No prefix | `FUserName` → `$userName` |
| `m_` prefix private | `private` or `protected` | Convention only |

## Common SQL Patterns

| Delphi SQL | Laravel Query Builder |
|------------|----------------------|
| `SELECT * FROM table` | `DB::table('table')->get()` |
| `WHERE field = :0` | `->where('field', $value)` |
| `WHERE field LIKE :0` | `->where('field', 'like', "%{$value}%")` |
| `INSERT INTO table` | `->insert([...])` |
| `UPDATE table SET` | `->update([...])` |
| `DELETE FROM table` | `->delete()` |
| `JOIN table ON` | `->join()` or `->leftJoin()` |
| `ORDER BY field` | `->orderBy('field')` |
| `GROUP BY field` | `->groupBy('field')` |
| `MAX(field)` | `->max('field')` |
| `SUM(field)` | `->sum('field')` |
| `COUNT(*)` | `->count()` |

## Transaction Handling

| Delphi | Laravel |
|--------|---------|
| `StartTransaction` | `DB::beginTransaction()` |
| `Commit` | `DB::commit()` |
| `Rollback` | `DB::rollBack()` |

```php
// Laravel transaction pattern
DB::transaction(function () {
    // Your operations here
    DB::table('dbmaster')->insert([...]);
    DB::table('dbdetail')->insert([...]);
    // Automatically commits if successful
    // Automatically rolls back if exception
});
```

## Error Handling

| Delphi | Laravel |
|--------|---------|
| `try...except` | `try...catch` |
| `ShowMessage` | Log or return error response |
| `On E: Exception do` | `catch(\Exception $e)` |

```php
try {
    // Your code
} catch (\Exception $e) {
    Log::error('Error occurred', ['message' => $e->getMessage()]);
    throw $e; // or return error response
}
```
