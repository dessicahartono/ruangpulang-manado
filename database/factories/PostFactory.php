<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Artikel blog contoh. Kalimatnya disusun dari tema komunitas, bukan lorem ipsum,
 * dan setiap artikel ditutup dengan tanda data contoh seperti artikel awal.
 *
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    private const TOPICS = [
        'Mengenali Rasa Lelah', 'Belajar Mendengarkan', 'Menjaga Batas Diri', 'Merawat Pertemanan',
        'Hari yang Berat', 'Menulis untuk Diri Sendiri', 'Bertemu Orang Baru', 'Istirahat Tanpa Rasa Bersalah',
        'Meminta Bantuan', 'Rutinitas Kecil yang Menenangkan', 'Pulang ke Diri Sendiri', 'Bercerita Tanpa Takut',
    ];

    private const SENTENCES = [
        'Kadang yang kita butuhkan bukan nasihat, tetapi seseorang yang mau mendengarkan sampai selesai.',
        'Tidak apa-apa kalau hari ini kamu hanya mampu melakukan hal-hal kecil.',
        'Perasaan yang diberi nama biasanya terasa lebih ringan untuk dihadapi.',
        'Teman yang baik tidak selalu punya jawaban, tetapi mereka tetap tinggal.',
        'Istirahat adalah bagian dari bertumbuh, bukan tanda menyerah.',
        'Menulis beberapa kalimat sebelum tidur bisa membantu pikiran menjadi lebih tenang.',
        'Bercerita kepada orang yang tepat membuat beban terasa dibagi.',
        'Kita tidak harus menghadapi semuanya sendirian.',
        'Langkah kecil yang dilakukan setiap hari lama-lama menjadi perubahan besar.',
        'Menjaga diri sendiri juga berarti tahu kapan harus berkata tidak.',
    ];

    public function definition(): array
    {
        $title = fake()->randomElement(self::TOPICS).' '.fake()->unique()->numberBetween(1, 9999);

        return [
            'slug' => Str::slug($title),
            'title' => $title,
            'author' => fake()->name(),
            'status' => Post::STATUS_PUBLISHED,
            'excerpt' => fake()->randomElement(self::SENTENCES),
            'body' => implode("\n\n", [
                $this->paragraph(),
                '## '.fake()->randomElement(['Mulai dari yang kecil', 'Kamu tidak sendiri', 'Pelan-pelan saja']),
                $this->paragraph(),
                $this->paragraph(),
                'Isi artikel ini adalah data contoh.',
            ]),
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /** Kiriman pengunjung yang menunggu persetujuan admin. */
    public function pending(): static
    {
        return $this->state(fn () => ['status' => Post::STATUS_PENDING, 'published_at' => null]);
    }

    private function paragraph(): string
    {
        return implode(' ', fake()->randomElements(self::SENTENCES, 3));
    }
}
