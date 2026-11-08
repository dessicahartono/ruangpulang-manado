<?php

namespace App\Http\Controllers;

use App\Repositories\CommunityRepository;
use Illuminate\View\View;

class PortofolioTeamController extends Controller
{
    public function __invoke(CommunityRepository $community): View
    {
        return view('pages.portofolioteam', ['groups' => $community->portofolioTeam()]);
    }
}
