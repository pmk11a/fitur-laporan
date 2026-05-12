# Error Handling: {{moduleName}}

## Gambaran Umum

Halaman ini berisi daftar error yang mungkin terjadi saat menggunakan modul {{moduleName}}, beserta penyebab dan solusinya.

## Kategori Error

### 1. Error Validasi Input

*Error yang terjadi saat data input tidak valid atau field yang wajib diisi kosong*

| Kode | Pesan | Penyebab | Solusi |
|------|-------|----------|--------|
{{#each validationErrors}}
| `{{this.code}}` | {{this.message}} | {{this.cause}} | {{this.solution}} |
{{/each}}

### 2. Error Otorisasi

*Error yang terjadi karena masalah hak akses*

| Kode | Pesan | Penyebab | Solusi |
|------|-------|----------|--------|
{{#each authorizationErrors}}
| `{{this.code}}` | {{this.message}} | {{this.cause}} | {{this.solution}} |
{{/each}}

### 3. Error Data

*Error yang terjadi karena masalah integritas data atau foreign key*

| Kode | Pesan | Penyebab | Solusi |
|------|-------|----------|--------|
{{#each dataErrors}}
| `{{this.code}}` | {{this.message}} | {{this.cause}} | {{this.solution}} |
{{/each}}

### 4. Error Sistem

*Error yang terjadi karena masalah pada sistem*

| Kode | Pesan | Penyebab | Solusi |
|------|-------|----------|--------|
{{#each systemErrors}}
| `{{this.code}}` | {{this.message}} | {{this.cause}} | {{this.solution}} |
{{/each}}

## Format Kode Error

Semua error code memiliki format: `{{errorPrefix}}-{{category}}-{{number}}`

Contoh: `MBRG-VALID-001` untuk error validasi pertama di modul barang.

| Komponen | Keterangan |
|----------|------------|
| `{{errorPrefix}}` | Prefix unik per modul (misal: MBRG, MPENJ) |
| `VALID` | Kategori error |
| `001` | Nomor urut error |

## Penanganan Error

### Langkah Umum

1. **Catat kode error** - Kode error berada di dalam tanda backtick `` ` ``
2. **Baca pesan error** - Pesan error biasanya sudah menjelaskan masalah
3. **Cek tabel di atas** - Cari error code di tabel sesuai kategori
4. **Ikuti solusi** - Terapkan solusi yang diberikan
5. **Hubungi IT** - Jika error tidak ada dalam daftar, hubungi tim IT

### Informasi yang Harus Disiapkan

Saat melaporkan error ke tim IT, siapkan informasi berikut:

- Kode error lengkap (misal: `MBRG-VALID-001`)
- Pesan error yang muncul
- Screenshot halaman error
- Langkah-langkah yang dilakukan sebelum error muncul
- Data yang sedang diinput (tanpa password/rahasia)

## Getting Help

:::info Butuh Bantuan?
Jika Anda menemukan error yang tidak ada dalam dokumentasi ini:

1. Catat kode error dan pesan lengkap
2. Screenshot halaman error
3. Laporkan ke tim IT dengan subjek: "Bug Manual - [Module] - [Error Code]"

Hubungi kami melalui:
- Email: support@your-org.com
- GitHub Issues: [Link](https://github.com/your-org/keu-app/issues)
:::