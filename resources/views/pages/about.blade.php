@extends('layouts.app')

@section('title', 'Tentang')
@section('description', 'Sejarah, tujuan, visi, dan misi komunitas Ruang Pulang di Manado.')

@section('content')
    <x-page-hero>Tentang <em>Ruang Pulang</em></x-page-hero>

    {{-- Desktop: dua kolom. HP: akordion, sesuai desain. Isinya sama dan berasal dari data yang sama. --}}
    <div class="only-wide">
        <section class="section">
            <div class="container split">
                <div class="prose">
                    <h2>Sejarah dan Latar Belakang</h2>
                    <x-paragraphs :items="$profile->history" />
                </div>
                <div class="prose">
                    <h2>Tujuan Terbentuknya Ruang Pulang</h2>
                    <x-paragraphs :items="$profile->purpose" />
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container split">
                <div class="prose">
                    <h2>Visi</h2>
                    <p>{{ $profile->vision }}</p>
                </div>
                <div class="prose">
                    <h2>Misi</h2>
                    <ol class="mission-list">
                        @foreach ($profile->missions as $mission)
                            <li>{{ $mission }}</li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </section>
    </div>

    <section class="section only-narrow">
        <div class="container acc-list">
            <details class="acc">
                <summary class="acc__sum">Sejarah dan Latar Belakang</summary>
                <div class="prose"><x-paragraphs :items="$profile->history" /></div>
            </details>
            <details class="acc">
                <summary class="acc__sum">Tujuan Terbentuknya Ruang Pulang</summary>
                <div class="prose"><x-paragraphs :items="$profile->purpose" /></div>
            </details>
            <details class="acc">
                <summary class="acc__sum">Visi dan Misi</summary>
                <div class="prose">
                    <h3>Visi</h3>
                    <p>{{ $profile->vision }}</p>
                    <h3>Misi</h3>
                    <ol class="mission-list">
                        @foreach ($profile->missions as $mission)
                            <li>{{ $mission }}</li>
                        @endforeach
                    </ol>
                </div>
            </details>
        </div>
    </section>
@endsection
