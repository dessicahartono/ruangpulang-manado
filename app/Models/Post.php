<?php

namespace App\Models;

use App\Models\Concerns\HasEnglish;
use App\Support\ArticleHtml;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasEnglish { translateToEnglish as private translateFields; }
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PUBLISHED = 'published';

    public const FORMAT_TEXT = 'text';

    public const FORMAT_HTML = 'html';

    protected $fillable = ['slug', 'title', 'title_en', 'author', 'status', 'excerpt', 'body', 'body_en', 'format', 'published_at'];

    /** Ringkasan tidak diterjemahkan sendiri, tetapi diambil dari isi versi Inggrisnya. */
    protected array $translatable = ['title', 'body'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    /** Isi artikel per blok. Blok dipisahkan baris kosong, dan awalan "## " menandai subjudul. */
    protected function content(): Attribute
    {
        return Attribute::get(fn () => preg_split('/\R\s*\R/', trim($this->local('body'))));
    }

    /** Artikel dari TinyMCE di halaman admin. Artikel lama dan kiriman pengunjung berupa teks. */
    public function isHtml(): bool
    {
        return $this->format === self::FORMAT_HTML;
    }

    /** HTML yang aman untuk ditampilkan. Sudah dibersihkan saat disimpan, dibersihkan sekali lagi untuk berjaga-jaga. */
    public function safeHtml(): string
    {
        return ArticleHtml::clean($this->local('body'));
    }

    /** Isi untuk TinyMCE: artikel teks diubah dulu menjadi paragraf dan subjudul HTML. */
    public function editorHtml(string $field = 'body'): string
    {
        $body = (string) $this->{$field};

        return $this->isHtml() ? $body : ArticleHtml::fromText($body);
    }

    /** Paragraf pertama, dipotong 160 karakter. Subjudul ("## " atau h2/h3) dilewati. */
    public static function excerptFrom(string $body, string $format): string
    {
        $firstParagraph = $format === self::FORMAT_HTML
            ? ArticleHtml::firstParagraph($body)
            : collect(preg_split('/\R\s*\R/', trim($body)))->first(fn (string $block) => ! str_starts_with($block, '## '), '');

        return Str::limit(Str::squish($firstParagraph), 160);
    }

    /** Isi HTML versi Inggris ikut dibersihkan, baik hasil Gemini maupun ketikan admin. */
    public function translateToEnglish(): void
    {
        $this->translateFields();

        if ($this->isHtml() && filled($this->body_en)) {
            $this->body_en = ArticleHtml::clean($this->body_en);
        }

        $this->excerpt_en = filled($this->body_en) ? self::excerptFrom($this->body_en, $this->format) : null;
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
