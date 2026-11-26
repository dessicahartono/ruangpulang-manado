<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Foto wajib untuk sponsor baru. Saat diubah, foto boleh dibiarkan. Link boleh kosong. */
class SponsorRequest extends FormRequest
{
    public function rules(): array
    {
        $sponsor = $this->route('sponsor');

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('sponsors')->ignore($sponsor)],
            'description' => ['required', 'string', 'max:600'],
            'description_en' => ['nullable', 'string', 'max:600'],
            'link' => ['nullable', 'string', 'max:300', 'url:http,https'],
            'photo' => [$sponsor ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Sponsor',
            'description' => 'Deskripsi',
            'description_en' => 'Deskripsi (English)',
            'link' => 'Link',
            'photo' => 'Foto',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'name.unique' => 'Sponsor dengan nama ini sudah ada.',
            'link.url' => 'Link harus diawali https://, misalnya https://www.contoh.com.',
            'photo.image' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'photo.mimes' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
            'photo.uploaded' => 'Foto gagal diunggah. Pastikan ukurannya maksimal 2 MB.',
            'max' => ':attribute maksimal :max karakter.',
        ];
    }
}
