<?php

namespace App\Http\Controllers;

use App\Support\AdminTwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Kunci halaman Kelola. Hanya ada satu admin dan tidak ada akun pengguna, jadi
 * yang dicek hanya satu username dan satu kata sandi. Keduanya diatur di .env,
 * dan kata sandinya hanya disimpan sebagai hash.
 *
 * Kalau verifikasi dua langkah aktif, kata sandi yang benar belum cukup: admin
 * masih harus memasukkan kode 6 angka dari aplikasi authenticator.
 */
class AdminSessionController extends Controller
{
    /** Batas waktu memasukkan kode setelah kata sandi benar. */
    private const CODE_TIMEOUT_SECONDS = 300;

    private const PENDING_KEY = 'admin_2fa_pending_at';

    public function __construct(private AdminTwoFactor $twoFactor) {}

    public function create(Request $request): View|RedirectResponse
    {
        return $request->session()->get('is_admin') === true
            ? to_route('manage.dashboard')
            : view('pages.manage.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate(
            ['username' => ['required', 'string', 'max:100'], 'password' => ['required', 'string', 'max:200']],
            ['username.required' => 'Username wajib diisi.', 'password.required' => 'Kata Sandi wajib diisi.'],
        );

        $expected = $this->normalizeUsername((string) config('community.admin_username'));
        $usernameMatches = $expected !== '' && hash_equals($expected, $this->normalizeUsername($credentials['username']));

        // Kata sandi tetap dicek walau username salah, supaya lama prosesnya sama dan tidak membocorkan username.
        $hash = config('community.admin_password_hash');
        $passwordMatches = filled($hash) && Hash::check($credentials['password'], $hash);

        if (! $usernameMatches || ! $passwordMatches) {
            return back()->onlyInput('username')->withErrors(['password' => 'Username atau kata sandi salah.']);
        }

        // ID sesi diganti supaya sesi lama tidak bisa dipakai untuk masuk sebagai admin.
        $request->session()->regenerate();

        if ($this->twoFactor->enabled()) {
            $request->session()->put(self::PENDING_KEY, now()->timestamp);

            return to_route('manage.login.code');
        }

        return $this->signIn($request);
    }

    public function codeForm(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('is_admin') === true) {
            return to_route('manage.dashboard');
        }

        return $this->codeIsPending($request)
            ? view('pages.manage.login-code')
            : to_route('manage.login');
    }

    public function verifyCode(Request $request): RedirectResponse
    {
        if (! $this->codeIsPending($request)) {
            $request->session()->forget(self::PENDING_KEY);

            return to_route('manage.login')->withErrors(['password' => 'Waktu memasukkan kode sudah habis. Masukkan kata sandi lagi.']);
        }

        $code = $request->validate(
            ['code' => ['required', 'string', 'max:10']],
            ['code.required' => 'Kode wajib diisi.'],
        )['code'];

        if (! $this->twoFactor->verify(preg_replace('/\s+/', '', $code))) {
            return back()->withErrors(['code' => 'Kode salah atau sudah dipakai. Masukkan kode terbaru dari aplikasi authenticator.']);
        }

        $request->session()->forget(self::PENDING_KEY);
        $request->session()->regenerate();

        return $this->signIn($request);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget(['is_admin', self::PENDING_KEY]);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return to_route('home');
    }

    /** "@DessicaHartono" dan "dessicahartono" dianggap sama. */
    private function normalizeUsername(string $username): string
    {
        return mb_strtolower(ltrim(trim($username), '@'));
    }

    private function signIn(Request $request): RedirectResponse
    {
        $request->session()->put('is_admin', true);

        return redirect()->intended(route('manage.dashboard'));
    }

    private function codeIsPending(Request $request): bool
    {
        $startedAt = $request->session()->get(self::PENDING_KEY);

        return is_int($startedAt) && now()->timestamp - $startedAt <= self::CODE_TIMEOUT_SECONDS;
    }
}
