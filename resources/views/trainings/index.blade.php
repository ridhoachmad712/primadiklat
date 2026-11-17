@extends('layouts.app')

@section('title', ($filters['status'] ?? null) === 'selesai' ? 'Arsip Kegiatan' : 'Jadwal Bimtek & Diklat')
@section('description', 'Jadwal lengkap bimtek, diklat, pelatihan, dan workshop untuk aparatur pemerintah. Lihat tanggal, lokasi, biaya, lalu daftar online.')

@section('content')
@php $isArchive = ($filters['status'] ?? null) === 'selesai'; @endphp
<x-page-header :title="$isArchive ? 'Arsip Kegiatan' : 'Jadwal Bimtek & Diklat'" eyebrow="Jadwal Kegiatan"
    :subtitle="$isArchive ? 'Kegiatan yang telah terselenggara.' : 'Pilih kegiatan, unduh undangan resmi, dan daftar online dalam hitungan menit.'"
    :crumbs="['Jadwal' => null]"/>

<section class="container-x py-10" x-data="{ filters: false }">
    <form method="GET" class="card p-4">
        <div class="flex flex-col gap-3 lg:flex-row">
            <label class="relative flex-1">
                <span class="sr-only">Kata kunci</span>
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-slate-400"/>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari judul kegiatan…" class="input pl-10">
            </label>
            <select name="kategori" class="input lg:w-56" aria-label="Kategori">
                <option value="">Semua kategori</option>
                @foreach($categories as $c)<option value="{{ $c->slug }}" @selected(($filters['kategori'] ?? '') === $c->slug)>{{ $c->name }}</option>@endforeach
            </select>
            <select name="bulan" class="input lg:w-44" aria-label="Bulan">
                <option value="">Semua bulan</option>
                @foreach($months as $m)<option value="{{ $m->format('Y-m') }}" @selected(($filters['bulan'] ?? '') === $m->format('Y-m'))>{{ $m->translatedFormat('F Y') }}</option>@endforeach
            </select>
            <button type="button" @click="filters = !filters" class="btn btn-outline lg:hidden"><x-heroicon-o-adjustments-horizontal class="size-4"/> Filter lain</button>
            <div class="hidden gap-3 lg:flex">
                @include('trainings.partials.extra-filters')
            </div>
            <button class="btn btn-primary">Terapkan</button>
        </div>
        <div x-show="filters" x-collapse x-cloak class="mt-3 grid gap-3 sm:grid-cols-3 lg:hidden">
            @include('trainings.partials.extra-filters')
        </div>
    </form>

    <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">Menampilkan <strong class="text-slate-900">{{ $trainings->total() }}</strong> kegiatan</p>
        <div class="flex items-center gap-1 rounded-xl bg-slate-100 p-1 text-sm font-medium">
            <a href="{{ route('trainings.index', collect($filters)->except('status')->all()) }}" @class(['rounded-lg px-3 py-1.5', 'bg-white text-slate-900 shadow-sm' => ! $isArchive, 'text-slate-500' => $isArchive])>Akan datang</a>
            <a href="{{ route('trainings.index', array_merge($filters, ['status' => 'selesai'])) }}" @class(['rounded-lg px-3 py-1.5', 'bg-white text-slate-900 shadow-sm' => $isArchive, 'text-slate-500' => ! $isArchive])>Arsip</a>
            <a href="{{ route('trainings.calendar') }}" class="rounded-lg px-3 py-1.5 text-slate-500">Kalender</a>
        </div>
    </div>

    @if(collect($filters)->except('status')->filter()->isNotEmpty())
        <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
            <span class="text-slate-500">Filter aktif:</span>
            @foreach(collect($filters)->except('status')->filter() as $k => $v)
                <a href="{{ route('trainings.index', collect($filters)->except($k)->all()) }}" class="chip hover:bg-slate-200">{{ $k === 'kategori' ? ($categories->firstWhere('slug', $v)?->name ?? $v) : ($k === 'jenis' ? (\App\Models\Training::TYPES[$v] ?? $v) : ($k === 'metode' ? (\App\Models\Training::METHODS[$v] ?? $v) : $v)) }} <x-heroicon-m-x-mark class="size-3.5"/></a>
            @endforeach
            <a href="{{ route('trainings.index') }}" class="text-brand-700 hover:underline">Reset</a>
        </div>
    @endif

    @if($trainings->isEmpty())
        <div class="card mt-8 flex flex-col items-center p-14 text-center">
            <span class="grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-400"><x-heroicon-o-calendar class="size-7"/></span>
            <h2 class="mt-4 text-lg font-semibold">Belum ada kegiatan yang cocok</h2>
            <p class="mt-1 max-w-md text-sm text-slate-500">Coba ubah filter, atau hubungi kami — kami juga melayani penyelenggaraan in-house training sesuai kebutuhan.</p>
            <div class="mt-6 flex gap-2">
                <a href="{{ route('trainings.index') }}" class="btn btn-outline">Reset filter</a>
                <a href="{{ route('inhouse.create') }}" class="btn btn-primary">Ajukan In-House</a>
            </div>
        </div>
    @else
        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($trainings as $t)<x-training-card :training="$t"/>@endforeach
        </div>
        {{ $trainings->links() }}
    @endif
</section>
@endsection
