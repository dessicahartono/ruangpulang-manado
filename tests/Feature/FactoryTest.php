<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityMedia;
use App\Models\Division;
use App\Models\Member;
use App\Models\Post;
use App\Models\Sponsor;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_factory_makes_published_and_pending_articles(): void
    {
        $published = Post::factory()->create();
        $pending = Post::factory()->pending()->create();

        $this->assertSame(Post::STATUS_PUBLISHED, $published->status);
        $this->assertNotNull($published->published_at);
        $this->assertStringEndsWith('Isi artikel ini adalah data contoh.', $published->body);
        $this->assertTrue($pending->isPending());
        $this->assertNull($pending->published_at);

        $this->get("/blog/{$published->slug}")->assertOk()->assertSee($published->title);
        $this->get("/blog/{$pending->slug}")->assertNotFound();
    }

    public function test_member_factory_creates_its_division_through_the_relationship(): void
    {
        $member = Member::factory()->create();
        $incomplete = Member::factory()->for($member->division)->withoutInstagram()->create();

        $this->assertInstanceOf(Division::class, $member->division);
        $this->assertSame(2, $member->division->members()->count());
        $this->assertSame(['foto', 'Instagram'], $incomplete->missingData());
    }

    public function test_activity_factory_respects_the_media_limit(): void
    {
        $activity = Activity::factory()->withMedia(5)->withLink()->create();

        $this->assertCount(Activity::MAX_MEDIA, $activity->media);
        $this->assertSame([0, 1, 2], $activity->media->pluck('position')->all());
        $this->assertFileExists(public_path($activity->media->first()->path));
        $this->assertSame('/blog', $activity->link_url);

        $video = ActivityMedia::factory()->video()->for($activity)->make();
        $this->assertTrue($video->isVideo());
    }

    public function test_sponsor_factory_and_many_to_many_relationship(): void
    {
        $sponsor = Sponsor::factory()->create();
        $noLink = Sponsor::factory()->withoutLink()->create();
        $activities = Activity::factory()->count(2)->withMedia()->create();

        $sponsor->activities()->attach($activities->pluck('id'));

        $this->assertStringStartsWith('https://', $sponsor->link);
        $this->assertSame(['foto', 'link'], $noLink->missingData());
        $this->assertCount(2, $sponsor->fresh()->activities);
        $this->assertTrue($activities->first()->sponsors->contains($sponsor));
    }

    public function test_demo_seeder_fills_enough_data_for_pagination_and_relationships(): void
    {
        $this->seed();
        $this->seed(DemoSeeder::class);

        $this->assertSame(17 + 25, Post::published()->count());
        $this->assertSame(3, Post::where('status', Post::STATUS_PENDING)->count());
        // Tiga kegiatan contoh, dengan 1, 2, dan 3 foto, semuanya terhubung ke sponsor contoh.
        $demo = Activity::where('position', '>=', 100)->withCount('media')->has('sponsors')->get();
        $this->assertSame([1, 2, 3], $demo->pluck('media_count')->sort()->values()->all());

        // 42 artikel terbit dibagi 5 per halaman menjadi 9 halaman.
        $this->get('/blog?page=9')->assertOk()->assertSee('class="pager"', false);
        $this->withSession(['is_admin' => true])->get('/kelola/pengurus')->assertSee('Total 35 data pengurus');
        $this->get('/kegiatan')->assertSee('Didukung oleh');
    }
}
