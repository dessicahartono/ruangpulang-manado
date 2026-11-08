<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Satu-satunya pintu masuk data konten website.
 *
 * Artikel blog disimpan di database. Profil, kegiatan, dan pengurus masih dari
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

    public function activities(): Collection
    {
        return $this->load('activities');
    }

    public function portofolioTeam(): Collection
    {
        return $this->load('portofolioteam')->map(function (object $group) {
            $group->members = collect($group->members)->map(fn (array $member) => (object) $member);

            return $group;
        });
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

    /** Semua artikel untuk admin. Yang menunggu persetujuan selalu tampil paling atas. */
    public function managedPosts(string $search, string $sort, int $perPage): LengthAwarePaginator
    {
        $query = Post::query()->orderByRaw('status = ? desc', [Post::STATUS_PENDING]);

        return $this->sorted($query, $sort)
            ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search))
            ->paginate($perPage)
            ->withQueryString();
    }

    public function pendingPostCount(): int
    {
        return Post::where('status', Post::STATUS_PENDING)->count();
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

    private function sorted(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'terlama' => $query->orderBy('published_at'),
            'judul-az' => $query->orderByRaw('LOWER(title) asc'),
            'judul-za' => $query->orderByRaw('LOWER(title) desc'),
            default => $query->orderByDesc('published_at')->orderByDesc('id'),
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

    private function load(string $file): Collection
    {
        return collect(require resource_path("data/{$file}.php"))
            ->map(fn (array $item) => (object) $item);
    }
}
