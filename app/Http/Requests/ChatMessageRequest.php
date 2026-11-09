<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ChatMessageRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'messages' => ['required', 'array', 'min:1', 'max:'.config('chatbot.max_history')],
            'messages.*.role' => ['required', Rule::in(['user', 'assistant'])],
            'messages.*.content' => ['required', 'string', 'max:'.config('chatbot.max_reply_length')],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'messages.*.content.max' => 'Pesannya terlalu panjang. Coba kirim maksimal :max karakter.',
            'messages.*.content.required' => 'Pesannya masih kosong.',
            'messages.max' => 'Percakapan ini sudah terlalu panjang. Silakan mulai percakapan baru.',
        ];
    }

    /** Percakapan harus dimulai dan diakhiri oleh pengguna. */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $messages = $this->input('messages');

            if ($messages[0]['role'] !== 'user' || end($messages)['role'] !== 'user') {
                $validator->errors()->add('messages', 'Format percakapan tidak valid.');

                return;
            }

            // Batas ketat hanya untuk pesan baru, karena riwayat juga berisi balasan AI.
            $limit = config('chatbot.max_message_length');

            if (mb_strlen(trim(end($messages)['content'])) > $limit) {
                $validator->errors()->add('messages', "Pesannya terlalu panjang. Coba kirim maksimal {$limit} karakter.");
            }
        }];
    }

    /** @return list<array{role: string, content: string}> */
    public function conversation(): array
    {
        return array_map(
            fn (array $message) => ['role' => $message['role'], 'content' => trim($message['content'])],
            $this->validated('messages'),
        );
    }
}
