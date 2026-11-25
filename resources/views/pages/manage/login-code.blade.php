@extends('layouts.app')

@section('title', 'Kode Verifikasi')

@section('content')
    <x-page-hero>Kode&nbsp;<em>Verifikasi</em></x-page-hero>

    <section class="section">
        <div class="container">
            <form class="form form--login" method="POST" action="{{ route('manage.login.code.store') }}">
                @csrf
                <div class="form__row">
                    <label for="code">Kode dari Aplikasi Authenticator</label>
                    <input class="field field--code" id="code" name="code" type="text" inputmode="numeric" pattern="[0-9 ]{6,7}" maxlength="7"
                           autocomplete="one-time-code" placeholder="123456" required autofocus>
                    @error('code')
                        <p class="form__error">{{ $message }}</p>
                    @enderror
                </div>
                <p class="form__hint">Buka aplikasi authenticator di HP, lalu masukkan 6 angka untuk Ruang Pulang. Kodenya berganti setiap 30 detik. Halaman ini berlaku 5 menit.</p>
                <div class="form__actions">
                    <x-button variant="accent" type="submit">Verifikasi</x-button>
                    <x-button variant="outline" :href="route('manage.login')">Kembali</x-button>
                </div>
            </form>
        </div>
    </section>
@endsection
