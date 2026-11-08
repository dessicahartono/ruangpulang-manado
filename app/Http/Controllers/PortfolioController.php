<?php

namespace App\Http\Controllers;

use App\Repositories\CommunityRepository;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function __invoke(CommunityRepository $community): View
    {
        return view('pages.portfolio', ['portfolios' => $community->portfolios()]);
    }
}
