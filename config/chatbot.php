<?php

return [
    'api_key' => env('GEMINI_API_KEY'),
    'model' => env('GEMINI_MODEL', 'gemini-3.5-flash-lite'),

    // Kuota free tier dipakai bersama semua pengunjung, jadi batas per IP dibuat ketat.
    'max_tokens' => 2048,
    'max_message_length' => 1000,
    'max_history' => 20,
    'limit_per_minute' => 5,
    'limit_per_day' => 20,
];
