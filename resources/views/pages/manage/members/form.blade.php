@extends('layouts.app')

@section('title', $member ? 'Ubah Pengurus' : 'Tambah Pengurus')

@section('content')
    <x-page-hero>{{ $member ? 'Ubah' : 'Tambah' }}&nbsp;<em>Pengurus</em></x-page-hero>

    <section class="section">
        <div class="container">
            <form class="form" method="POST" action="{{ $action }}" enctype="multipart/form-data">
                @csrf
                @method($method)

                <div class="form__row">
                    <label for="division_id">Divisi</label>
                    <select class="select" id="division_id" name="division_id" required>
                        <option value="" disabled @selected(! old('division_id', $member->division_id ?? null))>Pilih divisi</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->id }}" @selected((int) old('division_id', $member->division_id ?? 0) === $division->id)>{{ $division->name }}</option>
                        @endforeach
                    </select>
                    @error('division_id')
                        <p class="form__error">{{ $message }}</p>
                    @enderror
                </div>

                <x-form-field name="full_name" label="Nama Lengkap" placeholder="Tulis nama lengkap" :value="$member->full_name ?? ''" />
                <x-form-field name="nickname" label="Nama Panggilan" placeholder="Tulis nama panggilan" :value="$member->nickname ?? ''" />
                <x-form-field name="instagram" label="Link Instagram" type="url" placeholder="https://www.instagram.com/namapengguna/" :value="$member->instagram ?? ''" :required="$member === null" />

                <div class="form__row">
                    <label for="photo">Foto</label>
                    @if ($member?->photo)
                        <img class="member-preview" src="{{ asset($member->photo) }}" alt="Foto saat ini">
                    @endif
                    <input class="field" id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" @required($member === null)>
                    @error('photo')
                        <p class="form__error">{{ $message }}</p>
                    @enderror
                </div>

                <p class="form__hint">Foto berformat JPG, PNG, atau WEBP, maksimal 2 MB.{{ $member ? ' Biarkan kosong kalau foto tidak diganti.' : '' }}</p>

                <div class="form__actions">
                    <x-button variant="accent" type="submit">{{ $submitLabel }}</x-button>
                    <x-button variant="outline" :href="$cancelUrl">Batal</x-button>
                </div>
            </form>
        </div>
    </section>
@endsection
