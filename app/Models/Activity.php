<?php

namespace App\Models;

use App\Models\Concerns\HasEnglish;
use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    /** @use HasFactory<ActivityFactory> */
    use HasEnglish;
    use HasFactory;

    public const MAX_MEDIA = 3;

    protected $fillable = ['title', 'title_en', 'description', 'description_en', 'link_url', 'link_label', 'link_label_en', 'position'];

    protected array $translatable = ['title', 'description', 'link_label'];

    public function media(): HasMany
    {
        return $this->hasMany(ActivityMedia::class)->orderBy('position')->orderBy('id');
    }

    /** Many-to-many lewat tabel activity_sponsor. */
    public function sponsors(): BelongsToMany
    {
        return $this->belongsToMany(Sponsor::class)->withTimestamps()->orderBy('position')->orderBy('sponsors.id');
    }

    /** Link ke situs lain dibuka di tab baru. Link ke halaman situs ini tidak. */
    public function linkIsExternal(): bool
    {
        return $this->link_url !== null && ! str_starts_with($this->link_url, '/');
    }

    /** Ringkasan isi media untuk daftar admin, misalnya "2 foto dan 1 video". */
    public function mediaSummary(): string
    {
        $counts = $this->media->countBy('type');

        return collect([
            $counts->get(ActivityMedia::TYPE_IMAGE) ? $counts->get(ActivityMedia::TYPE_IMAGE).' foto' : null,
            $counts->get(ActivityMedia::TYPE_VIDEO) ? $counts->get(ActivityMedia::TYPE_VIDEO).' video' : null,
        ])->filter()->implode(' dan ') ?: 'Belum ada media';
    }
}
