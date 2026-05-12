# Function Mappings

Mapping Delphi functions/procedures to Laravel equivalents.

> **Note:** Keu-app uses database `dbwbcp2` with Delphi source in `pwt/` folder.
> KSP-specific examples preserved in `mappings_kps_reference.md`.

## Table of Contents

- [Delphi Function → Laravel Location](#delphi-function--laravel-location)
- [Database Operation Mappings](#database-operation-mappings)
- [Naming Convention Mappings](#naming-convention-mappings)
- [Event Handler Mappings (Skip - Frontend Only)](#event-handler-mappings-skip---frontend-only)
- [Helper Function Mappings](#helper-function-mappings)

---

## Delphi Function → Laravel Location

| Delphi Pattern | Laravel Location | Method | Example |
|----------------|------------------|--------|---------|
| `CekKosong()` | Request | `required` rule | Required validation |
| `CekAngka()` | Request | `numeric` rule | Numeric validation |
| `CekTanggal()` | Request | `date` rule | Date validation |
| `CekExist()` | Request | `exists` rule | Existence validation |
| `GetData` | Controller | `index()` | Data retrieval |
| `SaveData` | Controller | `store()` | Data persistence |
| `DeleteData` | Controller | `destroy()` | Data deletion |
| `TampilData` | Controller | `show()` | Display single item |
| `CekDuplicate()` | Service | `checkDuplicate()` | Duplicate check |
| `GenerateNoUrut()` | Service | `generateNumber()` | Number generation |

## Database Operation Mappings

### TADOQuery SELECT → Laravel

**Delphi:**
```delphi
with DM.QuCari do
begin
  Close;
  SQL.Clear;
  SQL.Add('SELECT * FROM BARANG WHERE KODE = :0');
  Parameters[0].Value := Kode.Text;
  Open;
  Result := not IsEmpty;
end;
```

**Laravel:**
```php
public function show(string $kode): JsonResponse
{
    $barang = BARANG::where('KODE', $kode)->first();
    if (!$barang) {
        return response()->json(['success' => false, 'message' => 'Not found'], 404);
    }
    return response()->json(['success' => true, 'data' => $barang]);
}
```

### TADOQuery INSERT → Laravel

**Delphi:**
```delphi
SQL.Add('INSERT INTO BARANG (KODE, NAMA, HARGA) VALUES (:0, :1, :2)');
Parameters[0].Value := Kode.Text;
Parameters[1].Value := Nama.Text;
Parameters[2].Value :=Harga.Value;
ExecSQL;
```

**Laravel:**
```php
public function store(BarangRequest $request): JsonResponse
{
    $barang = BARANG::create([
        'KODE' => $request->kode,
        'NAMA' => $request->nama,
        'HARGA' => $request->harga,
    ]);
    return response()->json(['success' => true, 'data' => $barang], 201);
}
```

## Naming Convention Mappings

| Delphi | Laravel | Notes |
|--------|---------|-------|
| FrmBarang | BarangController | Remove "Frm" prefix |
| FrmCustomer | CustomerController | Remove "Frm" prefix |
| FrmSupplier | SupplierController | Remove "Frm" prefix |
| CekKosong | required | "Cek" → validation rule |
| GetData | index / getData | Data retrieval |
| SaveData | store / save | Data persistence |
| DeleteData | destroy / delete | Data deletion |
| TampilData | show / detail | Display single item |
| btSimpanClick | store() | Button → Controller method |
| btHapusClick | destroy() | Button → Controller method |
| btEditClick | update() | Button → Controller method |

## Event Handler Mappings (Skip - Frontend Only)

| Delphi Event | Laravel Equivalent | Action |
|--------------|-------------------|--------|
| FormShow | - | Skip - Frontend (React state) |
| FormClose | - | Skip - Frontend (unmount) |
| FormCreate | - | Skip - Frontend (component mount) |
| btTambahClick | store() | Button → Controller method |
| btEditClick | update() | Button → Controller method |
| btHapusClick | destroy() | Button → Controller method |

## Form Reference Detection

**Delphi Pattern:**
```delphi
begin
  Application.CreateForm(TFrmSatuan, FrmSatuan);
  FrmSatuan.ShowModal;
end;
```

**Detection:**
- Pattern: `Application.CreateForm(TFrmSatuan, ...)`
- Find: `pwt/Master/Satuan/FrmSatuan.pas`

**In FrmSatuan.pas (found):**
```delphi
SQL.Add('Select * from SATUAN where KODESAT=:0');
```

**Generate in BarangController:**
```php
public function __construct(
    private BarangService $barangService,
    private SatuanService $satuanService  // ← Added
) {}

public function getSatuan(string $kodesat): JsonResponse
{
    $satuan = $this->satuanService->findByKode($kodesat);
    return response()->json(['success' => true, 'data' => $satuan]);
}
```
