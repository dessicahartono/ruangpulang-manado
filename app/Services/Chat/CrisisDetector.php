<?php

namespace App\Services\Chat;

class CrisisDetector
{
    /** Frasa dibuat spesifik agar kalimat seperti "mati lampu" tidak ikut terdeteksi. */
    private const PATTERN = '/bunuh\s*diri|\bsuicid|akhiri\s+hidup|mengakhiri\s+hidup|akhiri\s+semua(nya)?\b|(?:ingin|pengen|pingin|kepengen|mau|mending|lebih\s+baik|lebih\s+baik\s+aku)\s+mati\b|(?:tidak|nggak|ngga|gak|ga|enggak)\s+(?:ingin|mau|pengen|sanggup|kuat)\s+(?:hidup|lanjut\s+hidup)|lelah\s+hidup|capek\s+hidup|cape\s+hidup|(?:menyakiti|melukai|nyakitin|sakitin|lukai|sakiti)\s+(?:diri|diriku)|self[\s-]*harm|kill\s+myself|want\s+to\s+die'
        // Kalimat tersirat. Lebih baik sesekali keliru menawarkan bantuan daripada melewatkannya.
        .'|lebih\s+(?:baik|bahagia|tenang)\s+(?:kalau\s+|kalo\s+|jika\s+)?tanpa\s+(?:aku|saya|diriku|gue|gw)\b'
        .'|(?:kalau|kalo|jika)\s+(?:aku|saya|gue|gw)\s+(?:sudah\s+|udah\s+)?(?:tidak|nggak|ngga|gak|ga|enggak)\s+ada(?:\s+lagi)?\s*(?:[.,!?]|$)'
        .'|(?:tidak|nggak|ngga|gak|ga|enggak)\s+ada\s+(?:gunanya|artinya)\s+(?:lagi\s+)?(?:aku|saya|gue|gw|hidup)\b'
        .'|(?:aku|saya|gue|gw|hidupku|hidup\s+ini)\s+(?:tidak|nggak|ngga|gak|ga|enggak)\s+ada\s+(?:gunanya|artinya)\b'
        .'|(?:tidak|nggak|ngga|gak|ga|enggak)\s+ada\s+alasan\s+(?:buat|untuk)\s+(?:bertahan|hidup|lanjut)\b'
        .'|(?:ingin|pengen|pingin|kepengen|mau)\s+(?:hilang|menghilang)\s+(?:aja|saja|selamanya|dari\s+dunia)\b/iu';

    public function detect(string $message): bool
    {
        return preg_match(self::PATTERN, $message) === 1;
    }
}
