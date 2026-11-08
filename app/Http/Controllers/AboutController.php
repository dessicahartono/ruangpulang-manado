<?php

namespace App\Http\Controllers;

use App\Repositories\CommunityRepository;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(CommunityRepository $community): View
    {
        return view('pages.about', ['profile' => $community->profile()]);
    }
}
