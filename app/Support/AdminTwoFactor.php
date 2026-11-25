<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use PragmaRX\Google2FA\Google2FA;

/**
 * Verifikasi dua langkah untuk admin dengan aplikasi authenticator (TOTP).
 *
 * Kunci rahasianya disimpan di .env (ADMIN_TOTP_SECRET), sama seperti hash kata sandi.
 * Kode berganti setiap 30 detik. Kode yang sudah dipakai tidak bisa dipakai lagi.
 */
class AdminTwoFactor
{
    /** Kode dari 30 detik sebelum dan sesudahnya tetap diterima, supaya jam HP yang sedikit meleset tidak jadi masalah. */
    private const WINDOW = 1;

    private const LAST_USED_KEY = 'admin-2fa-last-timestamp';

    public function __construct(private Google2FA $google2fa) {}

    public function enabled(): bool
    {
        return filled($this->secret());
    }

    /** Benar kalau kodenya cocok dan lebih baru dari kode terakhir yang dipakai. */
    public function verify(string $code): bool
    {
        if (! $this->enabled() || preg_match('/^\d{6}$/', $code) !== 1) {
            return false;
        }

        // Nilai awal 0, bukan null: dengan null library hanya mengembalikan true, bukan waktu kodenya,
        // sehingga kode yang sama bisa dipakai ulang.
        $timestamp = $this->google2fa->verifyKeyNewer($this->secret(), $code, (int) Cache::get(self::LAST_USED_KEY, 0), self::WINDOW);

        if (! is_int($timestamp)) {
            return false;
        }

        Cache::forever(self::LAST_USED_KEY, $timestamp);

        return true;
    }

    private function secret(): ?string
    {
        return config('community.admin_totp_secret');
    }
}
