@extends('layouts.app')

@section('title', $gallery->title)
@if($gallery->cover_url) @section('og_image', $gallery->cover_url) @endif

@section('content')
<x-page-header :title="$gallery->title" eyebrow="Galeri" :subtitle="$gallery->description" :crumbs="['Galeri' => route('galleries.index'), \Illuminate\Support\Str::limit($gallery->title, 40) => null]">
    @if($gallery->training)
        <a href="{{ route('trainings.show', $gallery->training) }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:underline">Lihat kegiatan: {{ $gallery->training->title }}</a>
    @endif
</x-page-header>

<section class="container-x py-12" x-data="{ open: false, i: 0, imgs: @js($gallery->image_urls) }" @keydown.escape.window="open = false" @keydown.arrow-right.window="if(open) i = (i + 1) % imgs.length" @keydown.arrow-left.window="if(open) i = (i - 1 + imgs.length) % imgs.length">
    <div class="columns-2 gap-4 sm:columns-3 lg:columns-4 [&>*]:mb-4">
        @foreach($gallery->image_urls as $idx => $url)
            <button type="button" @click="i = {{ $idx }}; open = true" class="block w-full overflow-hidden rounded-xl">
                <img src="{{ $url }}" alt="Foto {{ $idx + 1 }} – {{ $gallery->title }}" loading="lazy" class="w-full transition hover:opacity-90">
            </button>
        @endforeach
    </div>

    <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4" @click.self="open = false">
        <button type="button" @click="open = false" class="absolute top-4 right-4 rounded-full p-2 text-white/80 hover:bg-white/10" aria-label="Tutup"><x-heroicon-o-x-mark class="size-7"/></button>
        <button type="button" @click="i = (i - 1 + imgs.length) % imgs.length" class="absolute left-4 rounded-full p-2 text-white/80 hover:bg-white/10" aria-label="Sebelumnya"><x-heroicon-o-chevron-left class="size-8"/></button>
        <img :src="imgs[i]" alt="" class="max-h-[85vh] max-w-full rounded-lg">
        <button type="button" @click="i = (i + 1) % imgs.length" class="absolute right-4 rounded-full p-2 text-white/80 hover:bg-white/10" aria-label="Berikutnya"><x-heroicon-o-chevron-right class="size-8"/></button>
        <p class="absolute bottom-4 text-sm text-white/70" x-text="(i + 1) + ' / ' + imgs.length"></p>
    </div>
</section>
@endsection
