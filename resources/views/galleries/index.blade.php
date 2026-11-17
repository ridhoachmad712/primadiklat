@extends('layouts.app')

@section('title', 'Galeri Kegiatan')

@section('content')
<x-page-header title="Galeri Kegiatan" eyebrow="Dokumentasi" subtitle="Dokumentasi bimtek, diklat, dan workshop yang telah kami selenggarakan." :crumbs="['Galeri' => null]"/>

<section class="container-x py-12">
    @if($galleries->isEmpty())
        <div class="card flex flex-col items-center p-14 text-center">
            <span class="grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-400"><x-heroicon-o-photo class="size-7"/></span>
            <p class="mt-4 text-slate-500">Dokumentasi kegiatan akan segera ditampilkan.</p>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($galleries as $g)
                <a href="{{ route('galleries.show', $g) }}" class="group">
                    <div class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-slate-100">
                        @if($g->cover_url)<img src="{{ $g->cover_url }}" alt="" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-105">@else<x-placeholder-cover :seed="$g->id"/>@endif
                        <span class="absolute right-3 bottom-3 inline-flex items-center gap-1 rounded-full bg-black/60 px-2.5 py-1 text-xs font-medium text-white backdrop-blur"><x-heroicon-o-photo class="size-3.5"/> {{ count($g->images ?? []) }}</span>
                    </div>
                    <h2 class="mt-3 font-semibold text-slate-900 group-hover:text-brand-700">{{ $g->title }}</h2>
                    @if($g->event_date)<p class="text-sm text-slate-500">{{ $g->event_date->translatedFormat('j F Y') }}</p>@endif
                </a>
            @endforeach
        </div>
        {{ $galleries->links() }}
    @endif
</section>
@endsection
