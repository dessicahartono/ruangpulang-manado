<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Post;
use Illuminate\View\View;

/** Halaman pertama setelah admin masuk. */
class ManageDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.manage.dashboard', [
            'memberCount' => Member::count(),
            'postCount' => Post::count(),
            'pendingCount' => Post::where('status', Post::STATUS_PENDING)->count(),
        ]);
    }
}
