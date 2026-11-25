<?php

namespace App\Models;

use Database\Factories\SponsorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sponsor extends Model
{
    /** @use HasFactory<SponsorFactory> */
    use HasFactory;

    protected $fillable = ['name', 'photo', 'description', 'link', 'position'];

    /** Kegiatan yang didukung sponsor ini. Kebalikan dari Activity::sponsors(). */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class)->withTimestamps()->orderBy('position')->orderBy('activities.id');
    }

    /** Data yang belum ada, supaya admin tahu apa yang perlu dilengkapi. */
    public function missingData(): array
    {
        return array_values(array_filter([
            $this->photo ? null : 'foto',
            $this->link ? null : 'link',
        ]));
    }
}
