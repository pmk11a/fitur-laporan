# Skrip Generator Manual Book

## Persyaratan

- Node.js 18+
- npm

## Instalasi

```bash
# Install dependencies
cd .claude/skills/manual-generator
npm install
```

## Penggunaan

### Generate Satu Modul

```bash
node scripts/generate.js 02-01-barang
```

### Generate Semua Modul

```bash
node scripts/generate.js --all
```

### Parse Model

```bash
node scripts/parse-model.js BARANG
```

### Parse Validations

```bash
node scripts/extract-validations.js StoreBarangRequest
```

## Struktur Output

```
docs/manual-book/docs/
├── 02-01-barang/
│   ├── README.md        # Overview + prasyarat
│   ├── form-input.md     # Detail form fields
│   ├── alur-kerja.md     # Workflow + flowchart
│   └── error-handling.md # Error list
└── ...
```

## Integrasi ke Workflow Migrasi

Setelah migrasi modul selesai diverifikasi:

```bash
# 1. Generate manual
node .claude/skills/manual-generator/scripts/generate.js 02-01-barang

# 2. Review output di docs/manual-book/docs/02-01-barang/

# 3. Commit
git add docs/manual-book/docs/02-01-barang/
git commit -m "docs: add manual for 02-01-barang"
```