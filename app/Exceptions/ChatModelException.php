<?php

namespace App\Exceptions;

use RuntimeException;

/** Model AI gagal dihubungi. Sengaja tidak menyimpan isi percakapan pengguna. */
class ChatModelException extends RuntimeException
{
    public function __construct(public readonly ?int $status = null)
    {
        parent::__construct('Model AI gagal dihubungi.');
    }

    /** Kuota free tier habis atau terlalu banyak permintaan dalam satu menit. */
    public function isQuotaExceeded(): bool
    {
        return $this->status === 429;
    }
}
