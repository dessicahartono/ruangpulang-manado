@extends('layouts.app')

@section('title', $sponsor ? 'Ubah Sponsor' : 'Tambah Sponsor')

@section('content')
    <x-page-hero>{{ $sponsor ? 'Ubah' : 'Tambah' }}&nbsp;<em>Sponsor</em></x-page-hero>

    <section class="section">
        <div class="container">
            <form class="form" method="POST" action="{{ $action }}" enctype="multipart/form-data">
                @csrf
                @method($method)

                <x-form-field name="name" label="Nama Sponsor" placeholder="Tulis nama sponsor" :value="$sponsor->name ?? ''" />

                <div class="form__row">
                    <label for="photo">Foto</label>
                    @if ($sponsor?->photo)
                        <img class="member-preview sponsor-preview" src="{{ asset($sponsor->photo) }}" alt="Foto saat ini">
                    @endif
                    <input class="field" id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" @required($sponsor === null)>
                    @error('photo')
                        <p class="form__error">{{ $message }}</p>
                    @enderror
                </div>

                <p class="form__hint">Foto atau logo berformat JPG, PNG, atau WEBP, maksimal 2 MB.{{ $sponsor?->photo ? ' Biarkan kosong kalau foto tidak diganti.' : '' }}</p>

                <x-form-field name="description" label="Deskripsi" placeholder="Tulis deskripsi singkat tentang sponsor" :value="$sponsor->description ?? ''" multiline />
                <x-form-field name="description_en" label="Deskripsi (English)" placeholder="Kosongkan untuk diterjemahkan otomatis" :value="$sponsor->description_en ?? ''" multiline :required="false" />

                <p class="form__hint">Kolom English boleh dikosongkan, nanti diterjemahkan otomatis oleh Gemini saat disimpan. Kalau teks Indonesia diubah tetapi kolom English tidak, versi Inggrisnya diterjemahkan ulang.</p>

                <x-form-field name="link" label="Link" type="url" placeholder="https://www.contoh.com" :value="$sponsor->link ?? ''" :required="false" />

                <p class="form__hint">Link boleh dikosongkan kalau sponsor belum punya situs atau media sosial.</p>

                <div class="form__actions">
                    <x-button variant="accent" type="submit">{{ $submitLabel }}</x-button>
                    <x-button variant="outline" :href="$cancelUrl">Batal</x-button>
                </div>
            </form>
        </div>
    </section>
@endsection
