@extends('layouts.app')

@section('title', 'Pengurus')
@section('description', 'Para pendiri dan pengurus komunitas Ruang Pulang.')

@section('content')
    <x-page-hero>Orang-orang di Balik <em>Ruang Pulang</em></x-page-hero>

    <section class="section">
        <div class="container">
            {{-- Desktop: semua kelompok terbuka. HP: akordion, sesuai desain. --}}
            <div class="only-wide">
                @foreach ($groups as $group)
                    <div class="team-group">
                        <h2 class="h3-title">{{ $group->name }}</h2>
                        <x-team-group :group="$group" />
                    </div>
                @endforeach
            </div>

            <div class="acc-list only-narrow">
                @foreach ($groups as $group)
                    <details class="acc" @if ($loop->first) open @endif>
                        <summary class="acc__sum">{{ $group->name }}</summary>
                        <div class="acc__body">
                            <x-team-group :group="$group" />
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endsection
