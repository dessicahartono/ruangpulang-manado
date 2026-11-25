<?php

namespace App\Http\Requests;

use App\Support\ArticleHtml;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ArticleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'author' => ['required', 'string', 'max:80'],
            // Dari TinyMCE (admin) isinya HTML, jadi batasnya lebih longgar karena tag ikut terhitung.
            'body' => ['required', 'string', 'max:'.($this->fromRichEditor() ? 60000 : 20000)],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($this->fromRichEditor() && is_string($this->input('body')) && ArticleHtml::isEmpty(ArticleHtml::clean($this->input('body')))) {
                $validator->errors()->add('body', 'Isi Artikel wajib diisi.');
            }
        }];
    }

    /** Form Tulis dan Ubah di Kelola Blog memakai TinyMCE. Form Tulis Artikel untuk pengunjung tidak. */
    public function fromRichEditor(): bool
    {
        return $this->routeIs('manage.blog.*');
    }

    public function attributes(): array
    {
        return ['title' => 'Judul Artikel', 'author' => 'Nama Penulis', 'body' => 'Isi Artikel'];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute maksimal :max karakter.',
        ];
    }
}
