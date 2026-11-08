<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Kata sandi diketik sendiri oleh admin dan hanya hash-nya yang disimpan di .env.
Artisan::command('admin:password', function () {
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
    $line = "ADMIN_PASSWORD_HASH='".Hash::make($password)."'";
    $env = (string) file_get_contents($path);

    $env = preg_match('/^ADMIN_PASSWORD_HASH=.*$/m', $env)
        ? preg_replace_callback('/^ADMIN_PASSWORD_HASH=.*$/m', fn () => $line, $env)
        : rtrim($env)."\n".$line."\n";

    file_put_contents($path, $env);
    $this->info('Kata sandi admin sudah disimpan.');

    return 0;
})->purpose('Mengatur kata sandi halaman Kelola');
