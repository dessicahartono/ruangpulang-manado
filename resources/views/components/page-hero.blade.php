@props(['after' => null])

<section class="page-hero">
    <x-sky />
    <div class="container">
        <h1>{{ $slot }}</h1>
        {{ $after }}
    </div>
</section>
