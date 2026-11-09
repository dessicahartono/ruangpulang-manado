<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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
        $this->post('/tulis-artikel', [...self::ARTICLE, 'status' => 'published', 'published_at' => '2026-01-01']);

        $post = Post::firstWhere('title', 'Judul Baru');

        $this->assertTrue($post->isPending());
        $this->assertNull($post->published_at);
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
        $this->asAdmin()->post("/kelola/blog/{$post->slug}/setujui");

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
            'daftar' => ['GET', '/kelola/blog'],
            'form tambah' => ['GET', '/kelola/blog/tambah'],
            'simpan' => ['POST', '/kelola/blog'],
            'tinjau' => ['GET', "/kelola/blog/{$slug}/tinjau"],
            'form ubah' => ['GET', "/kelola/blog/{$slug}/ubah"],
            'ubah' => ['PUT', "/kelola/blog/{$slug}"],
            'setujui' => ['POST', "/kelola/blog/{$slug}/setujui"],
            'hapus' => ['DELETE', "/kelola/blog/{$slug}"],
        ];
    }

    public function test_admin_can_log_in_and_log_out(): void
    {
        config(['community.admin_password_hash' => Hash::make('kata-sandi-benar')]);

        $this->get('/kelola/blog')->assertRedirect('/kelola/masuk');

        $this->post('/kelola/masuk', ['password' => 'kata-sandi-salah'])->assertSessionHasErrors('password');
        $this->assertNull(session('is_admin'));

        $this->post('/kelola/masuk', ['password' => 'kata-sandi-benar'])->assertRedirect('/kelola/blog');
        $this->get('/kelola/blog')->assertOk()->assertSee('Tulis Artikel');

        $this->post('/kelola/keluar')->assertRedirect('/');
        $this->get('/kelola/blog')->assertRedirect('/kelola/masuk');
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

    public function test_admin_list_separates_pending_from_published(): void
    {
        $this->post('/tulis-artikel', self::ARTICLE);

        $this->asAdmin()->get('/kelola/blog')
            ->assertSeeInOrder([
                'Total 18 artikel',
                'Menunggu Persetujuan (1)', 'Tanggal Dikirim', 'Judul Baru', 'Tinjau Artikel',
                'Sudah Diterbitkan (17)', 'Tanggal Publikasi', 'Kapan Sebaiknya',
            ])
            ->assertSee('/kelola/blog/judul-baru/tinjau')
            ->assertDontSee('Setujui')
            ->assertDontSee('Kategori');
    }

    public function test_approved_article_moves_to_the_published_section(): void
    {
        $this->post('/tulis-artikel', self::ARTICLE);
        $this->asAdmin()->post('/kelola/blog/judul-baru/setujui');

        $this->get('/kelola/blog')
            ->assertSeeInOrder(['Menunggu Persetujuan (0)', 'Tidak ada artikel yang menunggu persetujuan.', 'Sudah Diterbitkan (18)', 'Judul Baru']);
    }

    public function test_search_applies_to_both_sections(): void
    {
        $this->post('/tulis-artikel', self::ARTICLE);

        $this->asAdmin()->get('/kelola/blog?q=Judul+Baru')
            ->assertSeeInOrder(['Total 1 artikel', 'Menunggu Persetujuan (1)', 'Sudah Diterbitkan (0)', 'Tidak ada yang cocok'])
            ->assertDontSee('Kapan Sebaiknya');
    }

    private function createPosts(string $status, int $count): void
    {
        foreach (range(1, $count) as $number) {
            Post::create([
                'slug' => "{$status}-{$number}",
                'title' => "{$status} {$number}",
                'author' => 'Penulis Tes',
                'status' => $status,
                'excerpt' => 'x',
                'body' => 'x',
                'published_at' => $status === Post::STATUS_PUBLISHED ? now()->subYears(2)->addMinutes($number) : null,
            ]);
        }
    }

    public function test_queue_starts_with_the_oldest_submission(): void
    {
        foreach (['Kiriman Tengah' => 2, 'Kiriman Terbaru' => 1, 'Kiriman Terlama' => 3] as $title => $daysAgo) {
            $this->travelTo(now()->subDays($daysAgo));
            $this->post('/tulis-artikel', [...self::ARTICLE, 'title' => $title]);
            $this->travelBack();
        }

        $this->asAdmin()->get('/kelola/blog')->assertSeeInOrder(['Kiriman Terlama', 'Kiriman Tengah', 'Kiriman Terbaru']);
        $this->get('/kelola/blog?urut=terbaru')->assertSeeInOrder(['Kiriman Terbaru', 'Kiriman Tengah', 'Kiriman Terlama']);
    }

    public function test_default_order_of_published_articles_stays_newest_first(): void
    {
        $this->asAdmin()->get('/kelola/blog')->assertSeeInOrder(['Kapan Sebaiknya', 'Kenapa Bercerita']);
        $this->get('/blog')->assertSeeInOrder(['Kapan Sebaiknya', 'Kenapa Bercerita']);
    }

    public function test_first_page_shows_two_pending_and_eight_published(): void
    {
        $this->createPosts(Post::STATUS_PENDING, 5);
        $this->createPosts(Post::STATUS_PUBLISHED, 10);

        $posts = $this->asAdmin()->get('/kelola/blog')
            ->assertSee('Total 32 artikel')
            ->assertSee('Menunggu Persetujuan (5)')
            ->assertSee('Sudah Diterbitkan (27)')
            ->assertSee('Lihat Selengkapnya (3)')
            ->assertSee('Berikutnya')
            ->viewData('publishedPosts');

        $this->assertCount(8, $posts);
    }

    public function test_later_pages_show_ten_published_without_pending(): void
    {
        $this->createPosts(Post::STATUS_PENDING, 5);
        $this->createPosts(Post::STATUS_PUBLISHED, 3);

        $response = $this->asAdmin()->get('/kelola/blog?page=2')
            ->assertDontSee('Menunggu Persetujuan (')
            ->assertSee('Sudah Diterbitkan (20)');

        $this->assertCount(10, $response->viewData('publishedPosts'));

        $seen = collect([1, 2, 3])->flatMap(
            fn (int $page) => $this->get("/kelola/blog?page={$page}")->viewData('publishedPosts')->pluck('slug')
        );

        // Tidak ada artikel yang terlewat atau muncul dua kali di antara halaman.
        $this->assertCount(20, $seen);
        $this->assertCount(20, $seen->unique());
    }

    public function test_fewer_pending_leaves_more_room_for_published(): void
    {
        $this->createPosts(Post::STATUS_PUBLISHED, 10);

        $this->assertCount(10, $this->asAdmin()->get('/kelola/blog')->assertDontSee('Lihat Selengkapnya')->viewData('publishedPosts'));

        $this->createPosts(Post::STATUS_PENDING, 1);

        $this->assertCount(9, $this->get('/kelola/blog')->assertDontSee('Lihat Selengkapnya')->viewData('publishedPosts'));
    }

    public function test_exactly_eight_published_with_two_pending_fit_on_one_page(): void
    {
        Post::query()->delete();
        $this->createPosts(Post::STATUS_PENDING, 2);
        $this->createPosts(Post::STATUS_PUBLISHED, 8);

        $this->asAdmin()->get('/kelola/blog')->assertDontSee('Berikutnya')->assertSee('Total 10 artikel');
    }

    public function test_admin_can_review_and_approve_a_submission(): void
    {
        $this->post('/tulis-artikel', self::ARTICLE);

        $this->asAdmin()->get('/kelola/blog/judul-baru/tinjau')
            ->assertOk()
            ->assertSee('Tinjau')
            ->assertSee('Subjudul')
            ->assertSee('Setujui Artikel')
            ->assertDontSee('Artikel Telah Diterbitkan');

        $this->post('/kelola/blog/judul-baru/setujui')->assertRedirect('/kelola/blog/judul-baru/tinjau#terbit');

        $this->get('/kelola/blog/judul-baru/tinjau')->assertSee('Artikel Telah Diterbitkan')->assertDontSee('Setujui Artikel');
        $this->assertNotNull(Post::firstWhere('slug', 'judul-baru')->published_at);
        $this->get('/blog')->assertSee('Judul Baru');
        $this->get('/blog/judul-baru')->assertOk()->assertSee('Paragraf kedua.');
    }

    public function test_publication_date_follows_manado_time(): void
    {
        // 21.30 UTC tanggal 8 sudah pukul 05.30 WITA tanggal 9.
        $this->travelTo(Carbon::parse('2026-11-08 21:30:00', 'UTC'));

        $this->asAdmin()->post('/kelola/blog', self::ARTICLE);

        $this->get('/blog/judul-baru')->assertSee('9 November 2026');
    }

    public function test_approving_a_published_post_keeps_its_date(): void
    {
        $post = Post::firstWhere('slug', self::SLUG);

        $this->asAdmin()->post('/kelola/blog/'.self::SLUG.'/setujui')->assertRedirect();

        $this->assertTrue($post->published_at->equalTo($post->fresh()->published_at));
    }

    public function test_admin_can_publish_directly(): void
    {
        $this->asAdmin()->post('/kelola/blog', self::ARTICLE)->assertRedirect('/kelola/blog');

        $this->assertFalse(Post::firstWhere('slug', 'judul-baru')->isPending());
        $this->get('/blog/judul-baru')->assertOk();
    }

    public function test_admin_edit_keeps_slug_and_date(): void
    {
        $before = Post::firstWhere('slug', self::SLUG);

        $this->asAdmin()->get('/kelola/blog/'.self::SLUG.'/ubah')->assertOk()->assertSee('Ubah')->assertSee('Simpan Perubahan');
        $this->put('/kelola/blog/'.self::SLUG, ['title' => 'Judul Diubah', 'author' => 'Admin', 'body' => 'Isi baru.'])
            ->assertRedirect('/kelola/blog/'.self::SLUG.'/tinjau');

        $after = $before->fresh();

        $this->assertSame('Judul Diubah', $after->title);
        $this->assertSame(self::SLUG, $after->slug);
        $this->assertTrue($before->published_at->equalTo($after->published_at));
    }

    public function test_admin_can_delete(): void
    {
        $this->asAdmin()->delete('/kelola/blog/'.self::SLUG)->assertRedirect('/kelola/blog');

        $this->assertDatabaseMissing('posts', ['slug' => self::SLUG]);
        $this->get('/blog/'.self::SLUG)->assertNotFound();
    }

    public function test_managing_an_unknown_post_is_not_found(): void
    {
        $this->asAdmin()->get('/kelola/blog/tidak-ada/ubah')->assertNotFound();
        $this->get('/kelola/blog/tidak-ada/tinjau')->assertNotFound();
        $this->delete('/kelola/blog/tidak-ada')->assertNotFound();
        $this->post('/kelola/blog/tidak-ada/setujui')->assertNotFound();
    }

    public function test_seeder_does_not_overwrite_existing_posts(): void
    {
        Post::firstWhere('slug', self::SLUG)->update(['title' => 'Sudah Diedit']);

        $this->seed();

        $this->assertSame(17, Post::count());
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
