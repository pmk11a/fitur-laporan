# Delphi Code Examples

This document contains common Delphi patterns found in the KSP application and their Laravel equivalents.

## Database Operations

### TADOQuery SELECT Pattern

```delphi
function CekPeriode(Nama: string; tgl: Tdatetime): Boolean;
begin
  with DM.QuCari do
  begin
    Close;
    SQL.Clear;
    SQL.Add('SELECT * FROM dbPeriode WHERE UserID = :0');
    Parameters[0].Value := Nama;
    Open;
    Result := RecordCount > 0;
  end;
end;
```

**Laravel Equivalent:**
```php
public function validatePeriode(string $nama, Carbon $tanggal): bool
{
    return Periode::where('user_id', $nama)
        ->where('tanggal', $tanggal->format('Y-m-d'))
        ->exists();
}
```

### TADOQuery INSERT Pattern

```delphi
with DM.QuCari do
begin
  Close;
  SQL.Clear;
  SQL.Add('INSERT INTO dbPengajuan (NoBukti, Tanggal, KodeCust)');
  SQL.Add('VALUES (:0, :1, :2)');
  Parameters[0].Value := NoBukti.Text;
  Parameters[1].Value := Tanggal.Date;
  Parameters[2].Value := KodeCust.Text;
  ExecSQL;
end;
```

**Laravel Equivalent:**
```php
DB::transaction(function () use ($data) {
    Pengajuan::create([
        'no_bukti' => $data['no_bukti'],
        'tanggal' => Carbon::parse($data['tanggal']),
        'kode_cust' => $data['kode_cust'],
    ]);
});
```

### TADOQuery UPDATE Pattern

```delphi
with DM.QuCari do
begin
  Close;
  SQL.Clear;
  SQL.Add('UPDATE dbPengajuan SET Status = :0 WHERE NoBukti = :1');
  Parameters[0].Value := 'APPROVED';
  Parameters[1].Value := NoBukti;
  ExecSQL;
end;
```

**Laravel Equivalent:**
```php
Pengajuan::where('no_bukti', $noBukti)->update([
    'status' => 'APPROVED'
]);
```

### TADOQuery DELETE Pattern

```delphi
with DM.QuCari do
begin
  Close;
  SQL.Clear;
  SQL.Add('DELETE FROM dbPengajuan WHERE NoBukti = :0');
  Parameters[0].Value := NoBukti;
  ExecSQL;
end;
```

**Laravel Equivalent:**
```php
Pengajuan::where('no_bukti', $noBukti)->delete();
```

## Validation Patterns

### CekKosong - Empty Check

```delphi
function CekKosong(Data: String): Boolean;
begin
  Result := Trim(Data) = '';
end;
```

**Laravel Validation:**
```php
'kode_cust' => 'required|string'
```

### CekAngka - Numeric Check

```delphi
function CekAngka(Data: String): Boolean;
begin
  Result := not TryStrToFloat(Data, _nilai);
end;
```

**Laravel Validation:**
```php
'pinjaman' => 'required|numeric'
```

### CekPeriode - Date Validation

```delphi
function CekPeriode(Nama:string;tgl:Tdatetime):Boolean;
begin
  with DM.QuCari do
  begin
    Close;
    SQL.Clear;
    SQL.Add('SELECT * FROM dbPeriode WHERE UserID = :0 AND Tgl = :1');
    Parameters[0].Value := Nama;
    Parameters[1].Value := tgl;
    Open;
    Result := RecordCount > 0;
  end;
end;
```

**Laravel Validation:**
```php
'tanggal' => 'required|date|after_or_equal:today|exists:dbperiode,tgl'
```

### CekExist - Existence Check

```delphi
function CekCustomer(Kode: String): Boolean;
begin
  with DM.QuCari do
  begin
    Close;
    SQL.Clear;
    SQL.Add('SELECT * FROM dbCustomer WHERE KodeCust = :0');
    Parameters[0].Value := Kode;
    Open;
    Result := not IsEmpty;
  end;
end;
```

**Laravel Validation:**
```php
'kode_cust' => 'required|exists:dbcustomer,kode_cust'
```

## Number Generation

### Check_Nomor Pattern

```delphi
function Check_Nomor(Bulan,Tahun:integer;Tipe:String;PPn:String;
  var TipeTrans:String;var PlusPPN:String;var Nomor:String;Devisi:String):Boolean;
begin
  with DM.QuCari do
  begin
    Close;
    SQL.Clear;
    SQL.Add('SELECT Nomor FROM dbNomorPK WHERE Tipe = :0 AND Bulan = :1 AND Tahun = :2');
    Parameters[0].Value := Tipe;
    Parameters[1].Value := Bulan;
    Parameters[2].Value := Tahun;
    Open;
    if not IsEmpty then
    begin
      Nomor := FieldByName('Nomor').AsString;
      Result := True;
    end
    else
      Result := False;
  end;
end;
```

**Laravel Service:**
```php
public function generateNumber(string $tipe, int $bulan, int $tahun): string
{
    $record = NomorPk::where('tipe', $tipe)
        ->where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->lockForUpdate()
        ->first();

    if (!$record) {
        $nomor = '0001';
        NomorPk::create([
            'tipe' => $tipe,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'nomor' => $nomor,
        ]);
    } else {
        $nomor = str_pad((int)$record->nomor + 1, 4, '0', STR_PAD_LEFT);
        $record->update(['nomor' => $nomor]);
    }

    return $tipe . $bulan . $tahun . $nomor;
}
```

## Business Logic

### RateOfInterest - Interest Calculation

```delphi
function RateOfInterst(Pinjaman,Angsuran:Real;Tenor:Integer):Real;
var
  pokok, bunga: Real;
begin
  pokok := Pinjaman / Tenor;
  bunga := (Pinjaman * 0.12) / 12;  // 12% per tahun
  Result := pokok + bunga;
end;
```

**Laravel Model Method:**
```php
public function calculateAngsuran(): float
{
    $pokok = $this->pinjaman / $this->tenor;
    $bunga = ($this->pinjaman * $this->bunga_persen / 100) / 12;
    return $pokok + $bunga;
}
```

### LoggingData Pattern

```delphi
Procedure LoggingData(pPemakai, pAktivitas, pSumber, pNoBukti, pKeterangan: String);
begin
  With DM.QuLogFile do
  begin
    Close;
    SQL.Clear;
    SQL.Add('Insert into dbLogFile (Tahun, Bulan, Tanggal,Pemakai,Aktivitas,Sumber,NoBukti,Keterangan)');
    Sql.Add('values ('+QuotedStr(PeriodThn)+','+QuotedStr(PeriodBln)+', Getdate(),'+QuotedStr(pPemakai)+','+QuotedStr(pAktivitas)+','+QuotedStr(pSumber)+','+QuotedStr(pNoBukti)+','+QuotedStr(pKeterangan)+')');
    try
      ExecSQL;
    except
    end;
  end;
end;
```

**Laravel Service:**
```php
private function logActivity(string $action, string $source, string $noBukti, ?string $keterangan = null): void
{
    DB::table('dbLogFile')->insert([
        'Tahun' => Carbon::now()->year,
        'Bulan' => Carbon::now()->month,
        'Tanggal' => Carbon::now(),
        'Pemakai' => auth()->id() ?? 'system',
        'Aktivitas' => $action,
        'Sumber' => $source,
        'NoBukti' => $noBukti,
        'Keterangan' => $keterangan,
    ]);
}
```

### MyCek_Lock_Periode - Period Lock Check

```delphi
function MyCek_Lock_Periode(Bukti:string):Boolean;
begin
  with DM.QuCari do
  begin
    Close;
    SQL.Clear;
    SQL.Add('SELECT * FROM dbLockPeriode WHERE Tgl = :0');
    Parameters[0].Value := Bukti;
    Open;
    Result := not IsEmpty;
  end;
end;
```

**Laravel Service:**
```php
public function isLocked(Carbon $tanggal): bool
{
    return LockPeriode::where('tgl', $tanggal->format('Y-m-d'))
        ->exists();
}

public function validateNotLocked(Carbon $tanggal): void
{
    if ($this->isLocked($tanggal)) {
        throw new Exception('Periode terkunci, tidak dapat melakukan transaksi');
    }
}
```

## UI Event Handlers (Skip These)

These are frontend-only and should NOT be migrated to Laravel:

```delphi
// Skip - Form lifecycle
procedure FormShow(Sender: TObject);
procedure FormClose(Sender: TObject; var Action: TCloseAction);
procedure FormCreate(Sender: TObject);
procedure FormDestroy(Sender: TObject);

// Skip - UI events
procedure ButtonClick(Sender: TObject);
procedure EditEnter(Sender: TObject);
procedure EditExit(Sender: TObject);
procedure GridKeyDown(Sender: TObject; var Key: Word; Shift: TShiftState);

// Skip - Visual updates
procedure RefreshGrid;
procedure UpdateDisplay;
procedure SetFocus;
```

## Date/Time Utilities

### BulanRomawi - Roman Month Conversion

```delphi
function BulanRomawi(Tanggal:Tdatetime;Mode:String):String;
var
  Bulan: Integer;
  Romawi: array[1..12] of String;
begin
  Romawi[1] := 'I';
  Romawi[2] := 'II';
  // ... etc
  Bulan := MonthOf(Tanggal);
  Result := Romawi[Bulan];
end;
```

**Laravel Helper:**
```php
function bulanRomawi(Carbon $tanggal): string
{
    $romawi = ['I', 'II', 'III', 'IV', 'V', 'VI',
               'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    return $romawi[$tanggal->month - 1];
}
```

## String Manipulation

### SLeft / SRight - String Trimming

```delphi
Function SLeft(mString : String; mPos : Integer) : String;
begin
  Result := Copy(mString, 1, mPos);
end;

Function SRight(mString : String; mPos : Integer) : STring;
begin
  Result := Copy(mString, Length(mString) - mPos + 1, mPos);
end;
```

**Laravel Built-in:**
```php
// Use Laravel/PHP built-in
Str::limit($string, $limit);  // Instead of SLeft
substr($string, -$pos);       // Instead of SRight
```

## Transaction Pattern

```delphi
with DM do
begin
  conn.BeginTrans;
  try
    // Multiple operations
    QuCari.ExecSQL;
    QuCari2.ExecSQL;
    conn.CommitTrans;
  except
    conn.RollbackTrans;
    raise;
  end;
end;
```

**Laravel:**
```php
DB::transaction(function () {
    // Multiple operations
    DB::table('table1')->insert([...]);
    DB::table('table2')->insert([...]);
});
```
