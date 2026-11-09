<?php

namespace App\Repositories;

use App\Models\Division;
use App\Models\Member;
use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator as LengthAwarePaginatorInstance;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Satu-satunya pintu masuk data konten website.
 *
 * Artikel blog dan pengurus disimpan di database. Profil dan kegiatan masih dari
 * file PHP di resources/data karena hanya berubah kalau komunitasnya berubah.
 */
class CommunityRepository
{
    public const DEFAULT_POST_SORT = 'terbaru';

    public const POST_SORTS = [
        'terbaru' => 'Tanggal Terbaru',
        'terlama' => 'Tanggal Terlama',
        'judul-az' => 'Judul A sampai Z',
        'judul-za' => 'Judul Z sampai A',
    ];

    public const DEFAULT_MEMBER_SORT = 'susunan';

    public const MEMBER_SORTS = [
        'susunan' => 'Susunan Halaman Pengurus',
        'nama-az' => 'Nama A sampai Z',
        'nama-za' => 'Nama Z sampai A',
        'terbaru' => 'Terakhir Ditambahkan',
    ];

    /** Lokasi foto unggahan admin di folder public. Foto awal ada di images/pengurus. */
    private const MEMBER_UPLOAD_PATH = 'uploads/pengurus/';

    public function activities(): Collection
    {
        return $this->load('activities');
    }

    /** Seluruh divisi beserta pengurusnya, sesuai susunan halaman Pengurus. */
    public function portofolioTeam(): Collection
    {
        return Division::with('members')->orderBy('position')->get();
    }

    public function divisions(): Collection
    {
        return Division::orderBy('position')->get();
    }

    public function members(string $search, ?int $divisionId, string $sort, int $perPage): LengthAwarePaginator
    {
        return $this->sortedMembers(Member::with('division'), $sort)
            ->when($divisionId !== null, fn (Builder $query) => $query->where('division_id', $divisionId))
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $matches) => $matches
                ->whereAny(['full_name', 'nickname'], 'like', "%{$search}%")
                ->orWhereHas('division', fn (Builder $division) => $division->where('name', 'like', "%{$search}%"))))
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createMember(array $data): Member
    {
        return Member::create([
            ...Arr::except($data, 'photo'),
            'photo' => $this->storeMemberPhoto($data['photo']),
            'position' => $this->nextMemberPosition($data['division_id']),
        ]);
    }

    public function updateMember(Member $member, array $data): void
    {
        $attributes = Arr::except($data, 'photo');

        // Pindah divisi berarti masuk ke urutan terakhir di divisi barunya.
        if ((int) $data['division_id'] !== $member->division_id) {
            $attributes['position'] = $this->nextMemberPosition($data['division_id']);
        }

        if (isset($data['photo'])) {
            $this->deleteMemberPhoto($member);
            $attributes['photo'] = $this->storeMemberPhoto($data['photo']);
        }

        $member->update($attributes);
    }

    public function deleteMember(Member $member): void
    {
        $member->delete();
        $this->deleteMemberPhoto($member);
    }

    public function profile(): object
    {
        return (object) require resource_path('data/profile.php');
    }

    /** Artikel yang boleh dilihat pengunjung. */
    public function publishedPosts(string $search, string $sort, int $perPage): LengthAwarePaginator
    {
        return $this->sorted(Post::published(), $sort)
            ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search))
            ->paginate($perPage)
            ->withQueryString();
    }

    /** Kiriman yang menunggu persetujuan admin, diurutkan menurut tanggal kirim. */
    public function pendingPosts(string $search, string $sort): Collection
    {
        return $this->sorted(Post::where('status', Post::STATUS_PENDING), $sort, 'created_at')
            ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search))
            ->get();
    }

    /**
     * Artikel terbit untuk halaman Kelola, dengan sebagian tempat di halaman 1
     * dipakai oleh artikel yang menunggu.
     *
     * Paginator Laravel menganggap semua halaman sama panjang. Karena itu tempat
     * yang dipakai ikut dihitung dalam total supaya jumlah halamannya tetap benar,
     * sehingga jumlah artikel sebenarnya adalah total() dikurangi $reserved.
     */
    public function publishedPostsWithReservedSlots(string $search, string $sort, int $perPage, int $reserved): LengthAwarePaginator
    {
        $query = $this->sorted(Post::published(), $sort)
            ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search));

        $total = (clone $query)->count();
        $page = Paginator::resolveCurrentPage();

        $posts = $page === 1
            ? $query->limit($perPage - $reserved)->get()
            : $query->offset(($page - 1) * $perPage - $reserved)->limit($perPage)->get();

        return (new LengthAwarePaginatorInstance($posts, $total + $reserved, $perPage, $page, ['path' => Paginator::resolveCurrentPath()]))
            ->withQueryString();
    }

    public function findPublishedPost(string $slug): ?Post
    {
        return Post::published()->firstWhere('slug', $slug);
    }

    public function findPost(string $slug): ?Post
    {
        return Post::firstWhere('slug', $slug);
    }

    /** Kiriman pengunjung. Tidak tampil di blog sampai disetujui admin. */
    public function submitPost(array $data): Post
    {
        return Post::create([...$this->attributesFrom($data), 'status' => Post::STATUS_PENDING]);
    }

    public function publishPost(array $data): Post
    {
        return Post::create([
            ...$this->attributesFrom($data),
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }

    /** Slug tidak berubah supaya tautan lama ke artikel ini tetap berfungsi. */
    public function updatePost(Post $post, array $data): void
    {
        $post->update([
            'title' => $data['title'],
            'author' => $data['author'],
            'body' => $data['body'],
            'excerpt' => $this->excerptFrom($data['body']),
        ]);
    }

    public function approvePost(Post $post): void
    {
        // Artikel yang sudah terbit dilewati supaya tanggal publikasinya tidak berubah.
        if ($post->isPending()) {
            $post->update(['status' => Post::STATUS_PUBLISHED, 'published_at' => now()]);
        }
    }

    public function deletePost(Post $post): void
    {
        $post->delete();
    }

    private function sorted(Builder $query, string $sort, string $dateColumn = 'published_at'): Builder
    {
        return match ($sort) {
            'terlama' => $query->orderBy($dateColumn)->orderBy('id'),
            'judul-az' => $query->orderByRaw('LOWER(title) asc'),
            'judul-za' => $query->orderByRaw('LOWER(title) desc'),
            default => $query->orderByDesc($dateColumn)->orderByDesc('id'),
        };
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->whereAny(['title', 'author', 'excerpt', 'body'], 'like', "%{$search}%");
    }

    private function attributesFrom(array $data): array
    {
        return [
            'slug' => $this->uniqueSlug($data['title']),
            'title' => $data['title'],
            'author' => $data['author'],
            'body' => $data['body'],
            'excerpt' => $this->excerptFrom($data['body']),
        ];
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;

        for ($number = 2; Post::where('slug', $slug)->exists(); $number++) {
            $slug = "{$base}-{$number}";
        }

        return $slug;
    }

    private function excerptFrom(string $body): string
    {
        $firstParagraph = collect(preg_split('/\R\s*\R/', trim($body)))
            ->first(fn (string $block) => ! str_starts_with($block, '## '), '');

        return Str::limit(Str::squish($firstParagraph), 160);
    }

    private function sortedMembers(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'nama-az' => $query->orderByRaw('LOWER(full_name) asc')->orderBy('id'),
            'nama-za' => $query->orderByRaw('LOWER(full_name) desc')->orderBy('id'),
            'terbaru' => $query->orderByDesc('id'),
            default => $query
                ->orderBy(Division::select('position')->whereColumn('divisions.id', 'members.division_id'))
                ->orderBy('position')
                ->orderBy('id'),
        };
    }

    private function nextMemberPosition(int|string $divisionId): int
    {
        return (Member::where('division_id', $divisionId)->max('position') ?? -1) + 1;
    }

    private function storeMemberPhoto(UploadedFile $photo): string
    {
        return self::MEMBER_UPLOAD_PATH.$photo->storeAs('', $photo->hashName(), 'members');
    }

    /** Foto awal di images/pengurus ikut tersimpan di Git dan bisa dipakai beberapa baris, jadi tidak dihapus. */
    private function deleteMemberPhoto(Member $member): void
    {
        if ($member->photo && str_starts_with($member->photo, self::MEMBER_UPLOAD_PATH)) {
            Storage::disk('members')->delete(basename($member->photo));
        }
    }

    private function load(string $file): Collection
    {
        return collect(require resource_path("data/{$file}.php"))
            ->map(fn (array $item) => (object) $item);
    }
}
