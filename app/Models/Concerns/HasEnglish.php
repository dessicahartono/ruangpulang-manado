<?php

namespace App\Models\Concerns;

use App\Support\GeminiTranslator;

/**
 * Konten yang punya versi bahasa Inggris di kolom berakhiran _en.
 * Model yang memakainya menyebut kolom aslinya di properti $translatable.
 */
trait HasEnglish
{
    /** Teks sesuai bahasa yang dipilih pengunjung. Kalau versi Inggris belum ada, tampil versi Indonesia. */
    public function local(string $field): mixed
    {
        $english = $this->{$field.'_en'};

        return app()->isLocale('en') && filled($english) ? $english : $this->{$field};
    }

    /**
     * Kolom yang perlu diterjemahkan: versi Inggrisnya kosong, atau teks Indonesianya berubah
     * sementara versi Inggrisnya tidak ikut diubah admin.
     *
     * @return array<int, string>
     */
    public function untranslatedFields(): array
    {
        return array_values(array_filter($this->translatable, fn (string $field) => filled($this->{$field})
            && (blank($this->{$field.'_en'}) || ($this->isDirty($field) && ! $this->isDirty($field.'_en')))));
    }

    /** Mengosongkan semua versi Inggris, supaya diterjemahkan ulang dari awal. */
    public function forgetEnglish(): void
    {
        foreach ($this->translatable as $field) {
            $this->{$field.'_en'} = null;
        }
    }

    /** Dipanggil sebelum disimpan. Kalau Gemini gagal, versi Inggrisnya dikosongkan supaya yang tampil versi Indonesia. */
    public function translateToEnglish(): void
    {
        foreach ($this->translatable as $field) {
            if (blank($this->{$field})) {
                $this->{$field.'_en'} = null;
            }
        }

        $fields = $this->untranslatedFields();

        if ($fields === []) {
            return;
        }

        $translated = app(GeminiTranslator::class)->translate(
            collect($fields)->mapWithKeys(fn (string $field) => [$field => $this->{$field}])->all(),
        );

        foreach ($fields as $field) {
            $this->{$field.'_en'} = $translated[$field] ?? null;
        }
    }
}
