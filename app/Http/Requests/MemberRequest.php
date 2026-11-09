<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Foto dan Instagram wajib untuk pengurus baru. Saat diubah, keduanya boleh dilengkapi menyusul. */
class MemberRequest extends FormRequest
{
    public function rules(): array
    {
        $member = $this->route('member');
        $presence = $member ? 'nullable' : 'required';

        return [
            'division_id' => ['required', 'integer', Rule::exists('divisions', 'id')],
            'full_name' => [
                'required', 'string', 'max:120',
                Rule::unique('members')->where('division_id', $this->integer('division_id'))->ignore($member),
            ],
            'nickname' => ['required', 'string', 'max:40'],
            'instagram' => [$presence, 'string', 'max:200', 'regex:#^https://(www\.)?instagram\.com/[A-Za-z0-9._]+/?(\?\S*)?$#'],
            'photo' => [$presence, 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'division_id' => 'Divisi',
            'full_name' => 'Nama Lengkap',
            'nickname' => 'Nama Panggilan',
            'instagram' => 'Link Instagram',
            'photo' => 'Foto',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'division_id.exists' => 'Pilih divisi yang tersedia.',
            'full_name.unique' => 'Nama ini sudah ada di divisi tersebut.',
            'instagram.regex' => 'Link Instagram harus seperti https://www.instagram.com/namapengguna/.',
            'photo.image' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'photo.mimes' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
            'photo.uploaded' => 'Foto gagal diunggah. Pastikan ukurannya maksimal 2 MB.',
            'max' => ':attribute maksimal :max karakter.',
        ];
    }
}
