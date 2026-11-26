@extends('layouts.app')

@section('title', $post ? 'Ubah Karya' : (isset($isPublicForm) ? __('Tulis Karyamu') : 'Tulis Karya'))

@section('content')
    <x-page-hero>{!! $post ? 'Ubah&nbsp;<em>Karya</em>' : (isset($isPublicForm) ? __('Tulis&nbsp;<em>Karyamu</em>') : 'Tulis&nbsp;<em>Karya</em>') !!}</x-page-hero>

    <section class="section">
        <div class="container">
            @if (session('notice'))
                <x-notice>{{ __(session('notice')) }}</x-notice>
            @endif

            <form class="form" method="POST" action="{{ $action }}">
                @csrf
                @method($method)

                <x-form-field name="title" :label="__('Judul Karya')" :placeholder="__('Tulis judul karya')" :value="$post->title ?? ''" />
                <x-form-field name="author" :label="__('Nama Penulis')" :placeholder="__('Tulis namamu')" :value="$post->author ?? ''" />


                @if ($richEditor ?? false)
                    {{-- TinyMCE menggantikan kotak ini. Isinya HTML, dan dibersihkan lagi di server sebelum disimpan. --}}
                    <div class="form__row">
                        <label for="body">{{ __('Isi Karya') }}</label>
                        <textarea class="textarea" id="body" name="body" placeholder="{{ __('Mulai Menulis') }}" data-rich-editor>{{ old('body', $post?->editorHtml() ?? '') }}</textarea>
                        @error('body')
                            <p class="form__error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Versi Inggris hanya untuk admin. Kiriman pengunjung diterjemahkan saat disetujui. --}}
                    @unless (isset($isPublicForm))
                    <x-form-field name="title_en" label="Judul Karya (English)" placeholder="Kosongkan untuk diterjemahkan otomatis" :value="$post->title_en ?? ''" :required="false" />

                    <div class="form__row">
                        <label for="body_en">Isi Karya (English)</label>
                        <textarea class="textarea" id="body_en" name="body_en" data-rich-editor>{{ old('body_en', $post?->body_en ? $post->editorHtml('body_en') : '') }}</textarea>
                        @error('body_en')
                            <p class="form__error">{{ $message }}</p>
                        @enderror
                    </div>

                    <p class="form__hint">Kolom English boleh dikosongkan, nanti diterjemahkan otomatis oleh Gemini saat disimpan. Kalau teks Indonesia diubah tetapi kolom English tidak, versi Inggrisnya diterjemahkan ulang.</p>
                    @endunless
                @else
                    <x-form-field name="body" :label="__('Isi Karya')" :placeholder="__('Mulai Menulis')" multiline :value="$post->body ?? ''" />
                @endif

                @isset($isPublicForm)
                    {{-- Jebakan bot: tidak terlihat oleh pengunjung, tetapi biasanya diisi oleh bot. --}}
                    <div class="field-trap" aria-hidden="true">
                        <label for="website">Website</label>
                        <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                    </div>
                @endisset

                <p class="form__hint">{{ __($hint) }}</p>

                <div class="form__actions">
                    <x-button variant="accent" type="submit">{{ __($submitLabel) }}</x-button>
                    <x-button variant="outline" :href="$cancelUrl">{{ __('Batal') }}</x-button>
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
                block_formats: @json(__('Paragraf').'=p; '.__('Subjudul').'=h2; '.__('Subjudul kecil').'=h3'),
                // Sama dengan App\Support\ArticleHtml::ALLOWED, supaya yang terlihat di editor sama dengan yang tersimpan.
                valid_elements: 'p,br,h2,h3,strong/b,em/i,u,s,ul,ol,li,blockquote,a[href|title|target|rel]',
                link_default_target: '_blank',
                link_assume_external_targets: 'https',
                content_style: "body { font-family: Inter, system-ui, sans-serif; font-size: 17px; line-height: 1.7; color: #1b2340; } h2 { font-size: 1.5em; } h3 { font-size: 1.2em; } blockquote { margin-left: 0; padding-left: 16px; border-left: 3px solid #d9cdb8; color: #555; }",
            });
        </script>
    @endpush
@endif
