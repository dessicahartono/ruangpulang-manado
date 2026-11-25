<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityMedia;
use App\Models\Sponsor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ActivityManagementTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('activities');
    }

    private function asAdmin(): static
    {
        return $this->withSession(['is_admin' => true]);
    }

    private function photo(string $name = 'foto.jpg', int $kb = 500): UploadedFile
    {
        return UploadedFile::fake()->image($name, 800, 600)->size($kb);
    }

    private function video(int $kb = 5000): UploadedFile
    {
        return UploadedFile::fake()->create('video.mp4', $kb, 'video/mp4');
    }

    private function payload(array $overrides = []): array
    {
        return [
            'title' => 'Kegiatan Uji',
            'description' => 'Deskripsi kegiatan uji.',
            'media' => [$this->photo()],
            ...$overrides,
        ];
    }

    public function test_seeder_moves_existing_activities_into_the_database_once(): void
    {
        $data = require resource_path('data/activities.php');

        $this->assertSame(array_column($data, 'title'), Activity::orderBy('position')->pluck('title')->all());
        $this->assertSame(count($data), ActivityMedia::count());
        $this->assertDatabaseHas('activities', ['title' => 'Menulis', 'link_url' => '/blog', 'link_label' => 'Kunjungi Blog']);
        $this->assertDatabaseHas('activity_media', ['path' => 'images/kegiatan/pertemuan.jpg', 'focus' => '50% 80%']);

        $this->seed();

        $this->assertSame(count($data), Activity::count());
        $this->assertSame(count($data), ActivityMedia::count());
    }

    public function test_public_page_still_shows_every_activity_and_the_blog_link(): void
    {
        $this->get('/kegiatan')
            ->assertOk()
            ->assertSeeInOrder(['Pertemuan Mingguan', 'Sesi Sharing', 'Menulis'])
            ->assertSee('images/kegiatan/pertemuan.jpg')
            ->assertSee('object-position: 50% 80%', false)
            ->assertSee('Kunjungi Blog');
    }

    public static function protectedRoutes(): array
    {
        return [
            'daftar' => ['GET', '/kelola/kegiatan'],
            'tambah' => ['GET', '/kelola/kegiatan/tambah'],
            'simpan' => ['POST', '/kelola/kegiatan'],
            'tinjau' => ['GET', '/kelola/kegiatan/{activity}/tinjau'],
            'ubah' => ['GET', '/kelola/kegiatan/{activity}/ubah'],
            'perbarui' => ['PUT', '/kelola/kegiatan/{activity}'],
            'hapus' => ['DELETE', '/kelola/kegiatan/{activity}'],
        ];
    }

    #[DataProvider('protectedRoutes')]
    public function test_visitors_cannot_manage_activities(string $method, string $path): void
    {
        $activity = Activity::first();

        $this->call($method, str_replace('{activity}', (string) $activity->id, $path))->assertRedirect('/kelola/masuk');

        $this->assertDatabaseHas('activities', ['id' => $activity->id]);
    }

    public function test_dashboard_card_now_links_to_activity_management(): void
    {
        $this->asAdmin()->get('/kelola')
            ->assertSee('/kelola/kegiatan')
            ->assertSee(Activity::count().' kegiatan')
            ->assertDontSee('On Progress');
    }

    public function test_admin_pages_render(): void
    {
        $activity = Activity::firstWhere('title', 'Sesi Games');

        $this->asAdmin()->get('/kelola/kegiatan')->assertOk()->assertSee('Total '.Activity::count().' kegiatan')->assertSee('1 foto');
        $this->asAdmin()->get('/kelola/kegiatan/tambah')->assertOk()->assertSee('Simpan Kegiatan')
            ->assertSee('data-max-video-seconds="15"', false)
            ->assertSee('maksimal 15 detik dan 20 MB');
        $this->asAdmin()->get("/kelola/kegiatan/{$activity->id}/tinjau")->assertOk()->assertSee('Hapus Kegiatan Ini?');
        $this->asAdmin()->get("/kelola/kegiatan/{$activity->id}/ubah")->assertOk()->assertSee('Hapus foto ini');
    }

    public function test_admin_can_add_an_activity_with_photos_and_a_video(): void
    {
        $this->asAdmin()->post('/kelola/kegiatan', $this->payload([
            'media' => [$this->photo('a.jpg'), $this->video(), $this->photo('b.png')],
        ]))->assertRedirect('/kelola/kegiatan')->assertSessionHasNoErrors();

        $activity = Activity::firstWhere('title', 'Kegiatan Uji');
        $this->assertSame(Activity::count() - 1, $activity->position);
        $this->assertSame(['image', 'video', 'image'], $activity->media->pluck('type')->all());
        $this->assertSame('2 foto dan 1 video', $activity->mediaSummary());

        foreach ($activity->media as $media) {
            $this->assertStringStartsWith('uploads/kegiatan/', $media->path);
            Storage::disk('activities')->assertExists(basename($media->path));
        }

        $this->get('/kegiatan')->assertSee('Kegiatan Uji')->assertSee('<video', false)->assertSee('1/3');
    }

    public function test_new_activity_needs_title_description_and_at_least_one_media(): void
    {
        $this->asAdmin()->post('/kelola/kegiatan', ['title' => '', 'description' => ''])
            ->assertSessionHasErrors(['title', 'description', 'media']);

        $this->asAdmin()->post('/kelola/kegiatan', $this->payload(['title' => 'Menulis']))
            ->assertSessionHasErrors('title');
    }

    public function test_more_than_three_media_is_rejected(): void
    {
        $this->asAdmin()->post('/kelola/kegiatan', $this->payload([
            'media' => [$this->photo(), $this->photo(), $this->photo(), $this->photo()],
        ]))->assertSessionHasErrors('media');

        $this->assertDatabaseMissing('activities', ['title' => 'Kegiatan Uji']);
    }

    public function test_size_limits_differ_for_photos_and_videos(): void
    {
        $this->asAdmin()->post('/kelola/kegiatan', $this->payload(['media' => [$this->photo(kb: 3000)]]))
            ->assertSessionHasErrors(['media.0' => 'Ukuran foto maksimal 2 MB.']);

        $this->asAdmin()->post('/kelola/kegiatan', $this->payload(['media' => [$this->video(21000)]]))
            ->assertSessionHasErrors(['media.0' => 'Ukuran video maksimal 20 MB.']);

        $this->asAdmin()->post('/kelola/kegiatan', $this->payload(['media' => [$this->video(19000)]]))
            ->assertSessionHasNoErrors();
    }

    public function test_other_file_types_are_rejected(): void
    {
        $this->asAdmin()->post('/kelola/kegiatan', $this->payload([
            'media' => [UploadedFile::fake()->create('berkas.pdf', 100, 'application/pdf')],
        ]))->assertSessionHasErrors('media.0');
    }

    public function test_editing_keeps_media_and_can_add_up_to_the_limit(): void
    {
        $activity = Activity::firstWhere('title', 'Sesi Games');

        $this->asAdmin()->put("/kelola/kegiatan/{$activity->id}", $this->payload(['title' => 'Sesi Games', 'media' => []]))
            ->assertRedirect("/kelola/kegiatan/{$activity->id}/tinjau");
        $this->assertSame(1, $activity->media()->count());

        $this->asAdmin()->put("/kelola/kegiatan/{$activity->id}", $this->payload(['title' => 'Sesi Games', 'media' => [$this->photo(), $this->video()]]))
            ->assertSessionHasNoErrors();
        $this->assertSame(['image', 'image', 'video'], $activity->media()->pluck('type')->all());

        $this->asAdmin()->put("/kelola/kegiatan/{$activity->id}", $this->payload(['title' => 'Sesi Games', 'media' => [$this->photo()]]))
            ->assertSessionHasErrors('media');
    }

    public function test_removing_media_deletes_only_uploaded_files_and_keeps_at_least_one(): void
    {
        $activity = Activity::firstWhere('title', 'Sesi Games');
        $seeded = $activity->media()->first();

        $this->asAdmin()->put("/kelola/kegiatan/{$activity->id}", $this->payload([
            'title' => 'Sesi Games', 'media' => [], 'remove_media' => [$seeded->id],
        ]))->assertSessionHasErrors('media');

        $this->asAdmin()->put("/kelola/kegiatan/{$activity->id}", $this->payload([
            'title' => 'Sesi Games', 'media' => [$this->video()], 'remove_media' => [$seeded->id],
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('activity_media', ['id' => $seeded->id]);
        $this->assertFileExists(public_path('images/kegiatan/games.webp'));

        $uploaded = $activity->media()->first();
        $this->asAdmin()->put("/kelola/kegiatan/{$activity->id}", $this->payload([
            'title' => 'Sesi Games', 'media' => [$this->photo()], 'remove_media' => [$uploaded->id],
        ]))->assertSessionHasNoErrors();
        Storage::disk('activities')->assertMissing(basename($uploaded->path));
    }

    public function test_cannot_remove_media_of_another_activity(): void
    {
        $activity = Activity::firstWhere('title', 'Sesi Games');
        $other = Activity::firstWhere('title', 'Menulis')->media()->first();

        $this->asAdmin()->put("/kelola/kegiatan/{$activity->id}", $this->payload([
            'title' => 'Sesi Games', 'media' => [], 'remove_media' => [$other->id],
        ]))->assertSessionHasErrors('remove_media.0');

        $this->assertDatabaseHas('activity_media', ['id' => $other->id]);
    }

    public function test_admin_can_add_an_external_link_that_opens_in_a_new_tab(): void
    {
        $this->asAdmin()->post('/kelola/kegiatan', $this->payload([
            'link_url' => 'https://www.instagram.com/p/contoh/',
            'link_label' => 'Lihat Dokumentasi',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('activities', ['title' => 'Kegiatan Uji', 'link_url' => 'https://www.instagram.com/p/contoh/', 'link_label' => 'Lihat Dokumentasi']);

        $this->get('/kegiatan')
            ->assertSeeInOrder(['href="https://www.instagram.com/p/contoh/"', 'target="_blank"', 'rel="noopener"', 'Lihat Dokumentasi'], false);
    }

    public function test_link_label_defaults_and_is_dropped_without_a_link(): void
    {
        $this->asAdmin()->post('/kelola/kegiatan', $this->payload(['link_url' => '/sponsorship', 'link_label' => '']));
        $this->assertDatabaseHas('activities', ['title' => 'Kegiatan Uji', 'link_url' => '/sponsorship', 'link_label' => 'Kunjungi Laman']);

        $this->asAdmin()->post('/kelola/kegiatan', $this->payload(['title' => 'Kegiatan Luar', 'link_url' => 'https://www.youtube.com/', 'link_label' => '']));
        $this->assertDatabaseHas('activities', ['title' => 'Kegiatan Luar', 'link_label' => 'Kunjungi Situs']);

        $activity = Activity::firstWhere('title', 'Kegiatan Uji');
        $this->asAdmin()->put("/kelola/kegiatan/{$activity->id}", $this->payload(['media' => [], 'link_url' => '', 'link_label' => 'Sisa Teks']));
        $this->assertDatabaseHas('activities', ['id' => $activity->id, 'link_url' => null, 'link_label' => null]);
    }

    public function test_edit_form_keeps_the_seeded_blog_link(): void
    {
        $activity = Activity::firstWhere('title', 'Menulis');

        $this->asAdmin()->get("/kelola/kegiatan/{$activity->id}/ubah")
            ->assertSee('value="/blog"', false)
            ->assertSee('value="Kunjungi Blog"', false);

        $this->get('/kegiatan')->assertSee('href="'.url('/blog').'"', false)->assertDontSee('href="'.url('/blog').'" target', false);
    }

    public static function invalidLinks(): array
    {
        return [
            'tanpa awalan' => ['www.contoh.com'],
            'javascript' => ['javascript:alert(1)'],
            'situs lain lewat dua garis miring' => ['//contoh.com'],
            'ada spasi' => ['https://contoh .com'],
        ];
    }

    #[DataProvider('invalidLinks')]
    public function test_invalid_links_are_rejected(string $link): void
    {
        $this->asAdmin()->post('/kelola/kegiatan', $this->payload(['link_url' => $link]))->assertSessionHasErrors('link_url');
    }

    public function test_activities_and_sponsors_are_linked_many_to_many(): void
    {
        $gramedia = Sponsor::firstWhere('name', 'Gramedia');
        $paragon = Sponsor::firstWhere('name', 'Paragon');

        $this->asAdmin()->post('/kelola/kegiatan', $this->payload(['sponsors' => [$gramedia->id, $paragon->id]]))
            ->assertSessionHasNoErrors();
        $activity = Activity::firstWhere('title', 'Kegiatan Uji');
        $menulis = Activity::firstWhere('title', 'Menulis');
        $this->asAdmin()->put("/kelola/kegiatan/{$menulis->id}", $this->payload([
            'title' => 'Menulis', 'media' => [], 'link_url' => '/blog', 'sponsors' => [$gramedia->id],
        ]))->assertSessionHasNoErrors();

        // Dari dua arah: kegiatan punya banyak sponsor, sponsor mendukung banyak kegiatan.
        $this->assertSame(['Paragon', 'Gramedia'], $activity->sponsors->pluck('name')->all());
        $this->assertSame(['Menulis', 'Kegiatan Uji'], $gramedia->activities->pluck('title')->all());

        $this->get('/kegiatan')->assertSee('Didukung oleh Paragon dan Gramedia');
        $this->get('/sponsorship')->assertSee('Mendukung kegiatan Menulis dan Kegiatan Uji');
        $this->asAdmin()->get("/kelola/sponsorship/{$gramedia->id}/tinjau")->assertSee('Menulis dan Kegiatan Uji');
    }

    public function test_unchecking_sponsors_detaches_them_and_deleting_cleans_the_pivot(): void
    {
        $gramedia = Sponsor::firstWhere('name', 'Gramedia');
        $this->asAdmin()->post('/kelola/kegiatan', $this->payload(['sponsors' => [$gramedia->id]]));
        $activity = Activity::firstWhere('title', 'Kegiatan Uji');

        $this->asAdmin()->get("/kelola/kegiatan/{$activity->id}/ubah")->assertSee('value="'.$gramedia->id.'" checked', false);

        $this->asAdmin()->put("/kelola/kegiatan/{$activity->id}", $this->payload(['media' => []]));
        $this->assertDatabaseMissing('activity_sponsor', ['activity_id' => $activity->id]);

        $activity->sponsors()->attach($gramedia);
        $this->asAdmin()->delete("/kelola/sponsorship/{$gramedia->id}");
        $this->assertDatabaseMissing('activity_sponsor', ['sponsor_id' => $gramedia->id]);
        $this->assertDatabaseHas('activities', ['id' => $activity->id]);
    }

    public function test_unknown_sponsor_is_rejected(): void
    {
        $this->asAdmin()->post('/kelola/kegiatan', $this->payload(['sponsors' => [999]]))->assertSessionHasErrors('sponsors.0');
    }

    public function test_deleting_an_activity_removes_its_uploaded_files(): void
    {
        $this->asAdmin()->post('/kelola/kegiatan', $this->payload(['media' => [$this->photo(), $this->video()]]));
        $activity = Activity::firstWhere('title', 'Kegiatan Uji');
        $files = $activity->media->map(fn ($media) => basename($media->path));

        $this->asAdmin()->delete("/kelola/kegiatan/{$activity->id}")->assertRedirect('/kelola/kegiatan');

        $this->assertDatabaseMissing('activities', ['id' => $activity->id]);
        $this->assertDatabaseMissing('activity_media', ['activity_id' => $activity->id]);
        $files->each(fn ($file) => Storage::disk('activities')->assertMissing($file));
    }
}
