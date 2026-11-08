<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /** Aman dijalankan ulang: artikel yang sudah ada tidak ditimpa. */
    public function run(): void
    {
        foreach (require resource_path('data/posts.php') as $post) {
            Post::firstOrCreate(['slug' => $post['slug']], [
                'title' => $post['title'],
                'author' => $post['author'],
                'status' => Post::STATUS_PUBLISHED,
                'excerpt' => $post['excerpt'],
                'body' => implode("\n\n", $post['content']),
                'published_at' => $post['published_at'],
            ]);
        }
    }
}
