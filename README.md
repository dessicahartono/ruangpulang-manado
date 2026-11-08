# Ruang Pulang

Website komunitas **Ruang Pulang**, komunitas di Manado untuk bercerita, merawat diri, dan menemukan teman baru. Website ini dibangun dengan Laravel untuk memenuhi tugas pengembangan web, dan dilengkapi chatbot AI untuk teman bercerita.

Tidak ada akun pengguna, jadi pengunjung bisa langsung memakai seluruh halaman. Hanya halaman Kelola yang dikunci dengan satu kata sandi admin.

## Halaman

| URL | Isi | Kebutuhan tugas |
| --- | --- | --- |
| `/` | Beranda | Home |
| `/tentang` | Sejarah, tujuan, visi, dan misi | About |
| `/kegiatan` | Delapan jenis kegiatan | Tambahan |
| `/pengurus` | Lima pendiri dan seluruh pengurus | Portfolio |
| `/blog` dan `/blog/{slug}` | Daftar artikel dengan pencarian, urutan, dan halaman, serta detail artikel | Blog |
| `/kontak` | Media sosial dan informasi bantuan darurat. Menu dan footer membuka versi popup | Contact |
| `/bergabung` | Ketentuan keanggotaan dan tautan grup WhatsApp | Tambahan |
| `/tulis-artikel` | Form kiriman artikel dari pengunjung, tampil setelah disetujui admin | Tambahan |
| `/kelola` | Khusus admin: menyetujui kiriman, serta menulis, mengubah, dan menghapus artikel | Tambahan |

Chatbot AI "Teman Pulang" tersedia sebagai tombol melayang di semua halaman.

## Cara menjalankan

Syarat: PHP 8.3 atau lebih baru dan Composer. Node.js tidak diperlukan.

```bash
composer run setup
php artisan serve
```

`composer run setup` juga membuat database SQLite dan mengisinya dengan artikel contoh. Buka <http://localhost:8000>. Untuk menjalankan pengujian, gunakan `php artisan test`.

### Mengaktifkan halaman Kelola

```bash
php artisan admin:password
```

Perintah ini meminta kata sandi (minimal 12 karakter) dan hanya menyimpan hash-nya di `.env`. Selama belum diatur, tidak ada yang bisa masuk ke `/kelola`.

### Mengaktifkan chatbot AI

Tanpa API key, chatbot berjalan dalam **mode demo** dengan jawaban yang sudah disiapkan. Untuk memakai AI, buat API key gratis di [Google AI Studio](https://aistudio.google.com/apikey), lalu isi di file `.env`:

```env
GEMINI_API_KEY=isi-dengan-key-kamu
GEMINI_MODEL=gemini-3.5-flash-lite
```

Jangan pernah meng-commit file `.env`. Kuota aktual akunmu bisa dilihat di Google AI Studio. Kalau kuota habis, chatbot menampilkan pesan istirahat beserta informasi layanan bantuan.

### Batasan Gemini free tier

Website ini memakai paket gratis, sehingga hanya cocok untuk tugas, demo, dan uji coba. Sebelum dibuka untuk pengguna sungguhan, proyek perlu pindah ke paket berbayar karena [ketentuan Gemini API](https://aistudio.google.com/docs/terms) menyatakan:

- Konten pada paket gratis dipakai untuk mengembangkan produk Google, dan peninjau manusia dapat membacanya.
- Informasi sensitif, rahasia, atau pribadi tidak boleh dikirim ke paket gratis.
- API tidak boleh dipakai pada aplikasi yang ditujukan untuk, atau kemungkinan besar diakses oleh, pengguna di bawah 18 tahun.

## Implementasi fitur Laravel

| Fitur | Lokasi | Penggunaan |
| --- | --- | --- |
| Routing | `routes/web.php` | Route bernama untuk setiap halaman, parameter `{slug}` untuk detail blog, dan rate limit pada chatbot |
| Controller | `app/Http/Controllers` | Mengambil data dari repository lalu mengirimkannya ke View. Form divalidasi dengan Form Request di `app/Http/Requests` |
| Database | `database/migrations`, `app/Models/Post.php` | Tabel artikel dengan status `pending` dan `published`, diisi awal oleh `PostSeeder` |
| Middleware | `app/Http/Middleware/EnsureAdmin.php` | Melindungi seluruh route Kelola |
| View | `resources/views/pages` | Tampilan setiap halaman, terpisah dari logika |
| Blade | seluruh `resources/views` | `{{ }}`, `@foreach`, `@forelse`, `@if`, `@isset`, `@class`, `@error`, dan `@csrf` |
| Layout | `resources/views/layouts/app.blade.php` | Struktur utama dengan `@yield`, dipakai lewat `@extends` |
| Components | `resources/views/components` | Antara lain `navbar`, `footer`, `page-hero`, `sky`, `chat-widget`, `contact-modal`, `activity-card`, `member-card`, `post-result`, `post-toolbar`, `pager`, dan `form-field` |

### Alur data

```
Route → Controller → CommunityRepository → database (artikel)
                                         → resources/data/*.php (profil, kegiatan, pengurus)
             ↓
      View + Components
```

Artikel disimpan di database, sedangkan profil, kegiatan, dan pengurus di `resources/data`, dan identitas komunitas di `config/community.php`. Seluruh pengambilan konten melewati `App\Repositories\CommunityRepository`, sehingga Controller tidak perlu tahu dari mana datanya berasal.

### Tampilan

Desain berasal dari prototipe pemilik proyek. Prototipe memiliki file terpisah untuk desktop dan HP, sedangkan di sini setiap halaman menjadi satu halaman responsif. `public/css/rp.css` dan `rp-ext.css` adalah CSS asli desain, dan `public/css/app.css` berisi penyesuaian untuk Laravel.

### Alur artikel

1. Pengunjung mengirim artikel lewat `/tulis-artikel`. Artikel tersimpan dengan status `pending` dan belum bisa dilihat publik.
2. Admin masuk ke `/kelola`, membuka kiriman di bagian Menunggu Persetujuan lewat tombol Tinjau Artikel, lalu menyetujui, mengubah, atau menghapusnya.
3. Artikel yang disetujui mendapat tanggal publikasi dan tampil di `/blog`.

Pengiriman dibatasi 5 kali per jam per IP dan memakai kolom jebakan untuk bot. Percobaan masuk admin dibatasi 5 kali per menit per IP. Isi artikel selalu di-escape oleh Blade.

### Cara kerja chatbot

1. `public/js/chat.js` mengirim percakapan ke `POST /teman-cerita/pesan`. Riwayat disimpan di `localStorage` browser perangkat itu saja dan bisa dihapus dengan tombol Hapus di panel chat.
2. `ChatMessageRequest` memvalidasi panjang dan format pesan, dan route membatasi jumlah permintaan per IP.
3. `ChatbotService` memeriksa pesan dengan `CrisisDetector`. Pesan yang menunjukkan krisis langsung dijawab dengan arahan ke layanan bantuan tanpa dikirim ke AI.
4. Pesan lainnya dikirim ke Gemini oleh `GeminiChatModel` memakai HTTP Client Laravel, atau dijawab `DemoChatModel` kalau API key belum ada.

Website tidak menyimpan percakapan di database atau server dan tidak menulis isinya ke log, tetapi pesan tetap dikirim ke Google. Karena riwayat tersimpan di perangkat, siapa pun yang memakai browser yang sama bisa membacanya sampai dihapus. Chatbot ini bukan pengganti tenaga profesional. Pendeteksi krisis berbasis kata kunci sehingga bisa terlewat atau keliru, dan perilaku chatbot perlu ditinjau oleh orang yang berkompeten di bidang kesehatan mental sebelum dipakai publik.

## Data

Profil, kegiatan, dan pengurus memakai data asli dari pemilik proyek. Yang masih berupa contoh:

- Isi artikel awal (`resources/data/posts.php`), yang ditandai "data contoh" di akhir artikel. Setelah masuk database, artikel ini bisa diubah atau dihapus dari `/kelola`
- Foto beberapa pengurus yang belum tersedia, yang ditampilkan sebagai inisial (`resources/data/portofolioteam.php`)

## Penggunaan AI

Proyek ini dikembangkan dengan bantuan Claude Code (Anthropic) untuk menyusun struktur Laravel, menerapkan desain ke Blade, kode, artikel contoh, dan pengujian. Desain antarmuka dibuat pemilik proyek bersama Claude. Hasilnya ditinjau, dipelajari, dan dijalankan sendiri oleh pemilik proyek.

Fitur chatbot "Teman Pulang" memakai model AI Google Gemini saat `GEMINI_API_KEY` diisi.
