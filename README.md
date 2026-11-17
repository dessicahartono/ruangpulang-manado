# Ruang Pulang

Website yang dibangun untuk komunitas sosial di Manado bernama **Ruang Pulang**

## Fitur

| URL | Isi |
| --- | --- |
| `/` | Beranda |
| `/tentang` | Sejarah, tujuan, visi, dan misi |
| `/kegiatan` | Jenis kegiatan komunitas |
| `/pengurus` | Pendiri dan pengurus (halaman Portfolio pada tugas) |
| `/blog` | Daftar artikel dengan pencarian, pengurutan, dan halaman detail |
| `/kontak` | Media sosial dan informasi bantuan darurat |
| `/bergabung` | Ketentuan keanggotaan dan tautan grup WhatsApp |
| `/tulis-artikel` | Kiriman artikel dari pengunjung, tampil setelah disetujui admin |
| `/kelola` | Menu admin, berisi Kelola Profil Pengurus (`/kelola/pengurus`), Kelola Blog (`/kelola/blog`) Kelola Kegiatan (`/kelola/kegiatan`) |

Chatbot AI "Teman Pulang" tersedia di semua halaman.

## Cara menjalankan

Syarat: PHP 8.4.1 atau lebih baru (dibutuhkan Symfony 8 pada `composer.lock`) dan Composer.

```bash
composer run setup
php artisan serve
```

Buka <http://localhost:8000>. Perintah setup juga membuat database SQLite dan mengisinya dengan data pengurus, blog, dan kegiatan. Untuk menjalankan pengujian, gunakan `php artisan test`.

**Halaman Kelola:** jalankan `php artisan admin:password` untuk membuat kata sandi admin.

**Chatbot AI:** tanpa API key, chatbot berjalan dalam mode demo. Untuk memakai AI, buat API key di [Google AI Studio](https://aistudio.google.com/apikey), lalu isi di `.env`. Jangan pernah meng-commit file `.env`.

```env
GEMINI_API_KEY=isi-dengan-key-kamu
GEMINI_MODEL=gemini-3.5-flash-lite
```

## Deploy ke hosting tanpa SSH

Cocok untuk hosting bersama (cPanel) yang tidak punya akses SSH. Server cukup memakai **PHP 8.4.1 atau lebih baru**.

**Pertama kali**

1. Di laptop, jalankan `deploy/build.sh`. Skrip ini menyiapkan salinan bersih di folder `build/`, memasang paket tanpa paket pengembangan, membuat `.env` produksi dan database baru berisi data awal, lalu meminta kata sandi admin. Hasilnya `build/ruangpulang-full.zip`.
2. Di cPanel, **File Manager**, unggah zip itu ke luar `public_html` (misalnya `/home/USERNAME/ruangpulang`) lalu **Extract**. Hapus zip-nya setelah itu, karena memuat `.env`.
3. Di **Domains**, arahkan **Document Root** domain ke `ruangpulang/public`.
4. Di **Select PHP Version**, pilih PHP 8.4 atau lebih dan aktifkan ekstensi `mbstring`, `pdo_sqlite`, `sqlite3`, `fileinfo`, `curl`, `xml`, `tokenizer`.
5. Beri izin tulis (775) pada `storage`, `bootstrap/cache`, `database`, dan `public/uploads` lewat File Manager.
6. Aktifkan SSL, lalu buka domain untuk menguji.

Domain lain: `APP_URL=https://domainmu.com deploy/build.sh`.

**Pembaruan berikutnya**

Jalankan `deploy/build.sh --update`, lalu unggah dan Extract `build/ruangpulang-update.zip` ke folder yang sama dan pilih timpa. Zip ini tidak memuat `.env`, database, dan foto unggahan, sehingga data di server aman. Jangan mengunggah ulang `database.sqlite`, karena akan menimpa artikel dan data yang sudah ada di server. Kalau ada migrasi baru, jalankan `php artisan migrate --force` di server lewat **Cron Jobs** cPanel (jadwalkan satu kali, tunggu berjalan, lalu hapus).

**Cadangan**

Salin `database/database.sqlite` dan folder `public/uploads` dari server secara berkala.

## Struktur

| Bagian | Lokasi |
| --- | --- |
| Routing | `routes/web.php` |
| Controller | `app/Http/Controllers` |
| View dan Layout | `resources/views/pages`, `resources/views/layouts/app.blade.php` |
| Blade Components | `resources/views/components` |
| Data artikel | Database, dengan data awal di `resources/data/posts.php` |
| Data pengurus | Database, dengan data awal di `resources/data/portofolioteam.php` |
| Data profil dan kegiatan | `resources/data` |

## Data

Profil, kegiatan, dan pengurus memakai data asli komunitas. Artikel awal adalah data contoh dan ditandai di akhir tiap artikel.

## Catatan chatbot

Chatbot ini bukan pengganti tenaga profesional, dan pendeteksian pesan krisisnya berbasis kata kunci sehingga bisa terlewat atau keliru. Pesan dikirim ke Google, tetapi tidak disimpan di database website. Riwayat hanya tersimpan di browser pengguna.

Website ini memakai paket gratis Gemini, sehingga hanya cocok untuk tugas, demo, dan uji coba. Sebelum dipakai pengguna sungguhan, proyek perlu pindah ke paket berbayar, dan perilaku chatbot perlu ditinjau oleh orang yang berkompeten di bidang kesehatan mental. Pada paket gratis, konten dapat dibaca peninjau manusia dan dipakai untuk mengembangkan produk Google, informasi pribadi atau sensitif tidak boleh dikirim, dan API tidak boleh dipakai pada aplikasi yang kemungkinan besar diakses pengguna di bawah 18 tahun. Lihat [ketentuan Gemini API](https://aistudio.google.com/docs/terms).

## Penggunaan AI

Proyek ini dikembangkan dengan bantuan AI untuk mengefisienkan waktu pengerjaan. Ide dan konsep desain berasal dari pemilik proyek, dan pengerjaannya dibantu Claude. Claude Code membantu membuat struktur awal Laravel, menerapkan desain ke Blade, menulis artikel contoh, dan pengujian. Hasilnya ditinjau, dipelajari, dan dijalankan sendiri oleh pemilik proyek.

Fitur chatbot memakai model AI Google Gemini saat `GEMINI_API_KEY` diisi.
