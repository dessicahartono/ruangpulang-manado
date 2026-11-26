<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Sponsor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Tests\TestCase;

class EnglishVersionTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /** Gemini palsu: awalan "EN " di depan setiap teks, atau di dalam setiap paragraf HTML. */
    private function fakeGemini(): void
    {
        config(['services.gemini.key' => 'test-key']);

        Http::fake(function ($request) {
            $texts = json_decode($request['contents'][0]['parts'][0]['text'], true);
            $translate = fn (string $text) => str_starts_with($text, '<') ? str_replace('<p>', '<p>EN ', $text) : "EN {$text}";
            $translated = array_map(fn ($text) => is_array($text) ? array_map($translate, $text) : $translate($text), $texts);

            return Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode($translated)]]]]]]);
        });
    }

    public function test_language_switch_is_remembered_and_returns_to_the_page(): void
    {
        $this->get('/tentang')->assertSee('Sejarah dan Latar Belakang')->assertSee('lang="id"', false);

        $this->from('/tentang')->get('/bahasa/en')->assertRedirect('/tentang');

        $this->get('/tentang')
            ->assertSee('History and Background')
            ->assertSee('Ruang Pulang was officially founded')
            ->assertSee('<html lang="en">', false);
    }

    public function test_unknown_language_is_not_found(): void
    {
        $this->get('/bahasa/fr')->assertNotFound();
    }

    public function test_admin_pages_stay_in_indonesian(): void
    {
        $this->withSession(['is_admin' => true, 'locale' => 'en'])
            ->get('/kelola/sponsorship')
            ->assertSee('<html lang="id">', false)
            ->assertDontSee('lang-menu');
    }

    public function test_content_without_english_falls_back_to_indonesian(): void
    {
        $sponsor = Sponsor::first();

        $this->withSession(['locale' => 'en'])->get('/sponsorship')->assertSee($sponsor->description);

        $sponsor->update(['description_en' => 'An English description.']);

        $this->withSession(['locale' => 'en'])->get('/sponsorship')->assertSee('An English description.');
    }

    public function test_admin_article_is_translated_when_published(): void
    {
        $this->fakeGemini();

        $this->withSession(['is_admin' => true])->post('/kelola/blog', [
            'title' => 'Judul Baru',
            'author' => 'Penulis',
            'body' => '<p>Paragraf pertama.</p><script>alert(1)</script>',
        ]);

        $post = Post::firstWhere('title', 'Judul Baru');

        $this->assertSame('EN Judul Baru', $post->title_en);
        $this->assertSame('<p>EN Paragraf pertama.</p>', $post->body_en);
        $this->assertSame('EN Paragraf pertama.', $post->excerpt_en);
    }

    public function test_english_typed_by_admin_is_kept_and_retranslated_when_indonesian_changes(): void
    {
        $this->fakeGemini();
        $sponsor = Sponsor::first();
        $admin = $this->withSession(['is_admin' => true]);

        $admin->put("/kelola/sponsorship/{$sponsor->id}", [
            'name' => $sponsor->name,
            'description' => $sponsor->description,
            'description_en' => 'Written by the admin.',
        ]);
        $this->assertSame('Written by the admin.', $sponsor->fresh()->description_en);

        $admin->put("/kelola/sponsorship/{$sponsor->id}", [
            'name' => $sponsor->name,
            'description' => 'Deskripsi baru.',
            'description_en' => 'Written by the admin.',
        ]);
        $this->assertSame('EN Deskripsi baru.', $sponsor->fresh()->description_en);
    }

    public function test_visitor_submission_is_only_translated_after_approval(): void
    {
        $this->fakeGemini();

        $this->post('/tulis-artikel', ['title' => 'Kiriman', 'author' => 'Tamu', 'body' => 'Isi kiriman.']);
        $post = Post::firstWhere('title', 'Kiriman');

        $this->assertNull($post->title_en);
        Http::assertNothingSent();

        $this->withSession(['is_admin' => true])->post("/kelola/blog/{$post->slug}/setujui");

        $this->assertSame('EN Kiriman', $post->fresh()->title_en);
        $this->assertSame('EN Isi kiriman.', $post->fresh()->excerpt_en);
    }

    public function test_translation_failure_keeps_indonesian(): void
    {
        config(['services.gemini.key' => 'test-key']);
        Http::fake(['*' => Http::response('quota exceeded', 429)]);

        $this->withSession(['is_admin' => true])->post('/kelola/blog', ['title' => 'Judul Gagal', 'author' => 'Penulis', 'body' => '<p>Isi.</p>']);

        $post = Post::firstWhere('title', 'Judul Gagal');
        $this->assertNotNull($post);
        $this->assertNull($post->title_en);
    }

    public function test_command_fills_missing_english(): void
    {
        $this->fakeGemini();
        Sleep::fake();

        $this->artisan('konten:terjemahkan')->assertSuccessful();

        $this->assertSame('EN '.Sponsor::first()->description, Sponsor::first()->description_en);
        $this->assertNull(Post::where('status', Post::STATUS_PUBLISHED)->whereNull('title_en')->first());
    }
}

