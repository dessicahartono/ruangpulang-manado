<?php

namespace App\Http\Controllers;

use App\Http\Requests\SponsorRequest;
use App\Models\Sponsor;
use App\Repositories\CommunityRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Halaman Kelola Sponsorship. Seluruh route-nya dilindungi middleware admin. */
class ManageSponsorController extends Controller
{
    public function __construct(private CommunityRepository $community) {}

    public function index(): View
    {
        return view('pages.manage.sponsors.index', ['sponsors' => $this->community->sponsors()]);
    }

    public function create(): View
    {
        return view('pages.manage.sponsors.form', [
            'sponsor' => null,
            'action' => route('manage.sponsors.store'),
            'method' => 'POST',
            'cancelUrl' => route('manage.sponsors.index'),
            'submitLabel' => 'Simpan Sponsor',
        ]);
    }

    public function store(SponsorRequest $request): RedirectResponse
    {
        $sponsor = $this->community->createSponsor($request->validated());

        return to_route('manage.sponsors.show', $sponsor)->with('notice', 'Sponsor baru sudah ditambahkan.');
    }

    public function show(Sponsor $sponsor): View
    {
        return view('pages.manage.sponsors.review', ['sponsor' => $sponsor->load('activities')]);
    }

    public function edit(Sponsor $sponsor): View
    {
        return view('pages.manage.sponsors.form', [
            'sponsor' => $sponsor,
            'action' => route('manage.sponsors.update', $sponsor),
            'method' => 'PUT',
            'cancelUrl' => route('manage.sponsors.show', $sponsor),
            'submitLabel' => 'Simpan Perubahan',
        ]);
    }

    public function update(SponsorRequest $request, Sponsor $sponsor): RedirectResponse
    {
        $this->community->updateSponsor($sponsor, $request->validated());

        return to_route('manage.sponsors.show', $sponsor)->with('notice', 'Perubahan sudah disimpan.');
    }

    public function destroy(Sponsor $sponsor): RedirectResponse
    {
        $this->community->deleteSponsor($sponsor);

        return to_route('manage.sponsors.index')->with('notice', 'Sponsor sudah dihapus.');
    }
}
