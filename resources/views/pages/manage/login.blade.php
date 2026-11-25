@extends('layouts.app')

@section('title', 'Masuk Admin')

@section('content')
    <x-page-hero>Masuk&nbsp;<em>Admin</em></x-page-hero>

    <section class="section">
        <div class="container">
            <form class="form form--login" method="POST" action="{{ route('manage.login.store') }}">
                @csrf
                <div class="form__row">
                    <label for="username">Username</label>
                    <input class="field" id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="Masukkan username" required autofocus>
                    @error('username')
                        <p class="form__error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form__row">
                    <label for="password">Kata Sandi</label>
                    <input class="field" id="password" name="password" type="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required>
                    @error('password')
                        <p class="form__error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form__actions">
                    <x-button variant="accent" type="submit">Masuk</x-button>
                </div>
            </form>
        </div>
    </section>
@endsection
