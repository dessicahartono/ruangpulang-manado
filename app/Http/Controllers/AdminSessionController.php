<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Kunci halaman Kelola. Hanya ada satu admin dan tidak ada akun pengguna, jadi
 * yang dicek hanya satu kata sandi. Hash-nya disimpan di .env, bukan di kode.
 */
class AdminSessionController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        return $request->session()->get('is_admin') === true
            ? to_route('manage.index')
            : view('pages.manage.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $password = $request->validate(
            ['password' => ['required', 'string', 'max:200']],
            ['password.required' => 'Kata Sandi wajib diisi.'],
        )['password'];

        $hash = config('community.admin_password_hash');

        if (blank($hash) || ! Hash::check($password, $hash)) {
            return back()->withErrors(['password' => 'Kata sandi salah.']);
        }

        // ID sesi diganti supaya sesi lama tidak bisa dipakai untuk masuk sebagai admin.
        $request->session()->regenerate();
        $request->session()->put('is_admin', true);

        return redirect()->intended(route('manage.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('is_admin');
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return to_route('home');
    }
}
