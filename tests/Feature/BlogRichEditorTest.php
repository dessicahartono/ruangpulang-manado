<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogRichEditorTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function asAdmin(): static
    {
        return $this->withSession(['is_admin' => true]);
    }

    public function test_admin_forms_load_tinymce_but_the_public_form_does_not(): void
    {
        $post = Post::firstWhere('slug', 'kapan-sebaiknya-mencari-bantuan-profesional');

        $this->asAdmin()->get('/kelola/blog/tambah')->assertOk()->assertSee('tinymce@7.6.0', false)->assertSee('data-rich-editor', false);
        $this->asAdmin()->get("/kelola/blog/{$post->slug}/ubah")->assertOk()->assertSee('tinymce@7.6.0', false);
        $this->get('/tulis-artikel')->assertOk()->assertDontSee('tinymce', false)->assertDontSee('data-rich-editor', false);
    }

    public function test_admin_html_is_cleaned_before_it_is_saved(): void
    {
        $this->asAdmin()->post('/kelola/blog', [
            'title' => 'Artikel Format',
            'author' => 'Admin',
            'body' => '<h2>Subjudul</h2><p onclick="alert(1)">Halo <strong>teman</strong> <a href="javascript:alert(1)">bahaya</a></p>'
                .'<script>alert(1)</script><img src="x" onerror="alert(1)"><ul><li>satu</li></ul><blockquote>kutipan</blockquote>',
        ])->assertRedirect(route('manage.blog.show', 'artikel-format'));

        $post = Post::firstWhere('title', 'Artikel Format');
        $this->assertTrue($post->isHtml());
        $this->assertStringContainsString('<h2>Subjudul</h2>', $post->body);
        $this->assertStringContainsString('<strong>teman</strong>', $post->body);
        $this->assertStringContainsString('<ul><li>satu</li></ul>', $post->body);
        $this->assertStringContainsString('<blockquote>kutipan</blockquote>', $post->body);
        foreach (['<script', 'onclick', 'onerror', '<img', 'javascript:'] as $danger) {
            $this->assertStringNotContainsString($danger, $post->body);
        }
        $this->assertSame('Halo teman bahaya', $post->excerpt);
    }

    public function test_html_articles_are_shown_with_their_formatting(): void
    {
        $this->asAdmin()->post('/kelola/blog', [
            'title' => 'Artikel Bergaya',
            'author' => 'Admin',
            'body' => '<p>Teks <em>miring</em> dan <a href="https://www.contoh.com">tautan</a>.</p>',
        ]);
        $post = Post::firstWhere('title', 'Artikel Bergaya');

        $this->get("/blog/{$post->slug}")
            ->assertOk()
            ->assertSee('<em>miring</em>', false)
            ->assertSee('href="https://www.contoh.com"', false)
            ->assertSee('rel="noreferrer noopener"', false);
    }

    public function test_old_text_articles_open_in_tinymce_as_paragraphs_and_headings(): void
    {
        $post = Post::firstWhere('slug', 'datang-sendirian-ke-komunitas-baru');
        $this->assertFalse($post->isHtml());

        $html = $post->editorHtml();
        $this->assertStringStartsWith('<p>', $html);
        $this->assertStringContainsString('<h2>Tetapkan target kecil</h2>', $html);
        $this->assertStringNotContainsString('## ', $html);

        $this->asAdmin()->get("/kelola/blog/{$post->slug}/ubah")->assertSee(e(substr($html, 0, 40)), false);
    }

    public function test_editing_turns_an_article_into_html_and_keeps_its_slug(): void
    {
        $post = Post::firstWhere('slug', 'kapan-sebaiknya-mencari-bantuan-profesional');

        $this->asAdmin()->put("/kelola/blog/{$post->slug}", [
            'title' => 'Judul Baru',
            'author' => $post->author,
            'body' => '<p>Isi <strong>baru</strong>.</p>',
        ])->assertSessionHasNoErrors();

        $post->refresh();
        $this->assertSame('kapan-sebaiknya-mencari-bantuan-profesional', $post->slug);
        $this->assertTrue($post->isHtml());
        $this->assertSame('<p>Isi <strong>baru</strong>.</p>', $post->body);
        $this->assertSame('Isi baru.', $post->excerpt);
    }

    public function test_an_empty_editor_is_rejected(): void
    {
        $this->asAdmin()->post('/kelola/blog', ['title' => 'Kosong', 'author' => 'Admin', 'body' => '<p>&nbsp;</p>'])
            ->assertSessionHasErrors(['body' => 'Isi Artikel wajib diisi.']);
        $this->asAdmin()->post('/kelola/blog', ['title' => 'Kosong', 'author' => 'Admin', 'body' => '<script>alert(1)</script>'])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseMissing('posts', ['title' => 'Kosong']);
    }

    public function test_visitor_submissions_stay_plain_text(): void
    {
        $this->post('/tulis-artikel', [
            'title' => 'Kiriman Pengunjung',
            'author' => 'Tamu',
            'body' => "Paragraf satu <b>bukan tebal</b>.\n\nParagraf dua.",
        ]);

        $post = Post::firstWhere('title', 'Kiriman Pengunjung');
        $this->assertFalse($post->isHtml());
        $this->assertStringContainsString('<b>bukan tebal</b>', $post->body);

        $this->asAdmin()->get("/kelola/blog/{$post->slug}/tinjau")
            ->assertSee('&lt;b&gt;bukan tebal&lt;/b&gt;', false)
            ->assertDontSee('<b>bukan tebal</b>', false);
    }
}
