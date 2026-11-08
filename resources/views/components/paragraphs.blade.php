@props(['items'])

@foreach ($items as $paragraph)
    {{-- Teks berasal dari file data sendiri, bukan masukan pengunjung. inlineMarkdown mengubah **kata** menjadi tebal. --}}
    <p>{!! Str::inlineMarkdown($paragraph) !!}</p>
@endforeach
