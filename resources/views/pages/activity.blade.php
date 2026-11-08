@extends('layouts.app')

@section('title', 'Kegiatan')
@section('description', 'Pertemuan mingguan, sesi sharing, olahraga, musik, menulis, dan edukasi kesehatan mental bersama Ruang Pulang.')

@section('content')
    <x-page-hero>Kegiatan <em>Ruang Pulang</em></x-page-hero>

    <section class="section section--sand">
        <div class="container">
            <div class="grid grid--3">
                @foreach ($activities as $activity)
                    <x-activity-card :activity="$activity" />
                @endforeach
            </div>
        </div>
    </section>
@endsection
