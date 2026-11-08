<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    #[DataProvider('pages')]
    public function test_page_is_reachable_and_uses_shared_layout(string $url, string $expected): void
    {
        $this->get($url)
            ->assertOk()
            ->assertSee($expected)
            ->assertSee('Menu utama')
            ->assertSee('Media Sosial Kami')
            ->assertSee('Teman Bicara AI');
    }

    public static function pages(): array
    {
        return [
            'beranda' => ['/', 'Pintu Kami Selalu Terbuka untukmu'],
            'tentang' => ['/tentang', 'Sejarah dan Latar Belakang'],
            'kegiatan' => ['/kegiatan', 'Pertemuan Mingguan'],
            'pengurus' => ['/pengurus', 'Para Pendiri'],
            'bergabung' => ['/bergabung', 'Gabung Grup WhatsApp'],
            'blog' => ['/blog', 'Tulis Artikelmu di Sini'],
            'detail blog' => ['/blog/kapan-sebaiknya-mencari-bantuan-profesional', 'Baca Artikel Lainnya'],
            'kontak' => ['/kontak', 'Butuh Bantuan Segera?'],
            'tulis artikel' => ['/tulis-artikel', 'Kirim Artikel'],
            'masuk admin' => ['/kelola/masuk', 'Kata Sandi'],
        ];
    }

    public function test_home_uses_the_transparent_header_and_other_pages_use_the_sub_layout(): void
    {
        $this->get('/')->assertSee('class="is-home"', false)->assertSee('Tentang Kami');
        $this->get('/blog')->assertSee('class="is-home is-sub"', false);
    }

    public function test_about_shows_vision_and_all_missions(): void
    {
        $response = $this->get('/tentang')->assertSee('Visi')->assertSee('Misi');

        foreach ((require resource_path('data/profile.php'))['missions'] as $mission) {
            $response->assertSee($mission);
        }
    }

    public function test_team_page_shows_five_founders_and_every_group(): void
    {
        $groups = require resource_path('data/team.php');
        $this->assertCount(5, $groups[0]['members']);

        $response = $this->get('/pengurus');

        foreach ($groups as $group) {
            $response->assertSee($group['title']);

            foreach ($group['members'] as $member) {
                $response->assertSee($member['name']);

                if ($member['photo']) {
                    $this->assertFileExists(public_path($member['photo']));
                }
            }
        }
    }

    public function test_content_meets_minimum_data_and_images_exist(): void
    {
        $portfolios = require resource_path('data/portfolios.php');

        $this->assertGreaterThanOrEqual(3, count($portfolios));
        $this->assertGreaterThanOrEqual(5, count(require resource_path('data/posts.php')));

        foreach ($portfolios as $portfolio) {
            $this->assertFileExists(public_path($portfolio['image']));
        }
    }

    public function test_every_post_links_to_a_working_detail_page(): void
    {
        foreach (require resource_path('data/posts.php') as $post) {
            $this->get('/blog?q='.urlencode($post['title']))->assertSee(route('blog.show', $post['slug']));
            $this->get(route('blog.show', $post['slug']))
                ->assertOk()
                ->assertSee($post['title'])
                ->assertSee($post['author']);
        }
    }

    public function test_blog_date_is_in_indonesian(): void
    {
        $this->get('/blog')->assertSee('2 November 2026');
    }

    public function test_blog_search_matches_title_author_and_content(): void
    {
        $this->get('/blog?q=sebelum+tidur')
            ->assertSee('Cara Menenangkan Diri Sebelum Tidur')
            ->assertDontSee('Healing Tidak Harus Liburan')
            ->assertSee('Sekitar 1 artikel ditemukan');

        $this->get('/blog?q=TIM+RUANG+PULANG')->assertSee('Sekitar 3 artikel ditemukan');
        $this->get('/blog?q=matahari+terbenam')->assertSee('Healing Tidak Harus Liburan');
        $this->get('/blog?q=tidak-ada-yang-cocok')->assertSee('Belum ada artikel yang cocok');
    }

    public function test_blog_sort_changes_the_order(): void
    {
        $this->get('/blog?urut=terlama')->assertSeeInOrder(['Tips Datang Sendirian', 'Healing Tidak Harus Liburan']);
        $this->get('/blog?urut=terbaru')->assertSeeInOrder(['Kapan Sebaiknya', 'Kenapa Bercerita']);
        $this->get('/blog?urut=judul-az')->assertSeeInOrder(['Belajar Menjadi', 'Cara Menenangkan']);
        $this->get('/blog?urut=judul-za')->assertSeeInOrder(['Tips Datang', 'Kenapa Bercerita']);
    }

    public function test_blog_pagination_keeps_the_query_and_only_shows_real_pages(): void
    {
        $this->get('/blog?urut=terlama')
            ->assertSee('urut=terlama&amp;page=2', false)
            ->assertSee('Berikutnya')
            ->assertDontSee('>3</a>', false);

        $this->get('/blog?urut=terlama&page=2')
            ->assertSee('Kapan Sebaiknya')
            ->assertDontSee('Berikutnya');

        $this->get('/blog?q=sebelum+tidur')->assertDontSee('class="pager"', false);
    }

    public function test_malformed_blog_queries_are_ignored(): void
    {
        $this->get('/blog?q[]=x&urut[]=y&page=abc')->assertOk()->assertSee('Sekitar 6 artikel ditemukan');
        $this->get('/blog?urut=tidak-ada')->assertOk()->assertSeeInOrder(['Kapan Sebaiknya', 'Kenapa Bercerita']);
    }

    public function test_unknown_pages_use_friendly_404(): void
    {
        $this->get('/blog/tidak-ada')->assertNotFound()->assertSee('Salah Belok');
        $this->get('/halaman-yang-tidak-ada')->assertNotFound()->assertSee('Salah Belok');
    }
}
