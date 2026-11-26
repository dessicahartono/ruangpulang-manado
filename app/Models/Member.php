<?php

namespace App\Models;

use App\Models\Concerns\HasEnglish;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasEnglish;
    use HasFactory;

    protected $fillable = ['division_id', 'full_name', 'nickname', 'instagram', 'photo', 'role', 'role_en', 'bio', 'bio_en', 'bio_more', 'bio_more_en', 'position'];

    protected array $translatable = ['role', 'bio', 'bio_more'];

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
