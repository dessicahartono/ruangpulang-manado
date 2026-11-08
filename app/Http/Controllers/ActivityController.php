<?php

namespace App\Http\Controllers;

use App\Repositories\CommunityRepository;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function __invoke(CommunityRepository $community): View
    {
        return view('pages.activity', ['activities' => $community->activities()]);
    }
}
