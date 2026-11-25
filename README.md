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

Syarat: PHP 8.4.1 atau lebih baru (dibutuhkan Symfony 8 pada `composer.lock`) dan Composer.

```bash
composer run setup
php artisan serve
```

Buka <http://localhost:8000>. Perintah setup juga membuat database SQLite dan mengisinya dengan data pengurus, blog, dan kegiatan. Untuk menjalankan pengujian, gunakan `php artisan test`.

**Halaman Kelola:** jalankan `php artisan admin:password` untuk membuat kata sandi admin.

## Deploy ke hosting tanpa SSH

Cocok untuk hosting bersama (cPanel) yang tidak punya akses SSH. Server cukup memakai **PHP 8.4.1 atau lebih baru**.

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

## Data

Profil, kegiatan, dan pengurus memakai data asli komunitas. Artikel awal adalah data contoh dan ditandai di akhir tiap artikel.

## Penggunaan AI

Proyek ini dikembangkan dengan bantuan AI untuk mengefisienkan waktu pengerjaan. Ide dan konsep desain berasal dari pemilik proyek, dan pengerjaannya dibantu Claude. Claude Code membantu membuat struktur awal Laravel, menerapkan desain ke Blade, menulis artikel contoh, dan pengujian. Hasilnya ditinjau, dipelajari, dan dijalankan sendiri oleh pemilik proyek.
