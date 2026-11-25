<?php

namespace App\Http\Controllers;

use App\Repositories\CommunityRepository;
use Illuminate\View\View;

class SponsorshipController extends Controller
{
    public function __invoke(CommunityRepository $community): View
    {
        return view('pages.sponsorship', ['sponsors' => $community->sponsors()]);
    }
}
