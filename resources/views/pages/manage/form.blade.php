@extends('layouts.app')

@section('title', $post ? 'Ubah Artikel' : 'Tulis Artikel')

@section('content')
    <x-page-hero>{{ $post ? 'Ubah' : 'Tulis' }}&nbsp;<em>Artikel</em></x-page-hero>

    <section class="section">
        <div class="container">
            @if (session('notice'))
                <x-notice>{{ session('notice') }}</x-notice>
            @endif

            <form class="form" method="POST" action="{{ $action }}">
                @csrf
                @method($method)

                <x-form-field name="title" label="Judul Artikel" placeholder="Tulis judul artikel" :value="$post->title ?? ''" />
                <x-form-field name="author" label="Nama Penulis" placeholder="Tulis namamu" :value="$post->author ?? ''" />


                <x-form-field name="body" label="Isi Artikel" placeholder="Tulis isi artikel di sini" multiline :value="$post->body ?? ''" />

                @isset($isPublicForm)
                    {{-- Jebakan bot: tidak terlihat oleh pengunjung, tetapi biasanya diisi oleh bot. --}}
                    <div class="field-trap" aria-hidden="true">
                        <label for="website">Website</label>
                        <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                    </div>
                @endisset

                <p class="form__hint">{{ $hint }}</p>

                <div class="form__actions">
                    <x-button variant="accent" type="submit">{{ $submitLabel }}</x-button>
                    <x-button variant="outline" :href="$cancelUrl">Batal</x-button>
                </div>
            </form>
        </div>
    </section>
@endsection
