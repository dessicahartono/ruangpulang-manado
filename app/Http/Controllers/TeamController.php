<?php

namespace App\Http\Controllers;

use App\Repositories\CommunityRepository;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function __invoke(CommunityRepository $community): View
    {
        return view('pages.team', ['groups' => $community->teamGroups()]);
    }
}
