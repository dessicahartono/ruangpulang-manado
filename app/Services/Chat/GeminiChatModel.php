<?php

namespace App\Services\Chat;

use App\Contracts\ChatModel;
use App\Exceptions\ChatModelException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class GeminiChatModel implements ChatModel
{
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct(private string $apiKey, private string $model, private int $maxTokens) {}

    public function respond(string $system, array $messages): ?string
    {
        try {
            $response = Http::withHeaders(['x-goog-api-key' => $this->apiKey])
                ->connectTimeout(10)
                ->timeout(30)
                ->post(self::BASE_URL."/{$this->model}:generateContent", [
                    'systemInstruction' => ['parts' => [['text' => $system]]],
                    'contents' => array_map(fn (array $message) => [
                        // Gemini memakai peran "model" untuk balasan AI.
                        'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                        'parts' => [['text' => $message['content']]],
                    ], $messages),
                    'generationConfig' => ['maxOutputTokens' => $this->maxTokens],
                ]);
        } catch (ConnectionException) {
            throw new ChatModelException;
        }

        if ($response->failed()) {
            throw new ChatModelException($response->status());
        }

        // Kandidat kosong berarti pesan atau jawabannya diblokir filter keamanan Gemini.
        $text = collect($response->json('candidates.0.content.parts', []))
            ->reject(fn (array $part) => $part['thought'] ?? false)
            ->pluck('text')
            ->implode('');

        // Balasan yang terpotong batas token dipotong lagi ke akhir kalimat terakhir yang utuh.
        if ($response->json('candidates.0.finishReason') === 'MAX_TOKENS') {
            $text = preg_replace('/[^.!?]*$/u', '', trim($text));
        }

        return trim($text) === '' ? null : trim($text);
    }

    public function isDemo(): bool
    {
        return false;
    }
}
