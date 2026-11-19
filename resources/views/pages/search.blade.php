@extends('layouts.app')

@section('title', $q ? 'Hasil pencarian: '.$q : 'Pencarian')
@section('noindex', '1')

@section('content')
<x-page-header title="Pencarian" :crumbs="['Pencarian' => null]">
    <form class="mt-6 flex max-w-xl gap-2">
        <input type="search" name="q" value="{{ $q }}" placeholder="Cari tema bimtek, kota, atau artikel…" class="input !py-3" autofocus>
        <button class="btn btn-primary">Cari</button>
    </form>
</x-page-header>

<section class="container-x py-12">
    @if(mb_strlen($q) < 2)
        <p class="text-slate-500">Ketik minimal 2 huruf untuk mencari.</p>
    @elseif($trainings->isEmpty() && $posts->isEmpty() && $topics->isEmpty() && $venues->isEmpty())
        <div class="card p-6 sm:p-10 text-center">
            <p class="font-semibold">Tidak ada hasil untuk “{{ $q }}”</p>
            <p class="mt-1 text-sm text-slate-500">Coba kata kunci lain atau lihat <a href="{{ route('schedule.index') }}" class="text-brand-700 hover:underline">semua jadwal</a>.</p>
        </div>
    @else
        @if($topics->isNotEmpty())
            <h2 class="mb-5 text-lg font-bold">Tema Bimtek <span class="font-normal text-slate-500">({{ $topics->count() }})</span></h2>
            <div class="mb-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($topics as $topic)<x-topic-card :topic="$topic"/>@endforeach
            </div>
        @endif
        @if($venues->isNotEmpty())
            <h2 class="mb-5 text-lg font-bold">Lokasi Kegiatan</h2>
            <div class="mb-14 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($venues as $v)
                    <a href="{{ route('register.create', ['hotel' => $v->id]) }}" class="card p-4 hover:border-brand-200">
                        <span class="block text-xs font-bold text-brand-700 uppercase">{{ $v->city }}</span>
                        <span class="block font-semibold text-slate-900">{{ $v->name }}</span>
                        <span class="block text-xs text-slate-500">{{ $v->address }}</span>
                    </a>
                @endforeach
            </div>
        @endif
        @if($trainings->isNotEmpty())
            <h2 class="text-lg font-bold">Kegiatan <span class="font-normal text-slate-500">({{ $trainings->count() }})</span></h2>
            <div class="mt-5 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($trainings as $t)<x-training-card :training="$t"/>@endforeach
            </div>
        @endif
        @if($posts->isNotEmpty())
            <h2 class="mt-14 text-lg font-bold">Artikel <span class="font-normal text-slate-500">({{ $posts->count() }})</span></h2>
            <div class="mt-5 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($posts as $p)<x-post-card :post="$p"/>@endforeach
            </div>
        @endif
    @endif
</section>
@endsection
