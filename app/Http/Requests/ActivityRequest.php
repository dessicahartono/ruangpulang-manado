<?php

namespace App\Http\Requests;

use App\Models\Activity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Setiap kegiatan punya satu sampai tiga media. Saat diubah, admin bisa menghapus media lama
 * dan menambah yang baru, asalkan jumlah akhirnya tetap satu sampai tiga.
 */
class ActivityRequest extends FormRequest
{
    public const IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    public const VIDEO_MIMES = ['video/mp4', 'video/webm'];

    /** Dalam kilobyte. Batas upload PHP di server harus lebih besar dari VIDEO_MAX_KB. */
    public const IMAGE_MAX_KB = 2048;

    public const VIDEO_MAX_KB = 20480;

    /** Dicek di browser lewat site.js, karena hosting tanpa ffmpeg tidak bisa membaca durasi video. */
    public const VIDEO_MAX_SECONDS = 15;

    public function rules(): array
    {
        $activity = $this->route('activity');

        return [
            'title' => ['required', 'string', 'max:120', Rule::unique('activities')->ignore($activity)],
            'description' => ['required', 'string', 'max:1000'],
            // Alamat lengkap (https://...) atau halaman di situs ini (/blog).
            'link_url' => ['nullable', 'string', 'max:300', 'regex:#^(https?://\S+|/(?!/)\S*)$#'],
            'link_label' => ['nullable', 'string', 'max:60'],
            'media' => ['nullable', 'array', 'max:'.Activity::MAX_MEDIA],
            'media.*' => ['file', 'mimetypes:'.implode(',', [...self::IMAGE_MIMES, ...self::VIDEO_MIMES])],
            'sponsors' => ['nullable', 'array'],
            'sponsors.*' => ['integer', Rule::exists('sponsors', 'id')],
            'remove_media' => ['nullable', 'array'],
            'remove_media.*' => [
                'integer',
                Rule::exists('activity_media', 'id')->where('activity_id', $activity?->id ?? 0),
            ],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            foreach ($this->newMedia() as $index => $file) {
                $isVideo = in_array($file->getMimeType(), self::VIDEO_MIMES, true);
                $limit = $isVideo ? self::VIDEO_MAX_KB : self::IMAGE_MAX_KB;

                if ($file->getSize() > $limit * 1024) {
                    $validator->errors()->add("media.{$index}", $isVideo
                        ? 'Ukuran video maksimal 20 MB.'
                        : 'Ukuran foto maksimal 2 MB.');
                }
            }

            $activity = $this->route('activity');
            $kept = $activity ? $activity->media()->whereNotIn('id', $this->removedMediaIds())->count() : 0;
            $total = $kept + count($this->newMedia());

            if ($total < 1) {
                $validator->errors()->add('media', 'Tambahkan minimal satu foto atau video.');
            } elseif ($total > Activity::MAX_MEDIA) {
                $validator->errors()->add('media', 'Satu kegiatan maksimal punya '.Activity::MAX_MEDIA.' media. Hapus media lama dulu kalau mau menambah.');
            }
        }];
    }

    /** @return array<int, UploadedFile> */
    public function newMedia(): array
    {
        return array_filter((array) $this->file('media', []), fn ($file) => $file instanceof UploadedFile);
    }

    /** @return array<int, int> */
    public function removedMediaIds(): array
    {
        return array_map('intval', (array) $this->input('remove_media', []));
    }

    public function attributes(): array
    {
        return [
            'title' => 'Judul Kegiatan',
            'description' => 'Deskripsi',
            'link_url' => 'Link',
            'link_label' => 'Teks Link',
            'media' => 'Media',
            'media.*' => 'Media',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'title.unique' => 'Kegiatan dengan judul ini sudah ada.',
            'link_url.regex' => 'Link harus diawali https:// atau /, misalnya https://www.instagram.com/ruangpulang atau /blog.',
            'media.max' => 'Satu kegiatan maksimal punya '.Activity::MAX_MEDIA.' media.',
            'media.*.mimetypes' => 'Media harus berupa foto JPG, PNG, atau WEBP, atau video MP4 atau WEBM.',
            'media.*.uploaded' => 'Media gagal diunggah. Foto maksimal 2 MB dan video maksimal 20 MB.',
            'remove_media.*.exists' => 'Media yang mau dihapus tidak ditemukan.',
            'sponsors.*.exists' => 'Pilih sponsor yang tersedia.',
            'max' => ':attribute maksimal :max karakter.',
        ];
    }
}
