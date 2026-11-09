<?php

return [
    'api_key' => env('GEMINI_API_KEY'),
    'model' => env('GEMINI_MODEL', 'gemini-3.5-flash-lite'),

    // Kuota free tier dipakai bersama semua pengunjung, jadi batas per IP dibuat ketat.
    'max_tokens' => 150,
    // 1000 karakter kira-kira 200 token.
    'max_message_length' => 1000,
    // Lebih longgar dari pesan pengguna karena balasan AI ikut dikirim ulang sebagai riwayat.
    'max_reply_length' => 1200,
    'max_history' => 6,
    'limit_per_minute' => 5,
    'limit_per_day' => 20,
];
