<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ArticleModerationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private const SLUG = 'healing-tidak-harus-liburan';

    private const ARTICLE = ['title' => 'Judul Baru', 'author' => 'Penulis', 'body' => "Paragraf pertama.\n\n## Subjudul\n\nParagraf kedua."];

    private function asAdmin(): static
    {
        return $this->withSession(['is_admin' => true]);
    }

    public function test_visitor_submission_waits_for_approval(): void
    {
        $this->post('/tulis-artikel', self::ARTICLE)->assertRedirect('/tulis-artikel')->assertSessionHas('notice');

        $post = Post::firstWhere('title', 'Judul Baru');

        $this->assertTrue($post->isPending());
        $this->assertNull($post->published_at);
        $this->assertSame('judul-baru', $post->slug);
        $this->assertSame('Paragraf pertama.', $post->excerpt);

        $this->get('/blog')->assertDontSee('Judul Baru');
        $this->get('/blog?q=Judul+Baru')->assertSee('Sekitar 0 artikel');
        $this->get('/blog/judul-baru')->assertNotFound();
    }

    public function test_visitor_cannot_publish_or_choose_a_status(): void
    {
        $this->post('/tulis-artikel', [...self::ARTICLE, 'status' => 'published', 'published_at' => '2026-01-01', 'category' => 'Healing']);

        $post = Post::firstWhere('title', 'Judul Baru');

        $this->assertTrue($post->isPending());
        $this->assertNull($post->published_at);
        $this->assertNull($post->category);
    }

    public function test_article_form_validates_input(): void
    {
        $this->post('/tulis-artikel', [])
            ->assertSessionHasErrors(['title' => 'Judul Artikel wajib diisi.', 'author', 'body']);

        $this->assertDatabaseMissing('posts', ['status' => Post::STATUS_PENDING]);
    }

    public function test_honeypot_discards_bot_submissions(): void
    {
        $this->post('/tulis-artikel', [...self::ARTICLE, 'website' => 'spam.example'])->assertSessionHas('notice');

        $this->assertDatabaseMissing('posts', ['title' => 'Judul Baru']);
    }

    public function test_submissions_are_rate_limited(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->post('/tulis-artikel', self::ARTICLE)->assertRedirect();
        }

        $this->post('/tulis-artikel', self::ARTICLE)->assertTooManyRequests()->assertSee('Terlalu banyak percobaan');
        $this->assertSame(5, Post::where('status', Post::STATUS_PENDING)->count());
    }

    public function test_same_title_gets_a_unique_slug_and_symbols_get_a_fallback(): void
    {
        $this->post('/tulis-artikel', self::ARTICLE);
        $this->post('/tulis-artikel', self::ARTICLE);
        $this->post('/tulis-artikel', [...self::ARTICLE, 'title' => '???']);

        $this->assertDatabaseHas('posts', ['slug' => 'judul-baru-2']);
        $this->assertDatabaseHas('posts', ['slug' => 'artikel']);
    }

    public function test_submitted_html_is_escaped_on_the_article_page(): void
    {
        $this->post('/tulis-artikel', [...self::ARTICLE, 'body' => '<script>alert(1)</script>']);

        $post = Post::firstWhere('title', 'Judul Baru');
        $this->asAdmin()->post("/kelola/{$post->slug}/setujui");

        $this->get("/blog/{$post->slug}")->assertOk()->assertDontSee('<script>alert(1)</script>', false);
    }

    #[DataProvider('protectedRoutes')]
    public function test_manage_routes_require_admin(string $method, string $uri): void
    {
        $before = Post::count();

        $this->call($method, $uri, ['title' => 'Diubah', 'author' => 'x', 'body' => 'x'])
            ->assertRedirect('/kelola/masuk');

        $this->assertSame($before, Post::count());
        $this->assertDatabaseMissing('posts', ['title' => 'Diubah']);
    }

    public static function protectedRoutes(): array
    {
        $slug = self::SLUG;

        return [
            'daftar' => ['GET', '/kelola'],
            'form tambah' => ['GET', '/kelola/tambah'],
            'simpan' => ['POST', '/kelola'],
            'form ubah' => ['GET', "/kelola/{$slug}/ubah"],
            'ubah' => ['PUT', "/kelola/{$slug}"],
            'setujui' => ['POST', "/kelola/{$slug}/setujui"],
            'hapus' => ['DELETE', "/kelola/{$slug}"],
        ];
    }

    public function test_admin_can_log_in_and_log_out(): void
    {
        config(['community.admin_password_hash' => Hash::make('kata-sandi-benar')]);

        $this->get('/kelola')->assertRedirect('/kelola/masuk');

        $this->post('/kelola/masuk', ['password' => 'kata-sandi-salah'])->assertSessionHasErrors('password');
        $this->assertNull(session('is_admin'));

        $this->post('/kelola/masuk', ['password' => 'kata-sandi-benar'])->assertRedirect('/kelola');
        $this->get('/kelola')->assertOk()->assertSee('Tambah Artikel');

        $this->post('/kelola/keluar')->assertRedirect('/');
        $this->get('/kelola')->assertRedirect('/kelola/masuk');
    }

    public function test_login_is_impossible_when_no_password_is_configured(): void
    {
        config(['community.admin_password_hash' => null]);

        $this->post('/kelola/masuk', ['password' => 'apa-saja'])->assertSessionHasErrors('password');
        $this->assertNull(session('is_admin'));
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        config(['community.admin_password_hash' => Hash::make('kata-sandi-benar')]);

        foreach (range(1, 5) as $attempt) {
            $this->post('/kelola/masuk', ['password' => 'salah']);
        }

        $this->post('/kelola/masuk', ['password' => 'kata-sandi-benar'])->assertTooManyRequests();
    }

    public function test_admin_can_approve_a_submission(): void
    {
        $this->post('/tulis-artikel', self::ARTICLE);

        $this->asAdmin()->get('/kelola')
            ->assertSeeInOrder(['Judul Baru', 'Menunggu Persetujuan', 'Setujui', 'Kapan Sebaiknya'])
            ->assertSee('(1 Menunggu Persetujuan)');

        $this->post('/kelola/judul-baru/setujui')->assertRedirect('/kelola');

        $this->assertNotNull(Post::firstWhere('slug', 'judul-baru')->published_at);
        $this->get('/blog')->assertSee('Judul Baru');
        $this->get('/blog/judul-baru')->assertOk()->assertSee('Subjudul')->assertSee('Paragraf kedua.');
    }

    public function test_admin_can_publish_directly(): void
    {
        $this->asAdmin()->post('/kelola', [...self::ARTICLE, 'category' => 'Healing'])->assertRedirect('/kelola');

        $post = Post::firstWhere('slug', 'judul-baru');

        $this->assertFalse($post->isPending());
        $this->assertSame('Healing', $post->category);
        $this->get('/blog/judul-baru')->assertOk();
    }

    public function test_admin_edit_keeps_slug_and_date(): void
    {
        $before = Post::firstWhere('slug', self::SLUG);

        $this->asAdmin()->get('/kelola/'.self::SLUG.'/ubah')->assertOk()->assertSee('Simpan Perubahan');
        $this->put('/kelola/'.self::SLUG, ['title' => 'Judul Diubah', 'author' => 'Admin', 'body' => 'Isi baru.', 'category' => 'Bantuan'])
            ->assertRedirect('/kelola');

        $after = $before->fresh();

        $this->assertSame('Judul Diubah', $after->title);
        $this->assertSame(self::SLUG, $after->slug);
        $this->assertSame('Bantuan', $after->category);
        $this->assertTrue($before->published_at->equalTo($after->published_at));
    }

    public function test_admin_can_delete_and_rejects_unknown_categories(): void
    {
        $this->asAdmin()->post('/kelola', [...self::ARTICLE, 'category' => 'Palsu'])->assertSessionHasErrors('category');

        $this->delete('/kelola/'.self::SLUG)->assertRedirect('/kelola');

        $this->assertDatabaseMissing('posts', ['slug' => self::SLUG]);
        $this->get('/blog/'.self::SLUG)->assertNotFound();
    }

    public function test_manage_page_filters_by_category(): void
    {
        $this->asAdmin()->get('/kelola?kategori=Bantuan')
            ->assertSee('/blog/kapan-sebaiknya-mencari-bantuan-profesional')
            ->assertDontSee('/blog/healing-tidak-harus-liburan')
            ->assertSee('Total 1 artikel');

        $this->get('/kelola?kategori=tidak-ada')->assertOk()->assertSee('Total 6 artikel');
    }

    public function test_managing_an_unknown_post_is_not_found(): void
    {
        $this->asAdmin()->get('/kelola/tidak-ada/ubah')->assertNotFound();
        $this->delete('/kelola/tidak-ada')->assertNotFound();
        $this->post('/kelola/tidak-ada/setujui')->assertNotFound();
    }

    public function test_seeder_does_not_overwrite_existing_posts(): void
    {
        Post::firstWhere('slug', self::SLUG)->update(['title' => 'Sudah Diedit']);

        $this->seed();

        $this->assertSame(6, Post::count());
        $this->assertSame('Sudah Diedit', Post::firstWhere('slug', self::SLUG)->title);
    }

    public function test_password_command_stores_only_a_hash(): void
    {
        $directory = sys_get_temp_dir().'/rp-env-'.uniqid();
        mkdir($directory);
        file_put_contents("{$directory}/.env", "APP_NAME=Tes\nADMIN_PASSWORD_HASH=\n");
        $this->app->useEnvironmentPath($directory);

        $this->artisan('admin:password')
            ->expectsQuestion('Kata sandi admin baru (minimal 12 karakter)', 'kata-sandi-panjang')
            ->expectsQuestion('Ulangi kata sandi', 'kata-sandi-panjang')
            ->assertSuccessful();

        $env = file_get_contents("{$directory}/.env");
        unlink("{$directory}/.env");
        rmdir($directory);

        $this->assertStringNotContainsString('kata-sandi-panjang', $env);
        $this->assertStringContainsString('APP_NAME=Tes', $env);
        $this->assertSame(1, substr_count($env, 'ADMIN_PASSWORD_HASH='));
        preg_match("/ADMIN_PASSWORD_HASH='(.+)'/", $env, $match);
        $this->assertTrue(Hash::check('kata-sandi-panjang', $match[1]));
    }

    public function test_password_command_rejects_short_passwords(): void
    {
        $this->artisan('admin:password')
            ->expectsQuestion('Kata sandi admin baru (minimal 12 karakter)', 'pendek')
            ->assertFailed();
    }
}
