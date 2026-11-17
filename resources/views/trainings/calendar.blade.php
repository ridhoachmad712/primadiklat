@extends('layouts.app')

@section('title', 'Kalender Kegiatan '.$month->translatedFormat('F Y'))
@section('description', 'Kalender jadwal bimtek dan diklat bulan '.$month->translatedFormat('F Y').'.')

@section('content')
@php
    $colors = ['bg-brand-50 text-brand-800 border-brand-200', 'bg-emerald-50 text-emerald-800 border-emerald-200', 'bg-amber-50 text-amber-800 border-amber-200', 'bg-violet-50 text-violet-800 border-violet-200', 'bg-rose-50 text-rose-800 border-rose-200', 'bg-sky-50 text-sky-800 border-sky-200'];
    $days = [];
    for ($d = $gridStart->copy(); $d->lte($gridEnd); $d->addDay()) { $days[] = $d->copy(); }
    $inMonth = $trainings->filter(fn ($t) => $t->start_date->lte($month->copy()->endOfMonth()) && $t->end_date->gte($month));
    $slotsInMonth = $slots->filter(fn ($d) => $d->start_date->lte($month->copy()->endOfMonth()) && $d->end_date->gte($month));
@endphp
<x-page-header title="Kalender Kegiatan" eyebrow="Jadwal" subtitle="Lihat seluruh jadwal bimtek dan diklat dalam tampilan kalender bulanan." :crumbs="['Jadwal' => route('schedule.index'), 'Kalender' => null]"/>

<section class="container-x py-10">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h2 class="text-2xl font-bold">{{ $month->translatedFormat('F Y') }}</h2>
        <div class="flex items-center gap-2">
            <a href="{{ route('trainings.calendar', ['bulan' => $month->copy()->subMonth()->format('Y-m')]) }}" class="btn btn-outline !px-3" aria-label="Bulan sebelumnya"><x-heroicon-m-chevron-left class="size-4"/></a>
            <a href="{{ route('trainings.calendar') }}" class="btn btn-outline">Bulan ini</a>
            <a href="{{ route('trainings.calendar', ['bulan' => $month->copy()->addMonth()->format('Y-m')]) }}" class="btn btn-outline !px-3" aria-label="Bulan berikutnya"><x-heroicon-m-chevron-right class="size-4"/></a>
        </div>
    </div>

    {{-- Desktop grid --}}
    <div class="card mt-6 hidden overflow-hidden md:block">
        <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50 text-center text-xs font-semibold tracking-wide text-slate-500 uppercase">
            @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $d)<div class="py-3">{{ $d }}</div>@endforeach
        </div>
        <div class="grid grid-cols-7">
            @foreach($days as $day)
                @php $events = $trainings->filter(fn ($t) => $day->betweenIncluded($t->start_date, $t->end_date)); @endphp
                <div @class(['min-h-32 border-r border-b border-slate-100 p-2 [&:nth-child(7n)]:border-r-0', 'bg-slate-50/60' => ! $day->isSameMonth($month)])>
                    <div @class(['mb-1 grid size-7 place-items-center rounded-full text-xs font-semibold',
                        'bg-brand-600 text-white' => $day->isToday(),
                        'text-slate-300' => ! $day->isToday() && ! $day->isSameMonth($month),
                        'text-slate-700' => ! $day->isToday() && $day->isSameMonth($month)])>{{ $day->day }}</div>
                    <div class="space-y-1">
                        @foreach($slots->filter(fn ($d) => $day->betweenIncluded($d->start_date, $d->end_date)) as $d)
                            @if($d->is_open)
                                <a href="{{ route('register.create', ['tanggal' => $d->start_date->toDateString()]) }}" class="block rounded-md bg-brand-600 px-1.5 py-1 text-[11px] leading-tight font-semibold text-white hover:bg-brand-700">{{ $day->isSameDay($d->start_date) ? 'Jadwal Bimtek · Daftar' : '↳ Jadwal Bimtek' }}</a>
                            @else
                                <span class="block rounded-md bg-slate-200 px-1.5 py-1 text-[11px] leading-tight font-medium text-slate-500">Jadwal Bimtek</span>
                            @endif
                        @endforeach
                        @foreach($events->take(3) as $t)
                            <a href="{{ route('trainings.show', $t) }}" title="{{ $t->title }}"
                               class="block truncate rounded-md border px-1.5 py-1 text-[11px] leading-tight font-medium hover:brightness-95 {{ $colors[$t->id % count($colors)] }}">
                                {{ $day->isSameDay($t->start_date) || $day->isMonday() ? $t->title : '↳ '.$t->title }}
                            </a>
                        @endforeach
                        @if($events->count() > 3)<p class="px-1 text-[11px] text-slate-500">+{{ $events->count() - 3 }} lainnya</p>@endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- List (mobile + summary) --}}
    <div class="mt-8">
        <h3 class="font-semibold text-slate-900 md:text-lg">Daftar kegiatan {{ $month->translatedFormat('F Y') }}</h3>
        @if($slotsInMonth->isNotEmpty())
            <ul class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($slotsInMonth as $d)
                    <li>
                        <a href="{{ $d->is_open ? route('register.create', ['tanggal' => $d->start_date->toDateString()]) : route('schedule.index') }}" @class(['card flex items-center gap-4 p-4', 'hover:border-brand-200 hover:bg-brand-50/40' => $d->is_open, 'opacity-60' => ! $d->is_open])>
                            <x-heroicon-o-calendar-days class="size-6 shrink-0 text-brand-600"/>
                            <span class="flex-1">
                                <span class="block font-semibold text-slate-900">Jadwal Bimtek · {{ $d->label }}</span>
                                <span class="block text-xs text-slate-500">{{ $d->days_label }} · semua tema & kota</span>
                            </span>
                            <span class="text-xs font-semibold {{ $d->is_open ? 'text-brand-700' : 'text-slate-400' }}">{{ $d->is_open ? 'Daftar' : 'Ditutup' }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
        @if($inMonth->isEmpty() && $slotsInMonth->isEmpty())
            <p class="card mt-4 p-8 text-center text-sm text-slate-500">Tidak ada kegiatan pada bulan ini.</p>
        @elseif($inMonth->isNotEmpty())
            <ul class="mt-4 divide-y divide-slate-100 card">
                @foreach($inMonth as $t)
                    <li>
                        <a href="{{ route('trainings.show', $t) }}" class="flex items-center gap-4 p-4 hover:bg-slate-50">
                            <span class="flex w-14 shrink-0 flex-col items-center rounded-xl border border-slate-200 py-1.5">
                                <span class="text-lg leading-none font-extrabold text-slate-900">{{ $t->start_date->format('d') }}</span>
                                <span class="text-[10px] font-semibold text-brand-600 uppercase">{{ $t->start_date->translatedFormat('M') }}</span>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate font-semibold text-slate-900">{{ $t->title }}</span>
                                <span class="mt-0.5 block text-sm text-slate-500">{{ $t->date_range }} · {{ $t->method === 'online' ? 'Online' : $t->city }}</span>
                            </span>
                            <x-heroicon-m-chevron-right class="size-5 shrink-0 text-slate-300"/>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
@endsection
