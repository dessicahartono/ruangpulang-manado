<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivityRequest;
use App\Models\Activity;
use App\Repositories\CommunityRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Halaman Kelola Kegiatan. Seluruh route-nya dilindungi middleware admin. */
class ManageActivityController extends Controller
{
    public function __construct(private CommunityRepository $community) {}

    public function index(): View
    {
        return view('pages.manage.activities.index', ['activities' => $this->community->activities()]);
    }

    public function create(): View
    {
        return view('pages.manage.activities.form', [
            'activity' => null,
            'sponsors' => $this->community->sponsors(),
            'action' => route('manage.activities.store'),
            'method' => 'POST',
            'cancelUrl' => route('manage.activities.index'),
            'submitLabel' => 'Simpan Kegiatan',
        ]);
    }

    public function store(ActivityRequest $request): RedirectResponse
    {
        $this->community->createActivity($request->validated(), $request->newMedia());

        return to_route('manage.activities.index')->with('notice', 'Kegiatan baru sudah ditambahkan.');
    }

    public function show(Activity $activity): View
    {
        return view('pages.manage.activities.review', ['activity' => $activity->load(['media', 'sponsors'])]);
    }

    public function edit(Activity $activity): View
    {
        return view('pages.manage.activities.form', [
            'activity' => $activity->load(['media', 'sponsors']),
            'sponsors' => $this->community->sponsors(),
            'action' => route('manage.activities.update', $activity),
            'method' => 'PUT',
            'cancelUrl' => route('manage.activities.show', $activity),
            'submitLabel' => 'Simpan Perubahan',
        ]);
    }

    public function update(ActivityRequest $request, Activity $activity): RedirectResponse
    {
        $this->community->updateActivity($activity, $request->validated(), $request->newMedia(), $request->removedMediaIds());

        return to_route('manage.activities.show', $activity)->with('notice', 'Perubahan sudah disimpan.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $this->community->deleteActivity($activity);

        return to_route('manage.activities.index')->with('notice', 'Kegiatan sudah dihapus.');
    }
}
