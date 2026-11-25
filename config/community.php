<?php

// Identitas komunitas yang dipakai di seluruh halaman.
return [
    'name' => 'Ruang Pulang',
    'tagline' => 'Didengar, Dipahami, dan Tumbuh Bersama',
    'description' => 'Komunitas kecil di Manado untuk bercerita, merawat diri, dan menemukan teman baru.',
    'location' => 'Manado, North Sulawesi, Indonesia',
    'instagram' => 'https://www.instagram.com/ruangpulangmdo/',
    'tiktok' => 'https://www.tiktok.com/@ruangpulangmdo',
    'whatsapp_group' => 'https://chat.whatsapp.com/GUaKX1JmuqEE7jIhdoQ3TP',

    // Username admin, boleh diketik dengan atau tanpa @. Isi lewat: php artisan admin:password
    'admin_username' => env('ADMIN_USERNAME'),

    // Hash kata sandi admin. Isi lewat: php artisan admin:password
    'admin_password_hash' => env('ADMIN_PASSWORD_HASH'),

    // Kunci verifikasi dua langkah (aplikasi authenticator). Isi lewat: php artisan admin:2fa
    // Kosong berarti login cukup dengan kata sandi.
    'admin_totp_secret' => env('ADMIN_TOTP_SECRET'),

    'credit' => [
        'name' => 'Dessica Suhaedi Hartono',
        'url' => 'https://www.linkedin.com/in/dessica-suhaedi-hartono-822b24283/',
    ],

    'crisis' => [
        'name' => 'Healing119',
        'phone_label' => '119 ext. 8',
        'phone_href' => 'tel:119',
        'url' => 'https://www.healing119.id/',
    ],
];
