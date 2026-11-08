<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostListRequest;
use App\Repositories\CommunityRepository;
use Illuminate\View\View;

class BlogController extends Controller
{
    private const PER_PAGE = 5;

    public function __construct(private CommunityRepository $community) {}

    public function index(PostListRequest $request): View
    {
        return view('pages.blog.index', [
            'posts' => $this->community->publishedPosts($request->search(), $request->sort(), self::PER_PAGE),
            'search' => $request->search(),
            'sort' => $request->sort(),
        ]);
    }

    public function show(string $slug): View
    {
        $post = $this->community->findPublishedPost($slug);

        abort_if($post === null, 404);

        return view('pages.blog.show', ['post' => $post]);
    }
}
