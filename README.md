# Ruang Pulang

Website yang dibangun untuk komunitas sosial di Manado bernama **Ruang Pulang**

## Fitur

| URL | Isi |
| --- | --- |
| `/` | Beranda |
| `/tentang` | Sejarah, tujuan, visi, dan misi |
| `/kegiatan` | Jenis kegiatan komunitas, masing-masing dengan satu sampai tiga foto atau video |
| `/pengurus` | Pendiri dan pengurus (halaman Portfolio pada tugas) |
| `/blog` | Daftar artikel dengan pencarian, pengurutan, dan halaman detail |
| `/kontak` | Media sosial dan informasi bantuan darurat |
| `/sponsorship` | Sponsor yang mendukung kegiatan komunitas |
| `/bergabung` | Ketentuan keanggotaan dan tautan grup WhatsApp |
| `/tulis-artikel` | Kiriman artikel dari pengunjung, tampil setelah disetujui admin |
| `/kelola` | Menu admin, berisi Kelola Profil Pengurus (`/kelola/pengurus`), Kelola Blog (`/kelola/blog`), Kelola Sponsorship (`/kelola/sponsorship`), Kelola Kegiatan (`/kelola/kegiatan`) |

## Cara menjalankan

Syarat: PHP 8.4.1 atau lebih baru dengan ekstensi `pdo_mysql` (dibutuhkan Symfony 8 pada `composer.lock`), Composer, dan XAMPP untuk MySQL.

1. Buka XAMPP, lalu di **Manage Servers** nyalakan **MySQL Database**.
2. Buat database `ruangpulang` dengan collation `utf8mb4_unicode_ci`, lewat phpMyAdmin (<http://localhost/phpmyadmin>) atau perintah:
   ```bash
   /Applications/XAMPP/xamppfiles/bin/mysql -u root -e "CREATE DATABASE ruangpulang CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   ```
3. Jalankan:
   ```bash
   composer run setup
   php artisan serve
   ```

Buka <http://localhost:8000>. Perintah setup menyalin `.env.example` ke `.env` (sudah berisi koneksi MySQL XAMPP: user `root` tanpa kata sandi), membuat tabel, dan mengisinya dengan data pengurus, blog, sponsor, dan kegiatan.

Kalau port 3306 sudah dipakai MySQL lain, misalnya dari Homebrew, matikan dulu dengan `brew services stop mysql`.

Untuk menjalankan pengujian, gunakan `php artisan test`. Secara bawaan tes memakai SQLite di memori supaya cepat. Untuk menguji di MySQL, buat database `ruangpulang_test`, lalu jalankan `DB_CONNECTION=mysql DB_DATABASE=ruangpulang_test php artisan test`.

**Halaman Kelola:** jalankan `php artisan admin:password` untuk membuat kata sandi admin.

## Deploy ke hosting tanpa SSH

Cocok untuk hosting bersama (cPanel) yang tidak punya akses SSH. Server cukup memakai **PHP 8.4.1 atau lebih baru**. Di laptop aplikasi memakai MySQL, sedangkan di hosting memakai SQLite supaya tidak perlu mengatur database di cPanel. `deploy/build.sh` otomatis mengubah `.env` produksi ke SQLite.

**Pertama kali**

1. Di laptop, jalankan `deploy/build.sh`. Skrip ini menyiapkan salinan bersih di folder `build/`, memasang paket tanpa paket pengembangan, membuat `.env` produksi dan database baru berisi data awal, lalu meminta kata sandi admin. Hasilnya `build/ruangpulang-full.zip`.
2. Di cPanel, **File Manager**, unggah zip itu ke luar `public_html` (misalnya `/home/USERNAME/ruangpulang`) lalu **Extract**. Hapus zip-nya setelah itu, karena memuat `.env`.
3. Di **Domains**, arahkan **Document Root** domain ke `ruangpulang/public`.
4. Di **Select PHP Version**, pilih PHP 8.4 atau lebih dan aktifkan ekstensi `mbstring`, `pdo_sqlite`, `sqlite3`, `fileinfo`, `curl`, `xml`, `tokenizer`.
5. Beri izin tulis (775) pada `storage`, `bootstrap/cache`, `database`, dan `public/uploads` (beserta subfoldernya) lewat File Manager.
6. Aktifkan SSL, lalu buka domain untuk menguji.

Domain lain: `APP_URL=https://domainmu.com deploy/build.sh`.

**Pembaruan berikutnya**

Jalankan `deploy/build.sh --update`, lalu unggah dan Extract `build/ruangpulang-update.zip` ke folder yang sama dan pilih timpa. Zip ini tidak memuat `.env`, database, dan foto unggahan, sehingga data di server aman. Jangan mengunggah ulang `database.sqlite`, karena akan menimpa artikel dan data yang sudah ada di server. Kalau ada migrasi baru, jalankan `php artisan migrate --force` di server lewat **Cron Jobs** cPanel (jadwalkan satu kali, tunggu berjalan, lalu hapus).

**Cadangan**

Salin `database/database.sqlite` dan folder `public/uploads` (foto pengurus dan sponsor, serta foto dan video kegiatan) dari server secara berkala.

**Batas upload**

Foto maksimal 2 MB dan video kegiatan (MP4 atau WEBM) maksimal 15 detik dan 20 MB. Durasi video dicek di browser saat file dipilih. Batas PHP di server dinaikkan lewat `public/.user.ini`. Kalau upload video tetap gagal, isi nilai yang sama di **MultiPHP INI Editor** cPanel: `upload_max_filesize = 25M` dan `post_max_size = 70M`.

## Struktur

| Bagian | Lokasi |
| --- | --- |
| Routing | `routes/web.php` |
| Controller | `app/Http/Controllers` |
| View dan Layout | `resources/views/pages`, `resources/views/layouts/app.blade.php` |
| Blade Components | `resources/views/components` |
| Data artikel | Database, dengan data awal di `resources/data/posts.php` |
| Data pengurus | Database, dengan data awal di `resources/data/portofolioteam.php` |
| Data sponsor | Database, dengan data awal di `resources/data/sponsors.php` |
| Data kegiatan | Database, dengan data awal di `resources/data/activities.php` |
| Data profil | `resources/data` |

## Database dan relasi

| Tabel | Isi | Relasi |
| --- | --- | --- |
| `posts` | Artikel blog, baik kiriman pengunjung maupun tulisan admin | Berdiri sendiri |
| `divisions` | Divisi pengurus | One-to-many ke `members` |
| `members` | Penempatan pengurus di tiap divisi | Belongs-to `divisions` |
| `activities` | Kegiatan komunitas | One-to-many ke `activity_media`, many-to-many ke `sponsors` |
| `activity_media` | Satu sampai tiga foto atau video per kegiatan | Belongs-to `activities` |
| `sponsors` | Sponsor komunitas | Many-to-many ke `activities` |
| `activity_sponsor` | Tabel penghubung kegiatan dan sponsor | Pivot |

Relasi many-to-many diatur dari form Kelola Kegiatan lewat centang "Didukung oleh Sponsor". Hasilnya tampil di dua arah: kartu kegiatan menulis "Didukung oleh …" dan kartu sponsor menulis "Mendukung kegiatan …".

Data awal diisi oleh seeder di `database/seeders` dari file `resources/data`. Semua seeder memakai `firstOrCreate`, jadi aman dijalankan ulang tanpa menimpa data yang sudah diubah admin.

## Factory dan data demo

Setiap model konten punya factory di `database/factories`: `PostFactory`, `DivisionFactory`, `MemberFactory`, `SponsorFactory`, `ActivityFactory`, dan `ActivityMediaFactory`. Isinya kalimat berbahasa Indonesia sesuai tema komunitas, dengan nama dari Faker berlocale `id_ID`.

| Factory | State tambahan |
| --- | --- |
| `PostFactory` | `pending()` untuk kiriman yang menunggu persetujuan |
| `MemberFactory` | `withoutInstagram()`. Divisinya ikut dibuat lewat relasi kalau tidak ditentukan |
| `SponsorFactory` | `withoutLink()` |
| `ActivityFactory` | `withMedia($jumlah)` untuk 1 sampai 3 foto, dan `withLink()` |
| `ActivityMediaFactory` | `video()` |

Untuk demo pagination, pencarian, dan relasi, tambahkan data contoh dari factory:

```bash
php artisan db:seed --class=DemoSeeder
```

Seeder ini menambahkan 25 artikel terbit, 3 kiriman menunggu persetujuan, satu divisi berisi 12 pengurus, 3 sponsor, dan 3 kegiatan yang dihubungkan ke sponsor. `DemoSeeder` tidak ikut `DatabaseSeeder` supaya data asli tidak tercampur data palsu, dan hanya bisa dijalankan di laptop karena Faker tidak dipasang di hosting. Untuk kembali ke data asli saja, jalankan `php artisan migrate:fresh --seed`.

Factory juga dipakai di tes otomatis, misalnya `tests/Feature/FactoryTest.php`.

## Penanganan error

| Kode | Kapan muncul |
| --- | --- |
| 404 | Halaman atau artikel tidak ditemukan |
| 413 | File unggahan melebihi batas server |
| 419 | Kiriman formulir tidak bisa dipastikan berasal dari situs ini, misalnya dikirim dari situs lain. Laravel 13 menerima kiriman dari situs yang sama lewat header `Sec-Fetch-Site`, jadi di browser modern halaman ini jarang muncul |
| 429 | Terlalu banyak percobaan masuk admin atau kiriman artikel |
| 500 | Kesalahan di server. Detailnya tidak ditampilkan, tapi tercatat di `storage/logs/laravel.log` |

Kesalahan isian formulir ditampilkan di bawah kolomnya masing-masing, dalam bahasa Indonesia.

## Data

Profil, kegiatan, pengurus, dan sponsor memakai data asli komunitas. Artikel awal adalah data contoh dan ditandai di akhir tiap artikel.

## Penggunaan AI

Proyek ini dikembangkan dengan bantuan AI untuk mengefisienkan waktu pengerjaan. Ide dan konsep desain berasal dari pemilik proyek, dan pengerjaannya dibantu Claude. Claude Code membantu membuat struktur awal Laravel, menerapkan desain ke Blade, menulis artikel contoh, dan pengujian. Hasilnya ditinjau, dipelajari, dan dijalankan sendiri oleh pemilik proyek.
