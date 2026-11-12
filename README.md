# Wallows Official Web - Basis Data

Repositori ini berisi struktur dan contoh operasi CRUD untuk basis data MySQL
proyek Wallows Official Web. Total ada 19 tabel.

## Isi folder database

| File | Keterangan |
|---|---|
| `database/wallows.sql` | Struktur 19 tabel, kunci utama, kunci asing, dan aturan ON DELETE |
| `database/1_insert.sql` | Tahap 1: INSERT data contoh semua tabel |
| `database/2_select.sql` | Tahap 1: SELECT semua tabel dan JOIN untuk tiap halaman |
| `database/3_update.sql` | Tahap 2: UPDATE satu baris per tabel memakai WHERE |
| `database/4_delete.sql` | Tahap 2: DELETE tabel anak lebih dulu |

## Cara menjalankan dengan phpMyAdmin

1. Siapkan MySQL 8 atau MariaDB, misalnya lewat XAMPP atau Laragon.
2. Buka phpMyAdmin, lalu buat database baru bernama `wallows`.
3. Pilih database `wallows`, masuk ke tab Import, lalu impor file dengan urutan:
   1. `database/wallows.sql`
   2. `database/1_insert.sql`
   3. `database/2_select.sql`
   4. `database/3_update.sql`
   5. `database/4_delete.sql`
4. File `3_update.sql` mengubah data dan `4_delete.sql` menghapus data contoh.
   Jalankan `4_delete.sql` paling akhir. Jika ingin mengulang dari awal,
   impor ulang seluruh file dari langkah 3.

## Cara menjalankan lewat terminal

```bash
mysql -u root -p -e "CREATE DATABASE wallows"
mysql -u root -p wallows < database/wallows.sql
mysql -u root -p wallows < database/1_insert.sql
mysql -u root -p wallows < database/2_select.sql
mysql -u root -p wallows < database/3_update.sql
mysql -u root -p wallows < database/4_delete.sql
```

## Konfigurasi koneksi database

1. Salin `config/koneksi.example.php` menjadi `config/koneksi.php`.
2. Sesuaikan `$host`, `$port`, `$dbname`, `$username`, dan `$password` dengan
   pengaturan MySQL Anda.
3. File `config/koneksi.php` tidak di-commit karena berisi kredensial. Yang
   di-commit hanya file contohnya.

## Catatan

- Data di `1_insert.sql` adalah data contoh untuk latihan, bukan data resmi.
- Kolom `kata_sandi` diisi placeholder `GANTI_DENGAN_HASH`. Ganti dengan hash
  sungguhan sebelum dipakai di lingkungan nyata.
- `database/wallows.sql` sudah berisi data awal untuk tabel `kategori_foto` dan
  `kategori_produk`. Karena itu `1_insert.sql` memakai `INSERT IGNORE` untuk dua
  tabel tersebut agar tidak terjadi duplikat.
- File `config/koneksi.php` diabaikan oleh git karena berisi kredensial lokal.
