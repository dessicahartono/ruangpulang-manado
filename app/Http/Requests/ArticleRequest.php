<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'author' => ['required', 'string', 'max:80'],
            'body' => ['required', 'string', 'max:20000'],
        ];
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
