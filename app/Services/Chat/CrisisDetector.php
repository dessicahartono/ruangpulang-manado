<?php

namespace App\Services\Chat;

class CrisisDetector
{
    /** Frasa dibuat spesifik agar kalimat seperti "mati lampu" tidak ikut terdeteksi. */
    private const PATTERN = '/bunuh\s*diri|\bsuicid|akhiri\s+hidup|mengakhiri\s+hidup|akhiri\s+semua(nya)?\b|(?:ingin|pengen|pingin|kepengen|mau|mending|lebih\s+baik|lebih\s+baik\s+aku)\s+mati\b|(?:tidak|nggak|ngga|gak|ga|enggak)\s+(?:ingin|mau|pengen|sanggup|kuat)\s+(?:hidup|lanjut\s+hidup)|lelah\s+hidup|capek\s+hidup|cape\s+hidup|(?:menyakiti|melukai|nyakitin|sakitin|lukai|sakiti)\s+(?:diri|diriku)|self[\s-]*harm|kill\s+myself|want\s+to\s+die/iu';

    public function detect(string $message): bool
    {
        return preg_match(self::PATTERN, $message) === 1;
    }
}
