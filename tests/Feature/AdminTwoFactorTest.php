<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Mockery;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AdminTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'community.admin_username' => 'dessicahartono',
            'community.admin_password_hash' => Hash::make('kata-sandi-benar'),
            'community.admin_totp_secret' => self::SECRET,
        ]);
        Cache::flush();
        RateLimiter::clear('admin-login');
    }

    private function currentCode(): string
    {
        return (new Google2FA)->getCurrentOtp(self::SECRET);
    }

    private function passPassword(): void
    {
        $this->post('/kelola/masuk', ['username' => 'dessicahartono', 'password' => 'kata-sandi-benar'])->assertRedirect('/kelola/masuk/kode');
    }

    public function test_correct_password_alone_is_not_enough(): void
    {
        $this->passPassword();

        $this->get('/kelola')->assertRedirect('/kelola/masuk');
        $this->get('/kelola/masuk/kode')->assertOk()->assertSee('Kode dari Aplikasi Authenticator');
    }

    public function test_correct_code_signs_the_admin_in(): void
    {
        $this->get('/kelola/sponsorship');
        $this->passPassword();

        $this->post('/kelola/masuk/kode', ['code' => $this->currentCode()])->assertRedirect('/kelola/sponsorship');
        $this->get('/kelola')->assertOk();
    }

    public function test_wrong_code_is_rejected(): void
    {
        $this->passPassword();

        $this->post('/kelola/masuk/kode', ['code' => '000000'])->assertSessionHasErrors('code');
        $this->post('/kelola/masuk/kode', ['code' => 'abc'])->assertSessionHasErrors('code');
        $this->get('/kelola')->assertRedirect('/kelola/masuk');
    }

    public function test_code_with_a_space_from_the_app_is_accepted(): void
    {
        $this->passPassword();
        $code = $this->currentCode();

        $this->post('/kelola/masuk/kode', ['code' => substr($code, 0, 3).' '.substr($code, 3)])->assertRedirect('/kelola');
    }

    public function test_a_used_code_cannot_be_used_again(): void
    {
        $code = $this->currentCode();
        $this->passPassword();
        $this->post('/kelola/masuk/kode', ['code' => $code])->assertRedirect('/kelola');
        $this->post('/kelola/keluar');

        $this->passPassword();
        $this->post('/kelola/masuk/kode', ['code' => $code])->assertSessionHasErrors('code');
        $this->get('/kelola')->assertRedirect('/kelola/masuk');
    }

    public function test_code_page_needs_the_password_step_first(): void
    {
        $this->get('/kelola/masuk/kode')->assertRedirect('/kelola/masuk');
        $this->post('/kelola/masuk/kode', ['code' => $this->currentCode()])->assertRedirect('/kelola/masuk');
        $this->get('/kelola')->assertRedirect('/kelola/masuk');
    }

    public function test_code_step_expires_after_five_minutes(): void
    {
        $this->passPassword();
        $this->travel(6)->minutes();

        $this->post('/kelola/masuk/kode', ['code' => $this->currentCode()])
            ->assertRedirect('/kelola/masuk')
            ->assertSessionHasErrors('password');
    }

    public function test_code_attempts_are_rate_limited(): void
    {
        $this->passPassword();
        RateLimiter::clear('admin-login');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/kelola/masuk/kode', ['code' => '000000']);
        }

        $this->post('/kelola/masuk/kode', ['code' => $this->currentCode()])->assertTooManyRequests();
    }

    public function test_without_a_secret_login_works_with_the_password_only(): void
    {
        config(['community.admin_totp_secret' => null]);

        $this->post('/kelola/masuk', ['username' => 'dessicahartono', 'password' => 'kata-sandi-benar'])->assertRedirect('/kelola');
        $this->get('/kelola')->assertOk();
    }

    public function test_command_saves_the_secret_only_after_a_matching_code(): void
    {
        $env = $this->temporaryEnv("APP_NAME=Ruang\nADMIN_TOTP_SECRET=\n");
        $google2fa = Mockery::mock(Google2FA::class)->makePartial();
        $google2fa->shouldReceive('generateSecretKey')->andReturn(self::SECRET);
        $this->app->instance(Google2FA::class, $google2fa);

        $this->artisan('admin:2fa')
            ->expectsQuestion('Masukkan kode 6 angka dari aplikasi untuk memastikan', '000000')
            ->expectsQuestion('Masukkan kode 6 angka dari aplikasi untuk memastikan', $this->currentCode())
            ->assertSuccessful();

        $this->assertStringContainsString('ADMIN_TOTP_SECRET='.self::SECRET, file_get_contents($env));
    }

    public function test_command_changes_nothing_after_three_wrong_codes(): void
    {
        $env = $this->temporaryEnv("ADMIN_TOTP_SECRET=\n");

        $this->artisan('admin:2fa')
            ->expectsQuestion('Masukkan kode 6 angka dari aplikasi untuk memastikan', '000000')
            ->expectsQuestion('Masukkan kode 6 angka dari aplikasi untuk memastikan', '111111')
            ->expectsQuestion('Masukkan kode 6 angka dari aplikasi untuk memastikan', '222222')
            ->assertFailed();

        $this->assertSame("ADMIN_TOTP_SECRET=\n", file_get_contents($env));
    }

    public function test_command_can_disable_two_factor(): void
    {
        $env = $this->temporaryEnv('ADMIN_TOTP_SECRET='.self::SECRET."\n");

        $this->artisan('admin:2fa', ['--disable' => true])
            ->expectsConfirmation('Matikan verifikasi dua langkah? Login akan kembali cukup dengan kata sandi.', 'yes')
            ->assertSuccessful();

        $this->assertSame("ADMIN_TOTP_SECRET=\n", file_get_contents($env));
    }

    /** Perintah menulis ke .env, jadi tes memakai salinan sementara supaya .env asli tidak tersentuh. */
    private function temporaryEnv(string $content): string
    {
        $dir = sys_get_temp_dir().'/rp-env-'.uniqid();
        mkdir($dir);
        file_put_contents($dir.'/.env', $content);
        $this->app->useEnvironmentPath($dir);

        return $dir.'/.env';
    }
}
