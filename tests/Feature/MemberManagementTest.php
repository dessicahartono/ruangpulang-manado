<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MemberManagementTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('members');
    }

    /** Semua pengurus awal sudah lengkap, jadi yang belum lengkap dibuat khusus untuk tes. */
    private function incompleteMember(): Member
    {
        return Member::create([
            'division_id' => Division::firstWhere('name', 'Ruang Tumbuh')->id,
            'full_name' => 'Belum Lengkap',
            'nickname' => 'Belum',
            'position' => 99,
        ]);
    }

    private function asAdmin(): static
    {
        return $this->withSession(['is_admin' => true]);
    }

    private function payload(array $overrides = []): array
    {
        return [
            'division_id' => Division::firstWhere('name', 'Ruang Temu')->id,
            'full_name' => 'Nama Uji Coba',
            'nickname' => 'Uji',
            'instagram' => 'https://www.instagram.com/nama.uji/',
            'photo' => UploadedFile::fake()->image('foto.jpg', 300, 300)->size(500),
            ...$overrides,
        ];
    }

    public function test_seeder_fills_every_division_and_keeps_existing_data(): void
    {
        $this->assertSame(7, Division::count());
        $this->assertSame(0, Division::doesntHave('members')->count());

        Member::firstWhere('nickname', 'Alim')->update(['nickname' => 'Sudah Diedit']);
        $before = Member::count();

        $this->seed();

        $this->assertSame($before, Member::count());
        $this->assertDatabaseHas('members', ['nickname' => 'Sudah Diedit']);
    }

    public function test_seeded_members_have_a_photo_and_instagram(): void
    {
        $this->assertSame(0, Member::whereNull('photo')->orWhereNull('instagram')->count());
    }

    public function test_seeder_splits_names_and_keeps_role_and_bio(): void
    {
        $this->assertDatabaseHas('members', ['full_name' => 'Alim Aprilianto', 'nickname' => 'Alim', 'role' => 'Ketua']);
        $this->assertDatabaseHas('members', ['full_name' => 'Dhea Aprilliani Abasi', 'nickname' => 'April', 'photo' => 'images/pengurus/foto-b9db305d.jpg']);
        $this->assertDatabaseHas('members', ['full_name' => 'Reyno', 'nickname' => 'Mas/Rey']);
        $this->assertNotNull(Member::firstWhere('nickname', 'Dhia')->bio_more);
    }

    #[DataProvider('protectedRoutes')]
    public function test_visitors_cannot_manage_members(string $method, string $path): void
    {
        $member = Member::first();

        $this->call($method, str_replace('{member}', (string) $member->id, $path))->assertRedirect('/kelola/masuk');

        $this->assertDatabaseHas('members', ['id' => $member->id]);
    }

    public static function protectedRoutes(): array
    {
        return [
            'menu' => ['GET', '/kelola'],
            'daftar' => ['GET', '/kelola/pengurus'],
            'form tambah' => ['GET', '/kelola/pengurus/tambah'],
            'simpan' => ['POST', '/kelola/pengurus'],
            'tinjau' => ['GET', '/kelola/pengurus/{member}/tinjau'],
            'form ubah' => ['GET', '/kelola/pengurus/{member}/ubah'],
            'ubah' => ['PUT', '/kelola/pengurus/{member}'],
            'hapus' => ['DELETE', '/kelola/pengurus/{member}'],
        ];
    }

    public function test_dashboard_links_to_both_sections(): void
    {
        $this->asAdmin()->get('/kelola')
            ->assertOk()
            ->assertSee('Kelola Profil Pengurus')
            ->assertSee('Kelola Blog')
            ->assertSee('/kelola/pengurus')
            ->assertSee('/kelola/blog')
            ->assertSee('Kelola Menu Utama')
            ->assertSee('Akhiri Sesi dan Keluar')
            ->assertDontSee('Kelola Admin');
    }

    public function test_activity_card_is_only_a_marker_for_now(): void
    {
        $this->asAdmin()->get('/kelola')
            ->assertSeeInOrder(['Kelola Kegiatan', 'On Progress'])
            ->assertDontSee('/kelola/kegiatan');

        $this->get('/kelola/kegiatan')->assertNotFound();
    }

    public function test_blog_management_toolbar_matches_the_member_page(): void
    {
        $this->asAdmin()->get('/kelola/blog?urut=judul-az')
            ->assertSee('Kembali ke menu utama')
            ->assertDontSee('Menu Kelola')
            ->assertSee('class="sort-menu"', false)
            ->assertSee('name="urut" value="judul-az"', false)
            ->assertSee('urut=terlama', false);

        $this->get('/kelola/blog')->assertDontSee('name="urut"', false)->assertDontSee('aria-current="true"', false);
    }

    public function test_list_starts_in_the_order_of_the_team_page_and_is_paginated(): void
    {
        $response = $this->asAdmin()->get('/kelola/pengurus')
            ->assertOk()
            ->assertSee('Total 23 data pengurus')
            ->assertSee('class="pager"', false)
            ->assertSeeInOrder(['Para Pendiri', 'Fathia Salama Jacob', 'Pengurus Inti']);

        $this->assertCount(10, $response->viewData('members'));

        $this->get('/kelola/pengurus?page=3')->assertSee('Opyan')->assertDontSee('Berikutnya');
    }

    public function test_list_can_be_searched_by_name_nickname_and_division(): void
    {
        $this->asAdmin()->get('/kelola/pengurus?q=aprilianto')->assertSee('Alim Aprilianto')->assertSee('Total 1 data');
        $this->get('/kelola/pengurus?q=Moty')->assertSee('Timothy Tongkeles Wirodigdoyo')->assertSee('Total 1 data');
        $this->get('/kelola/pengurus?q=Ruang+Tertib')->assertSee('Agung')->assertSee('Opyan')->assertSee('Total 2 data');
        $this->get('/kelola/pengurus?q=tidak-ada-yang-cocok')->assertSee('Tidak ada pengurus yang cocok');
    }

    public function test_list_can_be_filtered_per_division(): void
    {
        $division = Division::firstWhere('name', 'Ruang Karya');

        $this->asAdmin()->get("/kelola/pengurus?divisi={$division->id}")
            ->assertSee('Total 3 data')
            ->assertSee('Rey')
            ->assertDontSee('Alim Aprilianto');
    }

    public function test_list_can_be_sorted(): void
    {
        $this->asAdmin()->get('/kelola/pengurus?urut=nama-az')->assertSeeInOrder(['Alim Aprilianto', 'Dessica Suhaedi Hartono']);
        $this->get('/kelola/pengurus?urut=nama-za')->assertSeeInOrder(['Varen Julian Legrants', 'Ursula Kimberly Melania', 'Timothy Tongkeles Wirodigdoyo']);

        $this->post('/kelola/pengurus', $this->payload());
        $this->get('/kelola/pengurus?urut=terbaru')->assertSeeInOrder(['Nama Uji Coba', 'Opyan Yunus']);
    }

    public function test_query_keeps_on_pagination_and_malformed_values_are_ignored(): void
    {
        $this->asAdmin()->get('/kelola/pengurus?urut=nama-az')->assertSee('urut=nama-az&amp;page=2', false);
        $this->get('/kelola/pengurus?q[]=x&divisi[]=y&urut[]=z&page=abc')->assertOk()->assertSee('Total 23 data');
    }

    public function test_toolbar_keeps_the_chosen_filters_and_links_back_to_the_menu(): void
    {
        $division = Division::firstWhere('name', 'Ruang Karya');

        $this->asAdmin()->get("/kelola/pengurus?divisi={$division->id}&urut=nama-az")
            ->assertSee('name="urut" value="nama-az"', false)
            ->assertSee("divisi={$division->id}&amp;urut=nama-za", false)
            ->assertSee('Kembali ke menu utama')
            ->assertDontSee('Menu Kelola');
    }

    public function test_list_flags_incomplete_data(): void
    {
        $this->incompleteMember();

        $this->asAdmin()->get('/kelola/pengurus?q=Belum+Lengkap')->assertSee('Belum ada foto dan Instagram');
        $this->get('/kelola/pengurus?q=Aprilianto')->assertSee('Lengkap')->assertDontSee('Belum ada');
    }

    public function test_form_lists_every_division(): void
    {
        $response = $this->asAdmin()->get('/kelola/pengurus/tambah')->assertOk();

        foreach (Division::pluck('name') as $name) {
            $response->assertSee($name);
        }
    }

    public function test_admin_can_add_a_member_who_appears_on_the_team_page(): void
    {
        $this->asAdmin()->post('/kelola/pengurus', $this->payload())
            ->assertRedirect('/kelola/pengurus')
            ->assertSessionHas('notice');

        $member = Member::firstWhere('full_name', 'Nama Uji Coba');

        $this->assertStringStartsWith('uploads/pengurus/', $member->photo);
        Storage::disk('members')->assertExists(basename($member->photo));

        // Masuk ke urutan terakhir di divisinya.
        $this->assertSame(2, $member->position);

        $this->get('/pengurus')
            ->assertSee('Nama Uji Coba (Uji)')
            ->assertSee($member->photo)
            ->assertSee('https://www.instagram.com/nama.uji/');
    }

    #[DataProvider('invalidInput')]
    public function test_invalid_input_is_rejected(string $field, mixed $value): void
    {
        $before = Member::count();

        $this->asAdmin()->post('/kelola/pengurus', $this->payload([$field => $value instanceof \Closure ? $value() : $value]))
            ->assertSessionHasErrors($field);

        $this->assertSame($before, Member::count());
    }

    public static function invalidInput(): array
    {
        return [
            'divisi kosong' => ['division_id', ''],
            'divisi tidak ada' => ['division_id', 99999],
            'nama lengkap kosong' => ['full_name', ''],
            'panggilan kosong' => ['nickname', ''],
            'instagram kosong' => ['instagram', ''],
            'bukan link' => ['instagram', 'namapengguna'],
            'bukan instagram' => ['instagram', 'https://contoh.com/nama'],
            'skrip' => ['instagram', 'javascript:alert(1)'],
            'foto kosong' => ['photo', null],
            'foto lebih dari 2 MB' => ['photo', fn () => UploadedFile::fake()->image('besar.jpg')->size(2049)],
            'bukan gambar' => ['photo', fn () => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf')],
            'svg' => ['photo', fn () => UploadedFile::fake()->create('gambar.svg', 10, 'image/svg+xml')],
            'skrip php' => ['photo', fn () => UploadedFile::fake()->create('foto.php', 10, 'application/x-php')],
        ];
    }

    public function test_photo_of_exactly_two_megabytes_is_accepted(): void
    {
        $this->asAdmin()->post('/kelola/pengurus', $this->payload(['photo' => UploadedFile::fake()->image('pas.jpg')->size(2048)]))
            ->assertSessionHasNoErrors();
    }

    public function test_same_name_is_rejected_in_one_division_but_allowed_in_another(): void
    {
        $this->asAdmin()->post('/kelola/pengurus', $this->payload(['full_name' => 'Fathia Salama Jacob', 'division_id' => Division::firstWhere('name', 'Para Pendiri')->id]))
            ->assertSessionHasErrors('full_name');

        $this->post('/kelola/pengurus', $this->payload(['full_name' => 'Fathia Salama Jacob']))
            ->assertSessionHasNoErrors();
    }

    public function test_review_page_shows_details_and_actions(): void
    {
        $member = Member::firstWhere('nickname', 'Alim');

        $this->asAdmin()->get("/kelola/pengurus/{$member->id}/tinjau")
            ->assertOk()
            ->assertSee('Alim Aprilianto')
            ->assertSee('Pengurus Inti')
            ->assertSee('Ketua')
            ->assertSee('Ubah')
            ->assertSee('Hapus');
    }

    public function test_incomplete_member_can_be_opened_and_saved_without_photo_or_instagram(): void
    {
        $member = $this->incompleteMember();

        $this->asAdmin()->get("/kelola/pengurus/{$member->id}/tinjau")->assertOk()->assertSee('Belum ada');
        $this->get("/kelola/pengurus/{$member->id}/ubah")->assertOk();

        $this->put("/kelola/pengurus/{$member->id}", [
            'division_id' => $member->division_id,
            'full_name' => 'Belum Lengkap Diisi',
            'nickname' => 'Belum',
            'instagram' => '',
        ])->assertRedirect("/kelola/pengurus/{$member->id}/tinjau");

        $this->assertDatabaseHas('members', ['id' => $member->id, 'full_name' => 'Belum Lengkap Diisi', 'instagram' => null, 'photo' => null]);
    }

    public function test_edit_keeps_photo_role_and_bio_when_they_are_not_changed(): void
    {
        $member = Member::firstWhere('nickname', 'Dhia');

        $this->asAdmin()->put("/kelola/pengurus/{$member->id}", [
            'division_id' => $member->division_id,
            'full_name' => 'Nama Diubah',
            'nickname' => 'Dhia',
            'instagram' => $member->instagram,
        ])->assertSessionHasNoErrors();

        $after = $member->fresh();

        $this->assertSame('Nama Diubah', $after->full_name);
        $this->assertSame($member->photo, $after->photo);
        $this->assertSame($member->role, $after->role);
        $this->assertSame($member->bio, $after->bio);
        $this->assertSame($member->position, $after->position);
    }

    public function test_moving_to_another_division_goes_to_the_end_of_that_division(): void
    {
        $member = Member::firstWhere('nickname', 'Moty');
        $target = Division::firstWhere('name', 'Ruang Tertib');

        $this->asAdmin()->put("/kelola/pengurus/{$member->id}", [
            'division_id' => $target->id,
            'full_name' => $member->full_name,
            'nickname' => $member->nickname,
            'instagram' => $member->instagram,
        ])->assertSessionHasNoErrors();

        $this->assertSame($target->id, $member->fresh()->division_id);
        $this->assertSame(2, $member->fresh()->position);
    }

    public function test_replacing_an_uploaded_photo_deletes_the_old_file(): void
    {
        $this->asAdmin()->post('/kelola/pengurus', $this->payload());
        $member = Member::firstWhere('full_name', 'Nama Uji Coba');
        $oldFile = basename($member->photo);

        $this->put("/kelola/pengurus/{$member->id}", $this->payload(['photo' => UploadedFile::fake()->image('baru.png')]))
            ->assertSessionHasNoErrors();

        Storage::disk('members')->assertMissing($oldFile);
        Storage::disk('members')->assertExists(basename($member->fresh()->photo));
    }

    public function test_admin_can_delete_and_uploaded_photo_is_removed(): void
    {
        $this->asAdmin()->post('/kelola/pengurus', $this->payload());
        $member = Member::firstWhere('full_name', 'Nama Uji Coba');
        $file = basename($member->photo);

        $this->delete("/kelola/pengurus/{$member->id}")->assertRedirect('/kelola/pengurus');

        $this->assertDatabaseMissing('members', ['id' => $member->id]);
        Storage::disk('members')->assertMissing($file);
        $this->get('/pengurus')->assertDontSee('Nama Uji Coba');
    }

    public function test_deleting_a_seeded_member_keeps_the_shared_photo(): void
    {
        // Foto Alma dipakai di dua divisi, dan file awalnya tersimpan di Git.
        $member = Member::where('nickname', 'Alma')->first();

        $this->asAdmin()->delete("/kelola/pengurus/{$member->id}")->assertRedirect('/kelola/pengurus');

        $this->assertFileExists(public_path($member->photo));
        $this->assertSame(1, Member::where('nickname', 'Alma')->count());
    }

    public function test_unknown_member_is_not_found(): void
    {
        $this->asAdmin()->get('/kelola/pengurus/99999/tinjau')->assertNotFound();
        $this->delete('/kelola/pengurus/99999')->assertNotFound();
    }
}
