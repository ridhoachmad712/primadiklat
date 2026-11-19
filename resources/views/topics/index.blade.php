@extends('layouts.app')

@section('title', 'Tema Bimtek')
@section('description', 'Katalog tema bimtek dan diklat yang dapat diikuti pada seluruh jadwal dan kota pelaksanaan.')

@section('content')
<x-page-header title="Tema Bimtek" eyebrow="Katalog Materi"
    subtitle="Semua tema dapat diikuti pada tanggal dan kota mana pun di jadwal tahunan. Tidak menemukan tema yang dibutuhkan? Usulkan saat mendaftar."
    :crumbs="['Tema' => null]">
    <form method="GET" class="mt-6 flex max-w-xl gap-2 sm:mt-8">
        @if($category)<input type="hidden" name="kategori" value="{{ $category }}">@endif
        <label class="sr-only" for="q">Cari tema</label>
        <input id="q" type="search" name="q" value="{{ $q }}" placeholder="Cari tema, misalnya: keuangan desa" class="input !py-3">
        <button class="btn btn-primary !py-3">Cari</button>
    </form>
</x-page-header>

<section class="container-x py-6 sm:py-10">
    @if($categories->isNotEmpty())
        <div class="chip-row">
            <a href="{{ route('topics.index', array_filter(['q' => $q])) }}" @class(['chip', '!bg-brand-600 !text-white' => ! $category])>Semua</a>
            @foreach($categories as $c)
                <a href="{{ route('topics.index', array_filter(['kategori' => $c->slug, 'q' => $q])) }}" @class(['chip', '!bg-brand-600 !text-white' => $category === $c->slug])>{{ $c->name }} <span>{{ $c->topics_count }}</span></a>
            @endforeach
        </div>
    @endif

    @if($topics->isEmpty())
        <div class="card mt-8 p-6 sm:p-10 text-center">
            <p class="text-slate-600">Tema tidak ditemukan.</p>
            <a href="{{ route('register.create') }}" class="btn btn-primary mt-4">Usulkan tema saat mendaftar</a>
        </div>
    @else
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($topics as $topic)<x-topic-card :topic="$topic"/>@endforeach
        </div>
    @endif
</section>
@endsection
