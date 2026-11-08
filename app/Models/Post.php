<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PUBLISHED = 'published';

    public const CATEGORIES = ['Bantuan', 'Bercerita', 'Healing', 'Komunitas'];

    protected $fillable = ['slug', 'title', 'author', 'category', 'status', 'excerpt', 'body', 'published_at'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    /** Isi artikel per blok. Blok dipisahkan baris kosong, dan awalan "## " menandai subjudul. */
    protected function content(): Attribute
    {
        return Attribute::get(fn () => preg_split('/\R\s*\R/', trim($this->body)));
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', self::STATUS_PUBLISHED);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
