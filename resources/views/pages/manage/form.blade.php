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


                @if ($richEditor ?? false)
                    {{-- TinyMCE menggantikan kotak ini. Isinya HTML, dan dibersihkan lagi di server sebelum disimpan. --}}
                    <div class="form__row">
                        <label for="body">Isi Artikel</label>
                        <textarea class="textarea" id="body" name="body" data-rich-editor>{{ old('body', $post?->editorHtml() ?? '') }}</textarea>
                        @error('body')
                            <p class="form__error">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <x-form-field name="body" label="Isi Artikel" placeholder="Tulis isi artikel di sini" multiline :value="$post->body ?? ''" />
                @endif

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

@if ($richEditor ?? false)
    @push('scripts')
        {{-- TinyMCE versi self-hosted dari jsDelivr, lisensi GPL, jadi tidak butuh API key. --}}
        <script src="https://cdn.jsdelivr.net/npm/tinymce@7.6.0/tinymce.min.js" referrerpolicy="origin"></script>
        <script>
            tinymce.init({
                selector: 'textarea[data-rich-editor]',
                license_key: 'gpl',
                promotion: false,
                branding: false,
                menubar: false,
                height: 520,
                plugins: 'lists link autolink wordcount',
                toolbar: 'undo redo | blocks | bold italic underline strikethrough | bullist numlist | blockquote link | removeformat',
                block_formats: 'Paragraf=p; Subjudul=h2; Subjudul kecil=h3',
                // Sama dengan App\Support\ArticleHtml::ALLOWED, supaya yang terlihat di editor sama dengan yang tersimpan.
                valid_elements: 'p,br,h2,h3,strong/b,em/i,u,s,ul,ol,li,blockquote,a[href|title|target|rel]',
                link_default_target: '_blank',
                link_assume_external_targets: 'https',
                content_style: "body { font-family: Inter, system-ui, sans-serif; font-size: 17px; line-height: 1.7; color: #1b2340; } h2 { font-size: 1.5em; } h3 { font-size: 1.2em; } blockquote { margin-left: 0; padding-left: 16px; border-left: 3px solid #d9cdb8; color: #555; }",
            });
        </script>
    @endpush
@endif
