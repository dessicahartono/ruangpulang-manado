# Ruang Pulang

Website **Ruang Pulang**, komunitas di Manado untuk bercerita, merawat diri, dan menemukan teman baru. Dibangun dengan Laravel untuk memenuhi tugas pengembangan web.

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
| `/kelola` | Halaman admin untuk mengelola artikel |

Chatbot AI "Teman Pulang" tersedia di semua halaman.

## Cara menjalankan

Syarat: PHP 8.3 atau lebih baru dan Composer.

```bash
composer run setup
php artisan serve
```

Buka <http://localhost:8000>. Perintah setup juga membuat database SQLite dan mengisinya dengan artikel contoh. Untuk menjalankan pengujian, gunakan `php artisan test`.

**Halaman Kelola:** jalankan `php artisan admin:password` untuk membuat kata sandi admin.

**Chatbot AI:** tanpa API key, chatbot berjalan dalam mode demo. Untuk memakai AI, buat API key di [Google AI Studio](https://aistudio.google.com/apikey), lalu isi di `.env`. Jangan pernah meng-commit file `.env`.

```env
GEMINI_API_KEY=isi-dengan-key-kamu
GEMINI_MODEL=gemini-3.5-flash-lite
```

## Struktur

| Bagian | Lokasi |
| --- | --- |
| Routing | `routes/web.php` |
| Controller | `app/Http/Controllers` |
| View dan Layout | `resources/views/pages`, `resources/views/layouts/app.blade.php` |
| Blade Components | `resources/views/components` |
| Data artikel | Database, dengan data awal di `resources/data/posts.php` |
| Data profil, kegiatan, dan pengurus | `resources/data` |

## Data

Profil, kegiatan, dan pengurus memakai data asli komunitas. Artikel awal adalah data contoh dan ditandai di akhir tiap artikel.

## Catatan chatbot

Chatbot ini bukan pengganti tenaga profesional, dan pendeteksian pesan krisisnya berbasis kata kunci sehingga bisa terlewat atau keliru. Pesan dikirim ke Google, tetapi tidak disimpan di database website. Riwayat hanya tersimpan di browser pengguna.

Website ini memakai paket gratis Gemini, sehingga hanya cocok untuk tugas, demo, dan uji coba. Sebelum dipakai pengguna sungguhan, proyek perlu pindah ke paket berbayar, dan perilaku chatbot perlu ditinjau oleh orang yang berkompeten di bidang kesehatan mental. Pada paket gratis, konten dapat dibaca peninjau manusia dan dipakai untuk mengembangkan produk Google, informasi pribadi atau sensitif tidak boleh dikirim, dan API tidak boleh dipakai pada aplikasi yang kemungkinan besar diakses pengguna di bawah 18 tahun. Lihat [ketentuan Gemini API](https://aistudio.google.com/docs/terms).

## Penggunaan AI

Proyek ini dikembangkan dengan bantuan AI untuk mengefisienkan waktu pengerjaan. Ide dan konsep desain berasal dari pemilik proyek, dan pengerjaannya dibantu Claude. Claude Code membantu membuat struktur awal Laravel, menerapkan desain ke Blade, menulis kode fitur, artikel contoh, dan pengujian. Hasilnya ditinjau, dipelajari, dan dijalankan sendiri oleh pemilik proyek.

Fitur chatbot memakai model AI Google Gemini saat `GEMINI_API_KEY` diisi.
