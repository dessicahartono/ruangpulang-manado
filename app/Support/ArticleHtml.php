<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Isi artikel dari TinyMCE berupa HTML, jadi harus dibersihkan sebelum disimpan dan ditampilkan.
 * Hanya tag format teks yang aman yang diizinkan. Script, style, atribut event (onclick, dan
 * sejenisnya), dan tautan javascript: dibuang.
 */
class ArticleHtml
{
    /** Harus sama dengan pilihan di toolbar TinyMCE (resources/views/pages/manage/form.blade.php). */
    public const ALLOWED = 'p,br,h2,h3,strong,em,u,s,ul,ol,li,blockquote,a[href|title]';

    private static ?HTMLPurifier $purifier = null;

    public static function clean(string $html): string
    {
        return trim(self::purifier()->purify($html));
    }

    /** Artikel lama berformat teks diubah ke HTML supaya bisa dibuka di TinyMCE. */
    public static function fromText(string $text): string
    {
        return collect(preg_split('/\R\s*\R/', trim($text)))
            ->filter(fn (string $block) => trim($block) !== '')
            ->map(fn (string $block) => str_starts_with($block, '## ')
                ? '<h2>'.e(trim(substr($block, 3))).'</h2>'
                : '<p>'.nl2br(e(trim($block)), false).'</p>')
            ->implode("\n");
    }

    /** Teks polos untuk ringkasan artikel: paragraf pertama, tanpa tag. */
    public static function firstParagraph(string $html): string
    {
        $text = preg_match('/<p>(.*?)<\/p>/s', $html, $match) === 1 ? $match[1] : $html;

        return trim(html_entity_decode(strip_tags(str_replace('<br>', ' ', $text)), ENT_QUOTES | ENT_HTML5));
    }

    /** Jumlah kata, dihitung seperti penghitung kata di TinyMCE: tag diganti spasi supaya kata antarparagraf tidak menyatu. */
    public static function wordCount(string $html): int
    {
        $text = html_entity_decode(preg_replace('/<[^>]*>/', ' ', $html), ENT_QUOTES | ENT_HTML5);

        return count(preg_split('/[\s\x{A0}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY));
    }

    /** Benar kalau HTML-nya tidak berisi teks apa pun, misalnya "<p></p>" dari editor yang kosong. */
    public static function isEmpty(string $html): bool
    {
        return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5), " \t\n\r\0\x0B\u{A0}") === '';
    }

    private static function purifier(): HTMLPurifier
    {
        if (self::$purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', self::ALLOWED);
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
            // Tautan ke situs lain dibuka di tab baru, lengkap dengan rel="noopener noreferrer".
            $config->set('HTML.TargetBlank', true);
            $config->set('AutoFormat.RemoveEmpty', true);
            // Tanpa cache file, jadi tidak perlu folder tambahan yang bisa ditulis di hosting.
            $config->set('Cache.DefinitionImpl', null);
            self::$purifier = new HTMLPurifier($config);
        }

        return self::$purifier;
    }
}
