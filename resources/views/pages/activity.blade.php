@extends('layouts.app')

@section('title', __('Kegiatan'))
@section('description', __('Pertemuan mingguan, sesi sharing, olahraga, musik, menulis, dan edukasi kesehatan mental bersama Ruang Pulang.'))

@section('content')
    <x-page-hero>{!! __('Kegiatan <em>Ruang Pulang</em>') !!}</x-page-hero>

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
