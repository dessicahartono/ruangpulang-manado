<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Username dan kata sandi diketik sendiri oleh admin. Kata sandinya hanya disimpan sebagai hash di .env.
Artisan::command('admin:password', function () {
    $username = ltrim(trim((string) $this->ask('Username admin', config('community.admin_username') ?: null)), '@');

    if (preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username) !== 1) {
        $this->error('Username hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung, 3 sampai 50 karakter. Tidak ada yang diubah.');

        return 1;
    }

    $password = (string) $this->secret('Kata sandi admin baru (minimal 12 karakter)');

    if (strlen($password) < 12) {
        $this->error('Kata sandi terlalu pendek. Tidak ada yang diubah.');

        return 1;
    }

    if ($password !== $this->secret('Ulangi kata sandi')) {
        $this->error('Kata sandi tidak sama. Tidak ada yang diubah.');

        return 1;
    }

    $path = app()->environmentFilePath();
    $env = (string) file_get_contents($path);

    foreach (['ADMIN_USERNAME' => $username, 'ADMIN_PASSWORD_HASH' => "'".Hash::make($password)."'"] as $key => $value) {
        $line = $key.'='.$value;
        $env = preg_match("/^{$key}=.*$/m", $env)
            ? preg_replace_callback("/^{$key}=.*$/m", fn () => $line, $env)
            : rtrim($env)."\n".$line."\n";
    }

    file_put_contents($path, $env);
    $this->info("Username {$username} dan kata sandi admin sudah disimpan.");

    return 0;
})->purpose('Mengatur kata sandi halaman Kelola');

// Verifikasi dua langkah: membuat kunci, menampilkan QR untuk di-scan aplikasi authenticator,
// lalu menyimpannya ke .env setelah satu kode dari aplikasi terbukti cocok.
Artisan::command('admin:2fa {--disable : Matikan verifikasi dua langkah}', function () {
    $path = app()->environmentFilePath();
    $env = (string) file_get_contents($path);
    $hasSecret = preg_match('/^ADMIN_TOTP_SECRET=.+$/m', $env) === 1;

    $save = function (string $value) use ($path, $env) {
        $line = 'ADMIN_TOTP_SECRET='.$value;
        $updated = preg_match('/^ADMIN_TOTP_SECRET=.*$/m', $env)
            ? preg_replace_callback('/^ADMIN_TOTP_SECRET=.*$/m', fn () => $line, $env)
            : rtrim($env)."\n".$line."\n";
        file_put_contents($path, $updated);
    };

    if ($this->option('disable')) {
        if (! $hasSecret || ! $this->confirm('Matikan verifikasi dua langkah? Login akan kembali cukup dengan kata sandi.')) {
            $this->info('Tidak ada yang diubah.');

            return 0;
        }

        $save('');
        $this->info('Verifikasi dua langkah sudah dimatikan.');

        return 0;
    }

    if ($hasSecret && ! $this->confirm('Verifikasi dua langkah sudah aktif. Buat kunci baru? Kode di aplikasi yang lama tidak akan berlaku lagi.')) {
        $this->info('Tidak ada yang diubah.');

        return 0;
    }

    $google2fa = app(PragmaRX\Google2FA\Google2FA::class);
    $secret = $google2fa->generateSecretKey();
    $uri = $google2fa->getQRCodeUrl(config('community.name'), 'admin', $secret);

    // QR digambar dengan karakter blok: satu karakter = dua baris modul. Hitam di atas putih supaya bisa di-scan
    // di terminal bertema gelap maupun terang.
    $matrix = BaconQrCode\Encoder\Encoder::encode($uri, BaconQrCode\Common\ErrorCorrectionLevel::L())->getMatrix();
    $size = $matrix->getWidth();
    $quiet = 2;
    $dark = fn (int $x, int $y) => $x >= 0 && $y >= 0 && $x < $size && $y < $size && $matrix->get($x, $y) === 1;

    $this->newLine();
    for ($y = -$quiet; $y < $size + $quiet; $y += 2) {
        $row = '';
        for ($x = -$quiet; $x < $size + $quiet; $x++) {
            $row .= match ([$dark($x, $y), $dark($x, $y + 1)]) {
                [true, true] => '█',
                [true, false] => '▀',
                [false, true] => '▄',
                default => ' ',
            };
        }
        $this->line("\e[30;47m".$row."\e[0m");
    }

    $this->newLine();
    $this->line('Scan QR di atas dengan Google Authenticator, Microsoft Authenticator, atau aplikasi sejenis.');
    $this->line('Kalau tidak bisa scan, pilih "Masukkan kunci penyiapan" di aplikasi, lalu ketik kunci ini:');
    $this->info('  '.trim(chunk_split($secret, 4, ' ')));
    $this->newLine();

    for ($attempt = 1; $attempt <= 3; $attempt++) {
        $code = preg_replace('/\s+/', '', (string) $this->ask('Masukkan kode 6 angka dari aplikasi untuk memastikan'));

        if (preg_match('/^\d{6}$/', $code) === 1 && $google2fa->verifyKey($secret, $code, 1)) {
            $save($secret);
            $this->info('Verifikasi dua langkah aktif. Mulai sekarang, login admin butuh kata sandi dan kode dari aplikasi.');
            $this->line('Untuk hosting, salin baris ADMIN_TOTP_SECRET dari .env ini ke .env di server.');

            return 0;
        }

        $this->error('Kode tidak cocok. Pastikan jam HP sudah otomatis, lalu coba kode terbaru.');
    }

    $this->error('Tiga kali gagal. Tidak ada yang diubah, jadi login masih seperti sebelumnya.');

    return 1;
})->purpose('Mengatur verifikasi dua langkah halaman Kelola');

// Mengisi versi bahasa Inggris untuk konten yang sudah ada di database, misalnya data hasil seeder.
// Yang sudah punya versi Inggris dilewati, kecuali memakai --ulang.
Artisan::command('konten:terjemahkan {--ulang : Terjemahkan ulang semuanya, termasuk yang sudah punya versi Inggris}', function () {
    if (blank(config('services.gemini.key'))) {
        $this->error('GEMINI_API_KEY di .env masih kosong. Tidak ada yang diterjemahkan.');

        return 1;
    }

    $items = collect([
        App\Models\Post::published()->get(),
        App\Models\Activity::all(),
        App\Models\Sponsor::all(),
        App\Models\Division::all(),
        App\Models\Member::all(),
    ])->flatten();

    $failed = 0;

    foreach ($items as $item) {
        if ($this->option('ulang')) {
            $item->forgetEnglish();
        }

        if ($item->untranslatedFields() === []) {
            continue;
        }

        $name = class_basename($item).' #'.$item->id;
        $item->translateToEnglish();

        if ($item->untranslatedFields() === []) {
            $item->save();
            $this->info("{$name} selesai.");
        } else {
            $failed++;
            $this->warn("{$name} gagal, lihat storage/logs/laravel.log.");
        }

        // Paket gratis Gemini membatasi jumlah permintaan per menit.
        Illuminate\Support\Sleep::for(5)->seconds();
    }

    $this->line($failed === 0 ? 'Semua konten sudah punya versi Inggris.' : "{$failed} konten gagal. Jalankan lagi nanti untuk mencoba ulang yang gagal.");

    return $failed === 0 ? 0 : 1;
})->purpose('Menerjemahkan konten database ke bahasa Inggris dengan Gemini');
