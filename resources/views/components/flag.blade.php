@props(['code'])

{{-- Bendera bulat kecil untuk pilihan bahasa. Digambar dengan SVG karena emoji bendera tidak tampil di Windows. --}}
@if ($code === 'en')
    <svg {{ $attributes->class('flag') }} viewBox="15 0 30 30" aria-hidden="true">
        <rect width="60" height="30" fill="#012169"/>
        <path d="M0,0 L60,30 M60,0 L0,30" stroke="#fff" stroke-width="6"/>
        <path d="M0,0 L60,30 M60,0 L0,30" stroke="#c8102e" stroke-width="2"/>
        <path d="M30,0 v30 M0,15 h60" stroke="#fff" stroke-width="10"/>
        <path d="M30,0 v30 M0,15 h60" stroke="#c8102e" stroke-width="6"/>
    </svg>
@else
    <svg {{ $attributes->class('flag') }} viewBox="0 0 30 30" aria-hidden="true"><rect width="30" height="15" fill="#ce1126"/><rect y="15" width="30" height="15" fill="#fff"/></svg>
@endif
