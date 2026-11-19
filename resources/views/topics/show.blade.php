@extends('layouts.app')

@section('title', $topic->title)
@section('description', $topic->excerpt ?: 'Bimtek '.$topic->title.' – pilih tanggal dan kota pelaksanaan sesuai kebutuhan.')

@section('mobile_cta', route('register.create', ['tema' => $topic->slug]))

@section('content')
<x-page-header :title="$topic->title" :eyebrow="$topic->type_label.($topic->category ? ' · '.$topic->category->name : '')" :subtitle="$topic->excerpt"
    :crumbs="['Tema' => route('topics.index'), \Illuminate\Support\Str::limit($topic->title, 40) => null]"/>

<div class="container-x grid gap-8 py-6 sm:py-10 lg:grid-cols-3 lg:gap-10">
    <div class="space-y-8 lg:col-span-2">
        @if($topic->thumbnail_url)
            <img src="{{ $topic->thumbnail_url }}" alt="" class="aspect-[16/8] w-full rounded-2xl object-cover">
        @endif

        @if($topic->description)
            <div class="prose prose-slate max-w-none">{!! $topic->description !!}</div>
        @endif

        @if($topic->materials)
            <div class="card p-4 sm:p-6">
                <h2 class="text-lg font-bold">Materi Pokok</h2>
                <div class="prose prose-slate mt-3 max-w-none prose-li:my-0.5">{!! $topic->materials !!}</div>
            </div>
        @endif

        @if($topic->target_participants)
            <div class="card p-4 sm:p-6">
                <h2 class="text-lg font-bold">Sasaran Peserta</h2>
                <p class="mt-2 text-slate-600">{{ $topic->target_participants }}</p>
            </div>
        @endif

        <div class="card p-4 sm:p-6">
            <h2 class="text-lg font-bold">Jadwal Terdekat</h2>
            <p class="mt-1 text-sm text-slate-500">Tema ini dapat diikuti pada seluruh jadwal berikut, di kota mana pun.</p>
            <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($dates as $d)
                    <a href="{{ route('register.create', ['tema' => $topic->slug, 'tanggal' => $d->start_date->toDateString()]) }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm transition hover:border-brand-300 hover:bg-brand-50/60">
                        <span class="block font-semibold text-slate-900">{{ $d->label }}</span>
                        <span class="text-xs text-slate-500">{{ $d->days_label }}</span>
                    </a>
                @endforeach
            </div>
            <a href="{{ route('schedule.index') }}" class="tap mt-4 inline-block text-sm font-semibold text-brand-700 hover:underline">Lihat jadwal setahun</a>
        </div>
    </div>

    <aside>
        <form action="{{ route('register.create') }}" method="GET" class="card sticky top-24 space-y-4 p-5">
            <input type="hidden" name="tema" value="{{ $topic->slug }}">
            <p class="font-bold text-slate-900">Ikuti tema ini</p>
            <div>
                <label for="tanggal" class="label">Tanggal</label>
                <select id="tanggal" name="tanggal" class="input">
                    <option value="">Pilih nanti</option>
                    @foreach($dates as $d)<option value="{{ $d->start_date->toDateString() }}">{{ $d->label }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="hotel" class="label">Kota & hotel</label>
                <select id="hotel" name="hotel" class="input">
                    <option value="">Pilih nanti</option>
                    @foreach($venues->groupBy('city') as $city => $items)
                        <optgroup label="{{ $city }}">@foreach($items as $v)<option value="{{ $v->id }}">{{ $v->name }}</option>@endforeach</optgroup>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary w-full">Lanjut Daftar</button>
            <p class="text-xs text-slate-500">{{ setting('price_notice') }}</p>
            <a href="{{ wa_link('Halo Admin, saya ingin bertanya tentang bimtek "'.$topic->title.'".') }}" target="_blank" rel="noopener" class="btn btn-wa w-full">Tanya via WhatsApp</a>
        </form>
    </aside>
</div>

@if($related->isNotEmpty())
<section class="bg-slate-50/70 py-10 sm:py-14">
    <div class="container-x">
        <h2 class="section-title">Tema Terkait</h2>
        <div class="swipe-row mt-6 sm:mt-8 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">
            @foreach($related as $r)<x-topic-card :topic="$r"/>@endforeach
        </div>
    </div>
</section>
@endif
@endsection
