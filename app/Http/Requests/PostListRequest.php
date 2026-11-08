<?php

namespace App\Http\Requests;

use App\Repositories\CommunityRepository;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Membaca pencarian dan urutan dari URL. Nilai yang tidak dikenal diabaikan
 * sehingga tautan yang rusak tetap menampilkan daftar artikel.
 */
class PostListRequest extends FormRequest
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

    public function sort(): string
    {
        return $this->requestedSort() ?? CommunityRepository::DEFAULT_POST_SORT;
    }

    /** Antrean dimulai dari kiriman terlama, kecuali admin memilih urutan lain. */
    public function pendingSort(): string
    {
        return $this->requestedSort() ?? 'terlama';
    }

    private function requestedSort(): ?string
    {
        $sort = $this->query('urut');

        return is_string($sort) && array_key_exists($sort, CommunityRepository::POST_SORTS) ? $sort : null;
    }
}
