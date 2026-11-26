<?php

namespace App\Repositories;

use App\Models\Activity;
use App\Models\ActivityMedia;
use App\Models\Division;
use App\Models\Member;
use App\Models\Post;
use App\Models\Sponsor;
use App\Support\ArticleHtml;
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
 * Artikel blog, pengurus, sponsor, dan kegiatan disimpan di database. Profil masih dari
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

    private const SPONSOR_UPLOAD_PATH = 'uploads/sponsor/';

    /** Media awal ada di images/kegiatan dan ikut tersimpan di Git, jadi tidak pernah dihapus. */
    private const ACTIVITY_UPLOAD_PATH = 'uploads/kegiatan/';

    public function activities(): Collection
    {
        return Activity::with(['media', 'sponsors'])->orderBy('position')->orderBy('id')->get();
    }

    /** @param  array<int, UploadedFile>  $files */
    public function createActivity(array $data, array $files): Activity
    {
        $activity = new Activity([
            ...$this->activityAttributes($data),
            'position' => (Activity::max('position') ?? -1) + 1,
        ]);
        $activity->translateToEnglish();
        $activity->save();

        $activity->sponsors()->sync($data['sponsors'] ?? []);
        $this->addActivityMedia($activity, $files);

        return $activity;
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @param  array<int, int>  $removeIds
     */
    public function updateActivity(Activity $activity, array $data, array $files, array $removeIds): void
    {
        $activity->fill($this->activityAttributes($data));
        $activity->translateToEnglish();
        $activity->save();
        $activity->sponsors()->sync($data['sponsors'] ?? []);

        $activity->media()->whereIn('id', $removeIds)->get()->each(function (ActivityMedia $media) {
            $media->delete();
            $this->deleteActivityFile($media);
        });

        $this->addActivityMedia($activity, $files);
    }

    public function deleteActivity(Activity $activity): void
    {
        $media = $activity->media()->get();

        $activity->delete();
        $media->each(fn (ActivityMedia $item) => $this->deleteActivityFile($item));
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

    public function sponsors(): Collection
    {
        return Sponsor::with('activities')->orderBy('position')->orderBy('id')->get();
    }

    public function createSponsor(array $data): Sponsor
    {
        $sponsor = new Sponsor([
            ...Arr::except($data, 'photo'),
            'photo' => $this->storeSponsorPhoto($data['photo']),
            'position' => (Sponsor::max('position') ?? -1) + 1,
        ]);
        $sponsor->translateToEnglish();
        $sponsor->save();

        return $sponsor;
    }

    public function updateSponsor(Sponsor $sponsor, array $data): void
    {
        $attributes = Arr::except($data, 'photo');

        if (isset($data['photo'])) {
            $this->deleteSponsorPhoto($sponsor);
            $attributes['photo'] = $this->storeSponsorPhoto($data['photo']);
        }

        $sponsor->fill($attributes);
        $sponsor->translateToEnglish();
        $sponsor->save();
    }

    public function deleteSponsor(Sponsor $sponsor): void
    {
        $sponsor->delete();
        $this->deleteSponsorPhoto($sponsor);
    }

    /** Profil versi Inggris ada di file terpisah, karena isinya juga dari file, bukan database. */
    public function profile(): object
    {
        return (object) require resource_path(app()->isLocale('en') ? 'data/profile.en.php' : 'data/profile.php');
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
        // Kiriman pengunjung tetap berupa teks, karena form publik tidak memakai TinyMCE.
        return Post::create([...$this->attributesFrom($data, Post::FORMAT_TEXT), 'status' => Post::STATUS_PENDING]);
    }

    public function publishPost(array $data): Post
    {
        $post = new Post([
            ...$this->attributesFrom($data, Post::FORMAT_HTML),
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
        $post->translateToEnglish();
        $post->save();

        return $post;
    }

    /**
     * Slug tidak berubah supaya tautan lama ke artikel ini tetap berfungsi.
     * Admin mengubah artikel lewat TinyMCE, jadi artikel yang diubah selalu tersimpan sebagai HTML.
     */
    public function updatePost(Post $post, array $data): void
    {
        $post->fill(Arr::except($this->attributesFrom($data, Post::FORMAT_HTML), 'slug'));
        $post->translateToEnglish();
        $post->save();
    }

    public function approvePost(Post $post): void
    {
        // Artikel yang sudah terbit dilewati supaya tanggal publikasinya tidak berubah.
        // Kiriman pengunjung baru diterjemahkan di sini, supaya kiriman yang ditolak tidak memakai kuota Gemini.
        if ($post->isPending()) {
            $post->fill(['status' => Post::STATUS_PUBLISHED, 'published_at' => now()]);
            $post->translateToEnglish();
            $post->save();
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
        $query->whereAny(['title', 'title_en', 'author', 'excerpt', 'body', 'body_en'], 'like', "%{$search}%");
    }

    private function attributesFrom(array $data, string $format): array
    {
        $body = $format === Post::FORMAT_HTML ? ArticleHtml::clean($data['body']) : $data['body'];

        return [
            'slug' => $this->uniqueSlug($data['title']),
            'title' => $data['title'],
            'author' => $data['author'],
            'body' => $body,
            'body_en' => $data['body_en'] ?? null,
            'title_en' => $data['title_en'] ?? null,
            'format' => $format,
            'excerpt' => Post::excerptFrom($body, $format),
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

    private function storeSponsorPhoto(UploadedFile $photo): string
    {
        return self::SPONSOR_UPLOAD_PATH.$photo->storeAs('', $photo->hashName(), 'sponsors');
    }

    private function deleteSponsorPhoto(Sponsor $sponsor): void
    {
        if ($sponsor->photo && str_starts_with($sponsor->photo, self::SPONSOR_UPLOAD_PATH)) {
            Storage::disk('sponsors')->delete(basename($sponsor->photo));
        }
    }

    /**
     * Teks link boleh kosong. Kalau link diisi tanpa teks, dipakai "Kunjungi Laman" untuk halaman situs ini
     * dan "Kunjungi Situs" untuk situs lain. Tanpa link, teksnya dibuang.
     */
    private function activityAttributes(array $data): array
    {
        $url = filled($data['link_url'] ?? null) ? trim($data['link_url']) : null;

        return [
            'title' => $data['title'],
            'title_en' => $data['title_en'] ?? null,
            'description' => $data['description'],
            'description_en' => $data['description_en'] ?? null,
            'link_url' => $url,
            // Tanpa link, teks link versi Inggris ikut dikosongkan oleh translateToEnglish().
            'link_label_en' => $data['link_label_en'] ?? null,
            'link_label' => match (true) {
                $url === null => null,
                filled($data['link_label'] ?? null) => trim($data['link_label']),
                str_starts_with($url, '/') => 'Kunjungi Laman',
                default => 'Kunjungi Situs',
            },
        ];
    }

    /** Media baru masuk ke urutan terakhir. Alt foto memakai judul kegiatan. */
    private function addActivityMedia(Activity $activity, array $files): void
    {
        $position = ($activity->media()->max('position') ?? -1) + 1;

        foreach ($files as $file) {
            $isVideo = str_starts_with((string) $file->getMimeType(), 'video/');

            $activity->media()->create([
                'path' => self::ACTIVITY_UPLOAD_PATH.$file->storeAs('', $file->hashName(), 'activities'),
                'type' => $isVideo ? ActivityMedia::TYPE_VIDEO : ActivityMedia::TYPE_IMAGE,
                'alt' => ($isVideo ? 'Video kegiatan ' : 'Foto kegiatan ').$activity->title,
                'position' => $position++,
            ]);
        }

        $activity->unsetRelation('media');
    }

    private function deleteActivityFile(ActivityMedia $media): void
    {
        if (str_starts_with($media->path, self::ACTIVITY_UPLOAD_PATH)) {
            Storage::disk('activities')->delete(basename($media->path));
        }
    }
}
