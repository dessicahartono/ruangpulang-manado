<?php

namespace Tests\Feature;

use App\Models\Sponsor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SponsorManagementTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('sponsors');
    }

    private function asAdmin(): static
    {
        return $this->withSession(['is_admin' => true]);
    }

    private function payload(array $overrides = []): array
    {
        return [
            'name' => 'Sponsor Uji',
            'description' => 'Deskripsi sponsor uji.',
            'link' => 'https://www.contoh.com',
            'photo' => UploadedFile::fake()->image('logo.png', 400, 250)->size(300),
            ...$overrides,
        ];
    }

    public function test_seeder_adds_the_current_sponsors_in_order_and_can_run_again(): void
    {
        $this->assertSame(['Paragon', 'Gramedia', 'Cuprise Coffeeshop'], Sponsor::orderBy('position')->pluck('name')->all());

        $this->seed();

        $this->assertSame(3, Sponsor::count());
    }

    public function test_public_page_lists_sponsors_and_is_in_the_menu(): void
    {
        $this->get('/sponsorship')
            ->assertOk()
            ->assertSeeInOrder(['Paragon', 'Gramedia', 'Cuprise Coffeeshop'])
            ->assertSee('Menu utama');

        $this->get('/blog')->assertSee(route('sponsorship'));
    }

    public function test_link_is_only_shown_when_filled(): void
    {
        Sponsor::firstWhere('name', 'Gramedia')->update(['link' => 'https://www.gramedia.com']);

        $this->get('/sponsorship')
            ->assertSee('href="https://www.gramedia.com"', false)
            ->assertSee('Kunjungi Gramedia')
            ->assertDontSee('Kunjungi Paragon');
    }

    public static function protectedRoutes(): array
    {
        return [
            'daftar' => ['GET', '/kelola/sponsorship'],
            'tambah' => ['GET', '/kelola/sponsorship/tambah'],
            'simpan' => ['POST', '/kelola/sponsorship'],
            'tinjau' => ['GET', '/kelola/sponsorship/{sponsor}/tinjau'],
            'ubah' => ['GET', '/kelola/sponsorship/{sponsor}/ubah'],
            'perbarui' => ['PUT', '/kelola/sponsorship/{sponsor}'],
            'hapus' => ['DELETE', '/kelola/sponsorship/{sponsor}'],
        ];
    }

    #[DataProvider('protectedRoutes')]
    public function test_visitors_cannot_manage_sponsors(string $method, string $path): void
    {
        $sponsor = Sponsor::first();

        $this->call($method, str_replace('{sponsor}', (string) $sponsor->id, $path))->assertRedirect('/kelola/masuk');

        $this->assertDatabaseHas('sponsors', ['id' => $sponsor->id]);
    }

    public function test_dashboard_links_to_sponsorship(): void
    {
        $this->asAdmin()->get('/kelola')
            ->assertSee('Kelola Sponsorship')
            ->assertSee('/kelola/sponsorship')
            ->assertSee('3 sponsor');
    }

    public function test_admin_list_shows_missing_photo_and_link(): void
    {
        $this->asAdmin()->get('/kelola/sponsorship')
            ->assertOk()
            ->assertSee('Total 3 sponsor')
            ->assertSee('Belum ada foto dan link');
    }

    public function test_admin_can_add_a_sponsor_with_photo(): void
    {
        $response = $this->asAdmin()->post('/kelola/sponsorship', $this->payload())->assertSessionHas('notice');

        $sponsor = Sponsor::firstWhere('name', 'Sponsor Uji');
        $response->assertRedirect(route('manage.sponsors.show', $sponsor));
        $this->assertSame(3, $sponsor->position);
        $this->assertStringStartsWith('uploads/sponsor/', $sponsor->photo);
        Storage::disk('sponsors')->assertExists(basename($sponsor->photo));

        $this->get('/sponsorship')->assertSeeInOrder(['Cuprise Coffeeshop', 'Sponsor Uji']);
    }

    public function test_link_is_optional_but_must_be_a_web_address(): void
    {
        $this->asAdmin()->post('/kelola/sponsorship', $this->payload(['link' => '']))->assertSessionHasNoErrors();
        $this->asAdmin()->post('/kelola/sponsorship', $this->payload(['name' => 'Lain', 'link' => 'javascript:alert(1)']))
            ->assertSessionHasErrors('link');
    }

    public function test_new_sponsor_needs_name_description_and_photo(): void
    {
        $this->asAdmin()->post('/kelola/sponsorship', $this->payload(['name' => '', 'description' => '', 'photo' => null]))
            ->assertSessionHasErrors(['name', 'description', 'photo']);

        $this->asAdmin()->post('/kelola/sponsorship', $this->payload(['name' => 'Paragon']))
            ->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_without_replacing_the_photo(): void
    {
        $sponsor = Sponsor::firstWhere('name', 'Paragon');

        $this->asAdmin()->put("/kelola/sponsorship/{$sponsor->id}", $this->payload(['name' => 'Paragon', 'photo' => null]))
            ->assertRedirect("/kelola/sponsorship/{$sponsor->id}/tinjau");

        $this->assertSame('https://www.contoh.com', $sponsor->fresh()->link);
        $this->assertNull($sponsor->fresh()->photo);
    }

    public function test_replacing_and_deleting_removes_the_uploaded_photo(): void
    {
        $this->asAdmin()->post('/kelola/sponsorship', $this->payload());
        $sponsor = Sponsor::firstWhere('name', 'Sponsor Uji');
        $oldPhoto = basename($sponsor->photo);

        $this->asAdmin()->put("/kelola/sponsorship/{$sponsor->id}", $this->payload());
        Storage::disk('sponsors')->assertMissing($oldPhoto);
        $newPhoto = basename($sponsor->fresh()->photo);

        $this->asAdmin()->delete("/kelola/sponsorship/{$sponsor->id}")->assertRedirect('/kelola/sponsorship');
        $this->assertDatabaseMissing('sponsors', ['id' => $sponsor->id]);
        Storage::disk('sponsors')->assertMissing($newPhoto);
    }

    public function test_review_and_edit_pages_render(): void
    {
        $sponsor = Sponsor::firstWhere('name', 'Gramedia');

        $this->asAdmin()->get("/kelola/sponsorship/{$sponsor->id}/tinjau")->assertOk()->assertSee('Hapus Sponsor Ini?');
        $this->asAdmin()->get("/kelola/sponsorship/{$sponsor->id}/ubah")->assertOk()->assertSee('Simpan Perubahan');
        $this->asAdmin()->get('/kelola/sponsorship/tambah')->assertOk()->assertSee('Simpan Sponsor');
    }
}
