<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    protected $fillable = ['division_id', 'full_name', 'nickname', 'instagram', 'photo', 'role', 'bio', 'bio_more', 'position'];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /** Format nama di halaman Pengurus, misalnya "Fathia Salama Jacob (Alma)". */
    protected function displayName(): Attribute
    {
        return Attribute::get(fn () => $this->full_name === $this->nickname
            ? $this->full_name
            : "{$this->full_name} ({$this->nickname})");
    }

    /** Data yang belum ada, supaya admin tahu apa yang perlu dilengkapi. */
    public function missingData(): array
    {
        return array_values(array_filter([
            $this->photo ? null : 'foto',
            $this->instagram ? null : 'Instagram',
        ]));
    }
}
