<?php

namespace App\Models;

use App\Support\ArticleHtml;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PUBLISHED = 'published';

    public const FORMAT_TEXT = 'text';

    public const FORMAT_HTML = 'html';

    protected $fillable = ['slug', 'title', 'author', 'status', 'excerpt', 'body', 'format', 'published_at'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    /** Isi artikel per blok. Blok dipisahkan baris kosong, dan awalan "## " menandai subjudul. */
    protected function content(): Attribute
    {
        return Attribute::get(fn () => preg_split('/\R\s*\R/', trim($this->body)));
    }

    /** Artikel dari TinyMCE di halaman admin. Artikel lama dan kiriman pengunjung berupa teks. */
    public function isHtml(): bool
    {
        return $this->format === self::FORMAT_HTML;
    }

    /** HTML yang aman untuk ditampilkan. Sudah dibersihkan saat disimpan, dibersihkan sekali lagi untuk berjaga-jaga. */
    public function safeHtml(): string
    {
        return ArticleHtml::clean($this->body);
    }

    /** Isi untuk TinyMCE: artikel teks diubah dulu menjadi paragraf dan subjudul HTML. */
    public function editorHtml(): string
    {
        return $this->isHtml() ? $this->body : ArticleHtml::fromText($this->body);
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
