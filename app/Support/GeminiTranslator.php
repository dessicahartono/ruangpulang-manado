<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Menerjemahkan konten dari bahasa Indonesia ke bahasa Inggris lewat Gemini API.
 * Kalau API key kosong atau permintaan gagal, hasilnya array kosong dan halaman tetap memakai versi Indonesia.
 */
class GeminiTranslator
{
    private const INSTRUCTION = <<<'TEXT'
        You translate content for Ruang Pulang, a small mental health support community in Manado, Indonesia.
        Translate every value in the JSON object from Indonesian to natural, warm, and gentle English.
        Return a JSON object with exactly the same keys and the same structure (a string stays a string, a list stays a list).
        Keep HTML tags and attributes, line breaks, blank lines, "## " at the start of a line, and **bold** markers exactly as they are.
        Do not translate names of people, places, organizations, or the name "Ruang Pulang".
        TEXT;

    /**
     * @param  array<string, string|array<int, string>>  $texts
     * @return array<string, string|array<int, string>> hanya kunci yang berhasil diterjemahkan
     */
    public function translate(array $texts): array
    {
        $key = config('services.gemini.key');

        if (blank($key) || $texts === []) {
            return [];
        }

        try {
            $response = Http::withHeaders(['x-goog-api-key' => $key])
                ->timeout(60)
                ->post('https://generativelanguage.googleapis.com/v1beta/models/'.config('services.gemini.model').':generateContent', [
                    'systemInstruction' => ['parts' => [['text' => self::INSTRUCTION]]],
                    'contents' => [['role' => 'user', 'parts' => [['text' => json_encode($texts, JSON_UNESCAPED_UNICODE)]]]],
                    'generationConfig' => ['responseMimeType' => 'application/json', 'temperature' => 0.2],
                ]);
        } catch (ConnectionException $exception) {
            Log::warning('Terjemahan Gemini gagal tersambung.', ['error' => $exception->getMessage()]);

            return [];
        }

        $result = json_decode((string) $response->json('candidates.0.content.parts.0.text'), true);

        if (! $response->successful() || ! is_array($result)) {
            Log::warning('Terjemahan Gemini gagal.', ['status' => $response->status(), 'body' => $response->body()]);

            return [];
        }

        // Hanya kunci yang diminta dan bentuknya sama (teks tetap teks, daftar tetap daftar).
        return array_filter(
            array_intersect_key($result, $texts),
            fn ($value, string $field) => is_string($texts[$field]) ? is_string($value) : is_array($value),
            ARRAY_FILTER_USE_BOTH,
        );
    }
}
