<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Http\Requests\PostListRequest;
use App\Models\Post;
use App\Repositories\CommunityRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Halaman Kelola Blog. Seluruh route-nya dilindungi middleware admin. */
class ManageBlogController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private CommunityRepository $community) {}

    public function index(PostListRequest $request): View
    {
        return view('pages.manage.index', [
            'posts' => $this->community->managedPosts($request->search(), $request->sort(), self::PER_PAGE),
            'pendingCount' => $this->community->pendingPostCount(),
            'search' => $request->search(),
            'sort' => $request->sort(),
        ]);
    }

    public function create(): View
    {
        return view('pages.manage.form', [
            'post' => null,
            'action' => route('manage.store'),
            'method' => 'POST',
            'cancelUrl' => route('manage.index'),
            'submitLabel' => 'Terbitkan Artikel',
            'hint' => 'Slug dan tanggal publikasi dibuat otomatis oleh sistem saat artikel diterbitkan.',
        ]);
    }

    public function store(ArticleRequest $request): RedirectResponse
    {
        $this->community->publishPost($request->validated());

        return to_route('manage.index')->with('notice', 'Artikel sudah diterbitkan.');
    }

    public function show(string $slug): View
    {
        return view('pages.manage.review', ['post' => $this->findOrFail($slug)]);
    }

    public function edit(string $slug): View
    {
        $post = $this->findOrFail($slug);

        return view('pages.manage.form', [
            'post' => $post,
            'action' => route('manage.update', $post->slug),
            'method' => 'PUT',
            'cancelUrl' => route('manage.index'),
            'submitLabel' => 'Simpan Perubahan',
            'hint' => 'Slug dan tanggal publikasi tidak berubah saat artikel disimpan.',
        ]);
    }

    public function update(ArticleRequest $request, string $slug): RedirectResponse
    {
        $this->community->updatePost($this->findOrFail($slug), $request->validated());

        return to_route('manage.show', $slug)->with('notice', 'Perubahan sudah disimpan.');
    }

    public function approve(string $slug): RedirectResponse
    {
        $this->community->approvePost($this->findOrFail($slug));

        // Fragmen #terbit membuka popup "Artikel Telah Diterbitkan" di halaman Tinjau.
        return to_route('manage.show', $slug)->withFragment('terbit')->with('published', true);
    }

    public function destroy(string $slug): RedirectResponse
    {
        $this->community->deletePost($this->findOrFail($slug));

        return to_route('manage.index')->with('notice', 'Artikel sudah dihapus.');
    }

    private function findOrFail(string $slug): Post
    {
        $post = $this->community->findPost($slug);

        abort_if($post === null, 404);

        return $post;
    }
}
