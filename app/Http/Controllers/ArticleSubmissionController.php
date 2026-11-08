<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Repositories\CommunityRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ArticleSubmissionController extends Controller
{
    private const THANKS = 'Terima kasih, artikelmu sudah kami terima dan akan tampil setelah disetujui admin.';

    public function create(): View
    {
        return view('pages.manage.form', [
            'post' => null,
            'action' => route('articles.store'),
            'method' => 'POST',
            'cancelUrl' => route('blog.index'),
            'submitLabel' => 'Kirim Artikel',
            'hint' => 'Artikelmu akan tampil setelah disetujui admin. Jangan tulis data pribadi orang lain.',
            'isPublicForm' => true,
        ]);
    }

    public function store(ArticleRequest $request, CommunityRepository $community): RedirectResponse
    {
        // Kolom tersembunyi ini hanya diisi oleh bot, jadi kirimannya diam-diam dibuang.
        if ($request->filled('website')) {
            return to_route('articles.create')->with('notice', self::THANKS);
        }

        $community->submitPost($request->validated());

        return to_route('articles.create')->with('notice', self::THANKS);
    }
}
