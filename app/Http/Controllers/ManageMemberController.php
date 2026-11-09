<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberListRequest;
use App\Http\Requests\MemberRequest;
use App\Models\Member;
use App\Repositories\CommunityRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Halaman Kelola Profil Pengurus. Seluruh route-nya dilindungi middleware admin. */
class ManageMemberController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private CommunityRepository $community) {}

    public function index(MemberListRequest $request): View
    {
        return view('pages.manage.members.index', [
            'members' => $this->community->members($request->search(), $request->divisionId(), $request->sort(), self::PER_PAGE),
            'divisions' => $this->community->divisions(),
            'search' => $request->search(),
            'divisionId' => $request->divisionId(),
            'sort' => $request->sort(),
        ]);
    }

    public function create(): View
    {
        return view('pages.manage.members.form', [
            'member' => null,
            'divisions' => $this->community->divisions(),
            'action' => route('manage.members.store'),
            'method' => 'POST',
            'cancelUrl' => route('manage.members.index'),
            'submitLabel' => 'Simpan Pengurus',
        ]);
    }

    public function store(MemberRequest $request): RedirectResponse
    {
        $this->community->createMember($request->validated());

        return to_route('manage.members.index')->with('notice', 'Pengurus baru sudah ditambahkan.');
    }

    public function show(Member $member): View
    {
        return view('pages.manage.members.review', ['member' => $member->load('division')]);
    }

    public function edit(Member $member): View
    {
        return view('pages.manage.members.form', [
            'member' => $member,
            'divisions' => $this->community->divisions(),
            'action' => route('manage.members.update', $member),
            'method' => 'PUT',
            'cancelUrl' => route('manage.members.show', $member),
            'submitLabel' => 'Simpan Perubahan',
        ]);
    }

    public function update(MemberRequest $request, Member $member): RedirectResponse
    {
        $this->community->updateMember($member, $request->validated());

        return to_route('manage.members.show', $member)->with('notice', 'Perubahan sudah disimpan.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        $this->community->deleteMember($member);

        return to_route('manage.members.index')->with('notice', 'Pengurus sudah dihapus.');
    }
}
