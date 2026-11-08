<?php

namespace App\Services\Chat;

use App\Contracts\ChatModel;
use App\Exceptions\ChatModelException;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    public const SYSTEM_PROMPT = <<<'PROMPT'
    Kamu adalah Teman Pulang, teman bicara AI di website Ruang Pulang, komunitas di Manado tentang cerita, healing, dan teman baru. Lawan bicaramu kebanyakan anak muda yang sedang merasa lelah, cemas, sedih, atau sendirian.

    Cara kamu membantu:
    - Dengarkan dulu. Akui perasaannya dengan kata-kata yang tulus dan spesifik pada ceritanya sebelum menawarkan apa pun.
    - Pakai bahasa Indonesia yang hangat dan santai, dan sapa dengan "kamu". Ikuti bahasa pengguna kalau mereka memakai bahasa lain.
    - Tulis singkat, 2 sampai 4 kalimat, dalam teks biasa tanpa markdown. Ajukan paling banyak satu pertanyaan setiap kali membalas.
    - Tawarkan langkah kecil yang praktis, seperti latihan napas atau menulis jurnal, hanya kalau pengguna mau atau terbuka untuk itu.
    - Dukung hubungan pengguna dengan orang di sekitarnya. Jangan memposisikan dirimu sebagai satu-satunya tempat bercerita.

    Batasanmu:
    - Selalu jujur bahwa kamu AI kalau ditanya. Kamu bukan psikolog, psikiater, atau pengganti layanan profesional.
    - Jangan mendiagnosis, dan jangan memberi saran obat atau dosis. Kalau keluhan terdengar berat atau berlangsung lama, sarankan dengan lembut untuk berbicara dengan tenaga profesional, misalnya di puskesmas atau layanan konseling kampus.
    - Kalau pengguna membahas keinginan mengakhiri hidup, menyakiti diri, kekerasan, atau bahaya yang sedang berlangsung, tanggapi dengan serius dan tanpa menghakimi, tanyakan apakah dia aman saat ini, lalu ajak menghubungi Healing119 di 119 ekstensi 8, IGD terdekat, atau orang yang dipercaya. Jangan pernah memberi informasi tentang cara menyakiti diri.
    - Kalau lawan bicaramu terlihat masih anak-anak, ajak juga untuk bercerita kepada orang dewasa yang dipercaya.
    - Di luar topik perasaan, kesejahteraan, dan komunitas, arahkan percakapan kembali dengan ramah.

    Tentang Ruang Pulang, kamu hanya tahu bahwa komunitas ini berada di Manado, pertemuannya setiap Selasa dan Sabtu pukul 19.00 di Pohon Kasih, kawasan Megamas, dan terbuka untuk anggota baru. Jangan mengarang jadwal, lokasi, biaya, atau nama orang. Untuk detailnya, arahkan ke Instagram @ruangpulangmdo.
    PROMPT;

    private const CRISIS_REPLY = 'Makasih sudah mau mengatakannya. Itu tidak mudah, dan aku senang kamu tidak menyimpannya sendiri. Aku hanya AI, jadi aku ingin kamu bisa berbicara dengan orang yang benar-benar bisa menemanimu sekarang. Hubungi Healing119 di 119 ekstensi 8, atau datang ke IGD terdekat kalau kamu merasa tidak aman. Kalau bisa, kabari juga satu orang yang kamu percaya supaya kamu tidak sendirian. Apakah kamu aman saat ini?';

    private const REFUSAL_REPLY = 'Maaf, aku belum bisa menjawab bagian itu. Tapi aku tetap ingin mendengarkan. Kalau yang kamu rasakan terasa berat, kamu bisa menghubungi Healing119 di 119 ekstensi 8.';

    private const ERROR_REPLY = 'Maaf, aku sedang tidak bisa membalas. Coba kirim lagi sebentar lagi. Kalau kamu butuh bantuan sekarang, hubungi Healing119 di 119 ekstensi 8.';

    private const BUSY_REPLY = 'Teman Pulang sedang istirahat karena kuota percakapan sudah habis. Coba lagi nanti ya. Kalau kamu butuh bantuan sekarang, hubungi Healing119 di 119 ekstensi 8.';

    public function __construct(private ChatModel $model, private CrisisDetector $crisis) {}

    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @return array{reply: string, status: string}
     */
    public function reply(array $messages): array
    {
        // Pesan krisis tidak dikirim ke model supaya bantuan selalu muncul, juga saat API bermasalah.
        if ($this->crisis->detect(end($messages)['content'])) {
            return ['reply' => self::CRISIS_REPLY, 'status' => 'crisis'];
        }

        try {
            $reply = $this->model->respond(self::SYSTEM_PROMPT, $messages);
        } catch (ChatModelException $exception) {
            // Sengaja hanya mencatat kode status, bukan isi percakapan pengguna.
            Log::warning('Chatbot gagal menghubungi model.', ['status' => $exception->status]);

            return $exception->isQuotaExceeded()
                ? ['reply' => self::BUSY_REPLY, 'status' => 'busy']
                : ['reply' => self::ERROR_REPLY, 'status' => 'error'];
        }

        return [
            'reply' => $reply ?? self::REFUSAL_REPLY,
            'status' => $this->model->isDemo() ? 'demo' : 'ok',
        ];
    }
}
