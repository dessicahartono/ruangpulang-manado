<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    public const MAX_MEDIA = 3;

    protected $fillable = ['title', 'description', 'link_url', 'link_label', 'position'];

    public function media(): HasMany
    {
        return $this->hasMany(ActivityMedia::class)->orderBy('position')->orderBy('id');
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
