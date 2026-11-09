<?php

namespace App\Http\Requests;

use App\Repositories\CommunityRepository;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Membaca pencarian, divisi, dan urutan dari URL. Nilai yang tidak dikenal
 * diabaikan sehingga tautan yang rusak tetap menampilkan daftar pengurus.
 */
class MemberListRequest extends FormRequest
{
    public function rules(): array
    {
        return [];
    }

    public function search(): string
    {
        $search = $this->query('q');

        return is_string($search) ? trim($search) : '';
    }

    public function divisionId(): ?int
    {
        $division = $this->query('divisi');

        return is_string($division) && ctype_digit($division) ? (int) $division : null;
    }

    public function sort(): string
    {
        $sort = $this->query('urut');

        return is_string($sort) && array_key_exists($sort, CommunityRepository::MEMBER_SORTS)
            ? $sort
            : CommunityRepository::DEFAULT_MEMBER_SORT;
    }
}
