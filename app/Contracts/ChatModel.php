<?php

namespace App\Contracts;

interface ChatModel
{
    /**
     * Mengembalikan teks balasan, atau null kalau model menolak menjawab.
     *
     * @param  list<array{role: string, content: string}>  $messages
     */
    public function respond(string $system, array $messages): ?string;

    public function isDemo(): bool;
}
