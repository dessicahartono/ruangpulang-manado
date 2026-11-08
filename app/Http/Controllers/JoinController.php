<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class JoinController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.join');
    }
}
