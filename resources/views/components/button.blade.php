@props(['href' => null, 'variant' => 'primary', 'type' => 'button'])

@if ($href)
    <a {{ $attributes->class(['btn', "btn--{$variant}"])->merge(['href' => $href]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->class(['btn', "btn--{$variant}"])->merge(['type' => $type]) }}>{{ $slot }}</button>
@endif
