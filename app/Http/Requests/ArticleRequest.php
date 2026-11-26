<?php

namespace App\Http\Requests;

use App\Support\ArticleHtml;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ArticleRequest extends FormRequest
{
    /** Berlaku untuk admin dan pengunjung. Penghitung kata di bawah TinyMCE memakai cara hitung yang sama. */
    public const MAX_WORDS = 5000;

    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:150'],
            'author' => ['required', 'string', 'max:80'],
            // Dari TinyMCE isinya HTML, jadi batasnya lebih longgar karena tag ikut terhitung.
            'body' => ['required', 'string', 'max:'.($this->fromRichEditor() ? 60000 : 20000)],
        ];

        // Versi Inggris hanya ada di form admin. Kosong berarti diterjemahkan otomatis.
        if ($this->routeIs('manage.blog.*')) {
            $rules['title_en'] = ['nullable', 'string', 'max:150'];
            $rules['body_en'] = ['nullable', 'string', 'max:60000'];
        }

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if (! $this->fromRichEditor() || ! is_string($this->input('body'))) {
                return;
            }

            $body = ArticleHtml::clean($this->input('body'));

            if (ArticleHtml::isEmpty($body)) {
                $validator->errors()->add('body', __('Isi Artikel wajib diisi.'));
            } elseif (ArticleHtml::wordCount($body) > self::MAX_WORDS) {
                $validator->errors()->add('body', __('Isi Artikel maksimal :max kata. Sekarang :count kata.', [
                    'max' => self::MAX_WORDS,
                    'count' => ArticleHtml::wordCount($body),
                ]));
            }
        }];
    }

    /** Form Tulis dan Ubah di Kelola Blog dan form Tulis Artikel untuk pengunjung memakai TinyMCE. */
    public function fromRichEditor(): bool
    {
        return $this->routeIs('manage.blog.*', 'articles.*');
    }

    public function attributes(): array
    {
        return [
            'title' => __('Judul Artikel'),
            'author' => __('Nama Penulis'),
            'body' => __('Isi Artikel'),
            'title_en' => 'Judul Artikel (English)',
            'body_en' => 'Isi Artikel (English)',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => __(':attribute wajib diisi.'),
            'max' => __(':attribute maksimal :max karakter.'),
        ];
    }
}
