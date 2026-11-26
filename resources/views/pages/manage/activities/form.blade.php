@extends('layouts.app')

@section('title', $activity ? 'Ubah Kegiatan' : 'Tambah Kegiatan')

@section('content')
    <x-page-hero>{{ $activity ? 'Ubah' : 'Tambah' }}&nbsp;<em>Kegiatan</em></x-page-hero>

    <section class="section">
        <div class="container">
            <form class="form" method="POST" action="{{ $action }}" enctype="multipart/form-data">
                @csrf
                @method($method)

                <x-form-field name="title" label="Judul Kegiatan" placeholder="Tulis judul kegiatan" :value="$activity->title ?? ''" />
                <x-form-field name="description" label="Deskripsi" placeholder="Tulis deskripsi kegiatan" :value="$activity->description ?? ''" multiline />
                <x-form-field name="link_url" label="Link" placeholder="https://www.instagram.com/p/... atau /blog" :value="$activity->link_url ?? ''" :required="false" />
                <x-form-field name="link_label" label="Teks Link" placeholder="Misalnya Lihat Dokumentasi" :value="$activity->link_label ?? ''" :required="false" />

                <p class="form__hint">Link dan teks link boleh dikosongkan. Link bisa berupa alamat lengkap (diawali https://) atau halaman di situs ini (diawali /, misalnya /blog). Kalau teks link kosong, yang tampil adalah "Kunjungi Situs" untuk situs lain atau "Kunjungi Laman" untuk halaman di situs ini.</p>

                <x-form-field name="title_en" label="Judul Kegiatan (English)" placeholder="Kosongkan untuk diterjemahkan otomatis" :value="$activity->title_en ?? ''" :required="false" />
                <x-form-field name="description_en" label="Deskripsi (English)" placeholder="Kosongkan untuk diterjemahkan otomatis" :value="$activity->description_en ?? ''" multiline :required="false" />
                <x-form-field name="link_label_en" label="Teks Link (English)" placeholder="Kosongkan untuk diterjemahkan otomatis" :value="$activity->link_label_en ?? ''" :required="false" />

                <p class="form__hint">Kolom English boleh dikosongkan, nanti diterjemahkan otomatis oleh Gemini saat disimpan. Kalau teks Indonesia diubah tetapi kolom English tidak, versi Inggrisnya diterjemahkan ulang.</p>

                @if ($sponsors->isNotEmpty())
                    @php($chosen = array_map('intval', (array) old('sponsors', $activity?->sponsors->pluck('id')->all() ?? [])))
                    <fieldset class="form__row sponsor-choice">
                        <legend>Didukung oleh Sponsor</legend>
                        <div class="sponsor-choice__list">
                            @foreach ($sponsors as $sponsor)
                                <label class="sponsor-choice__item">
                                    <input type="checkbox" name="sponsors[]" value="{{ $sponsor->id }}" @checked(in_array($sponsor->id, $chosen, true))>
                                    {{ $sponsor->name }}
                                </label>
                            @endforeach
                        </div>
                        @error('sponsors.*')
                            <p class="form__error">{{ $message }}</p>
                        @enderror
                    </fieldset>
                    <p class="form__hint">Boleh tidak dicentang kalau kegiatan ini tidak didukung sponsor.</p>
                @endif

                @if ($activity?->media->isNotEmpty())
                    <fieldset class="form__row media-current">
                        <legend>Media Saat Ini</legend>
                        <div class="media-current__list">
                            @foreach ($activity->media as $item)
                                <label class="media-current__item">
                                    @if ($item->isVideo())
                                        <video src="{{ asset($item->path) }}" preload="metadata" muted playsinline></video>
                                    @else
                                        <img src="{{ asset($item->path) }}" alt="{{ $item->alt }}">
                                    @endif
                                    <span>
                                        <input type="checkbox" name="remove_media[]" value="{{ $item->id }}" @checked(in_array($item->id, (array) old('remove_media', [])))>
                                        Hapus {{ $item->isVideo() ? 'video' : 'foto' }} ini
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endif

                <div class="form__row">
                    <label for="media">{{ $activity ? 'Tambah Media' : 'Foto atau Video' }}</label>
                    <input class="field" id="media" name="media[]" type="file" multiple
                           accept="image/jpeg,image/png,image/webp,video/mp4,video/webm" @required($activity === null)
                           data-max-video-seconds="{{ App\Http\Requests\ActivityRequest::VIDEO_MAX_SECONDS }}"
                           data-shrink-photos="{{ App\Http\Requests\ActivityRequest::IMAGE_MAX_KB }}">
                    <p class="form__error" data-media-error hidden></p>
                    @error('media')
                        <p class="form__error">{{ $message }}</p>
                    @enderror
                    @foreach ($errors->get('media.*') as $messages)
                        <p class="form__error">{{ $messages[0] }}</p>
                    @endforeach
                </div>

                <p class="form__hint">
                    Maksimal {{ App\Models\Activity::MAX_MEDIA }} media per kegiatan. Foto JPG, PNG, atau WEBP maksimal 2 MB, foto JPG yang lebih besar dikecilkan otomatis. Video MP4 atau WEBM singkat, maksimal {{ App\Http\Requests\ActivityRequest::VIDEO_MAX_SECONDS }} detik dan 20 MB, sebaiknya 720p. Pilih beberapa file sekaligus dengan menahan Ctrl atau Cmd.
                    @if ($activity)
                        Biarkan kosong kalau tidak ada media baru.
                    @endif
                </p>

                <div class="form__actions">
                    <x-button variant="accent" type="submit">{{ $submitLabel }}</x-button>
                    <x-button variant="outline" :href="$cancelUrl">Batal</x-button>
                </div>
            </form>
        </div>
    </section>
@endsection
