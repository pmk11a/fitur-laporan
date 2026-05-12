# Laravel Best Practices for Delphi Migration

This document contains Laravel patterns and best practices for migrating Delphi code to Laravel.

> **Note:** Keu-app uses database `dbwbcp2` with tables like `BARANG`, `CUSTOMER`, `SATUAN`, etc.
> KSP-specific examples preserved in `laravel-patterns_kps_reference.md`.

## Model Patterns

### Reading from Existing Tables

Since we're using an existing SQL Server database, models should reference the actual table names:

```php
class Barang extends Model
{
    protected $table = 'BARANG';     // Use actual table name
    protected $primaryKey = 'KODE';  // Use actual column name
    public $incrementing = false;    // If primary key is not auto-increment
    protected $keyType = 'string';  // If primary key is string
}
```

### Defining Fillable Fields

Always specify fillable fields from the actual database schema:

```php
protected $fillable = [
    'no_bukti',
    'tanggal',
    'kode_cust',
    'kode_prd',
    'pinjaman',
    'tenor',
    'bunga_persen',
    'status',
];
```

### Type Casting

Define casts for proper type conversion:

```php
protected $casts = [
    'tanggal' => 'date',
    'pinjaman' => 'decimal:2',
    'bunga_persen' => 'decimal:2',
    'status' => 'string',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
];
```

### Relationships

Define relationships based on foreign keys:

```php
// belongsTo - for "belongs to" relationship
public function customer(): BelongsTo
{
    return $this->belongsTo(Customer::class, 'kode_cust', 'kode_cust');
}

// hasMany - for "has many" relationship
public function angsuran(): HasMany
{
    return $this->hasMany(Angsuran::class, 'no_bukti', 'no_bukti');
}

// belongsToMany - for many-to-many relationship
public function products(): BelongsToMany
{
    return $this->belongsToMany(Product::class, 'table_pivot', 'pengajuan_id', 'product_id');
}
```

### Business Logic Methods

Add business logic methods directly to the model:

```php
public function calculateAngsuran(): float
{
    $pokok = $this->pinjaman / $this->tenor;
    $bunga = ($this->pinjaman * $this->bunga_persen / 100) / 12;
    return $pokok + $bunga;
}

public function isDraft(): bool
{
    return $this->status === 'DRAFT';
}

public function isApproved(): bool
{
    return $this->status === 'APPROVED';
}
```

## Service Patterns

### Dependency Injection

Use constructor dependency injection:

```php
class PengajuanService
{
    public function __construct(
        private PeriodLockService $periodLock,
        private NumberSequenceService $numberSequence,
        private ActivityLogService $activityLog
    ) {}
}
```

### Transaction Management

Always wrap database operations in transactions:

```php
public function create(array $data): Pengajuan
{
    return DB::transaction(function () use ($data) {
        // Validate
        $this->validatePeriod($data['tanggal']);

        // Generate number
        $data['no_bukti'] = $this->generateNumber($data['tanggal']);

        // Create record
        $pengajuan = Pengajuan::create($data);

        // Create related records
        if (isset($data['jaminan'])) {
            $pengajuan->jaminan()->create($data['jaminan']);
        }

        // Log activity
        $this->logActivity('CREATE', 'Pengajuan', $pengajuan->no_bukti);

        return $pengajuan;
    });
}
```

### Validation in Services

Validate business rules in service methods:

```php
private function validatePeriod(Carbon $tanggal): void
{
    if ($this->periodLock->isLocked($tanggal)) {
        throw new Exception('Periode terkunci');
    }
}

private function validatePinjamanLimit(string $kodeCust, float $pinjaman): void
{
    $customer = Customer::findOrFail($kodeCust);
    $limit = $customer->plafon_pinjaman;

    if ($pinjaman > $limit) {
        throw new Exception('Pinjaman melebihi plafon');
    }
}
```

## Controller Patterns

### Resource Controllers

Follow RESTful conventions:

```php
class PengajuanController extends Controller
{
    public function index() { /* List */ }
    public function store(Request $request) { /* Create */ }
    public function show(Pengajuan $pengajuan) { /* Detail */ }
    public function update(Request $request, Pengajuan $pengajuan) { /* Update */ }
    public function destroy(Pengajuan $pengajuan) { /* Delete */ }
}
```

### Service Injection

Inject services via constructor:

```php
public function __construct(
    private PengajuanService $service
) {}
```

### API Resources

Use API Resources for consistent response format:

```php
// Create resource: php artisan make:resource PengajuanResource

class PengajuanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'no_bukti' => $this->no_bukti,
            'tanggal' => $this->tanggal->format('Y-m-d'),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'pinjaman' => (float) $this->pinjaman,
            'angsuran' => $this->calculateAngsuran(),
        ];
    }
}
```

## Request Validation

### FormRequest Classes

Create dedicated FormRequest classes:

```php
class StorePengajuanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'kode_cust' => 'required|string|exists:dbcustomer,kode_cust',
            'tanggal' => 'required|date|after_or_equal:today',
            'pinjaman' => 'required|numeric|min:500000|max:1000000000',
            'tenor' => 'required|integer|min:1|max:60',
        ];
    }

    public function messages(): array
    {
        return [
            'kode_cust.required' => 'Kode Customer harus diisi',
            'pinjaman.min' => 'Pinjaman minimum Rp 500.000',
        ];
    }
}
```

### Custom Validation Rules

For complex validation, create custom rules:

```php
// Create: php artisan make:rule PeriodNotLocked

class PeriodNotLocked implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $periodLock = app(PeriodLockService::class);

        if ($periodLock->isLocked(Carbon::parse($value))) {
            $fail('Periode terkunci');
        }
    }
}

// Use in request:
'tanggal' => ['required', 'date', new PeriodNotLocked()]
```

## Database Patterns

### Query Builder vs Eloquent

Use Eloquent for models, Query Builder for complex queries:

```php
// Eloquent - simple queries
$barang = Barang::where('AKTIF', '1')->get();

// Query Builder - complex queries
$results = DB::table('BELI as b')
    ->join('SUPPLIER as s', 'b.KODESPL', '=', 's.KODESPL')
    ->select('b.*', 's.NAMASPL')
    ->where('b.TANGGAL', '>=', $startDate)
    ->paginate();
```

### Raw Queries (When Necessary)

For very complex queries, use raw SQL:

```php
$results = DB::select('
    SELECT p.*, c.nama_cust
    FROM dbpengajuan p
    INNER JOIN dbcustomer c ON p.kode_cust = c.kode_cust
    WHERE p.tanggal BETWEEN ? AND ?
', [$startDate, $endDate]);
```

### Transactions

Always use DB::transaction():

```php
DB::transaction(function () {
    // All operations here are atomic
    Pengajuan::create([...]);
    Angsuran::create([...]);
    Jaminan::create([...]);
});
```

## Error Handling

### Exception Handling

```php
try {
    $pengajuan = $this->service->create($request->validated());
    return new PengajuanResource($pengajuan);
} catch (ValidationException $e) {
    return response()->json(['message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
} catch (Exception $e) {
    Log::error('Pengajuan creation failed', ['error' => $e->getMessage()]);
    return response()->json(['message' => 'Terjadi kesalahan'], 500);
}
```

## Logging

### Activity Logging

```php
use Illuminate\Support\Facades\Log;

// Info level
Log::info('Pengajuan created', ['no_bukti' => $pengajuan->no_bukti]);

// Error level
Log::error('Failed to create pengajuan', ['error' => $e->getMessage()]);

// Custom log channel
Log::channel('audit')->info('User action', [
    'user' => auth()->id(),
    'action' => 'CREATE',
    'target' => $pengajuan->no_bukti
]);
```

## Naming Conventions

| Delphi | Laravel |
|--------|---------|
| FrmPengajuan | PengajuanController |
| MyProcedure | UtilityService |
| dbPengajuan | Use $table = 'dbpengajuan' |
| KodeCust | kode_cust (column) |
| NoBukti | no_bukti (column) |
| CekPeriode | validatePeriode() |
| btTambahClick | store() method |
| btEditClick | update() method |
| btHapusClick | destroy() method |

## SQL Server Specific

### Connection Configuration

```php
// config/database.php
'sqlsrv' => [
    'driver' => 'sqlsrv',
    'host' => env('SQLSRV_HOST', 'localhost'),
    'port' => env('SQLSRV_PORT', '1433'),
    'database' => env('SQLSRV_DATABASE', 'ksppare'),
    'username' => env('SQLSRV_USERNAME', 'sa'),
    'password' => env('SQLSRV_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
],
```

### Date Format

SQL Server uses different date format:

```php
// Input to SQL Server
DB::table('dbpengajuan')->insert([
    'tanggal' => Carbon::parse('2024-01-01')->format('Y-m-d H:i:s'),
]);

// Output from SQL Server
$pengajuan->tanggal->format('d/m/Y'); // Indonesian format
```

## Testing

### Unit Test Example

```php
class PengajuanServiceTest extends TestCase
{
    public function test_calculate_angsuran()
    {
        $pengajuan = Pengajuan::factory()->make([
            'pinjaman' => 1000000,
            'tenor' => 12,
            'bunga_persen' => 12,
        ]);

        $angsuran = $pengajuan->calculateAngsuran();

        $this->assertEquals(93333.33, round($angsuran, 2));
    }
}
```

### Feature Test Example

```php
public function test_create_pengajuan()
{
    $data = [
        'kode_cust' => 'CUST001',
        'tanggal' => now()->toDateString(),
        'pinjaman' => 5000000,
        'tenor' => 12,
    ];

    $response = $this->postJson('/api/v1/pengajuan', $data);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'no_bukti',
                'tanggal',
                'kode_cust',
            ]
        ]);
}
```
