<?php

namespace App\Services\Chat;

use App\Contracts\ChatModel;

/** Jawaban yang sudah disiapkan, dipakai saat GEMINI_API_KEY belum diisi. */
class DemoChatModel implements ChatModel
{
    private const REPLIES = [
        'cemas|panik|takut|khawatir|deg' => 'Kedengarannya rasa cemasnya lagi cukup kuat ya. Mau coba tarik napas pelan sebentar bareng aku, atau kamu lebih ingin cerita dulu apa yang bikin kepikiran?',
        'sedih|nangis|menangis|kecewa|hancur' => 'Makasih sudah mau cerita. Sedih seperti itu berat untuk dibawa sendirian. Kalau kamu mau, ceritakan apa yang paling terasa hari ini.',
        'capek|lelah|burnout|cape|tugas|kerja|kuliah' => 'Kamu kedengarannya sudah lama menahan lelah. Wajar kalau rasanya berat. Bagian mana yang paling menguras tenagamu belakangan ini?',
        'sendiri|kesepian|teman|sepi' => 'Merasa sendirian itu tidak enak, apalagi kalau sudah berlangsung lama. Sejak kapan kamu mulai merasa seperti ini?',
        'gabung|komunitas|ruang pulang|kegiatan|jadwal' => 'Ruang Pulang ada di Manado dan terbuka untuk anggota baru. Pertemuannya setiap Selasa dan Sabtu pukul 19.00 di Pohon Kasih, kawasan Megamas. Info terbaru ada di Instagram @ruangpulangmdo.',
    ];

    public function respond(string $system, array $messages): ?string
    {
        $last = mb_strtolower(end($messages)['content']);

        foreach (self::REPLIES as $keywords => $reply) {
            if (preg_match('/'.$keywords.'/u', $last)) {
                return $reply;
            }
        }

        return 'Aku dengar kamu. Boleh ceritakan lebih banyak? Aku di sini untuk mendengarkan, tanpa menghakimi.';
    }

    public function isDemo(): bool
    {
        return true;
    }
}
