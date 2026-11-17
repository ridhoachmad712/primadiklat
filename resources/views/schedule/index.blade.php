@extends('layouts.app')

@section('title', 'Jadwal Bimtek '.$year)
@section('description', 'Jadwal bimtek Januari – Desember '.$year.': '.$dates->count().' jadwal pelaksanaan di '.$venues->count().' kota. Pilih tema, tanggal, dan kota sesuai kebutuhan instansi.')

@section('content')
@php $closeDays = \App\Models\ScheduleDate::closeDays(); @endphp
<x-page-header :title="'Jadwal Bimtek '.$year" eyebrow="Januari – Desember {{ $year }}"
    :subtitle="'Kegiatan dua hari setiap pekan. Semua tema dapat diikuti pada tanggal dan kota mana pun di bawah ini.'"
    :crumbs="['Jadwal' => null]">
    <div class="mt-8 flex flex-wrap gap-3">
        <a href="{{ route('register.create') }}" class="btn btn-primary">Daftar Sekarang</a>
        <a href="{{ route('topics.index') }}" class="btn btn-outline">Lihat {{ $topicCount }} Tema</a>
        <a href="{{ route('trainings.calendar', ['bulan' => ($next?->start_date ?? now())->format('Y-m')]) }}" class="btn btn-ghost"><x-heroicon-o-calendar class="size-4"/> Kalender</a>
    </div>
    <dl class="mt-8 grid max-w-2xl grid-cols-3 gap-3 text-center">
        <div class="rounded-2xl border border-slate-200 bg-white p-4"><dt class="text-xs text-slate-500">Jadwal</dt><dd class="text-2xl font-extrabold text-slate-900">{{ $dates->count() }}</dd></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4"><dt class="text-xs text-slate-500">Kota</dt><dd class="text-2xl font-extrabold text-slate-900">{{ $venues->count() }}</dd></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4"><dt class="text-xs text-slate-500">Hotel</dt><dd class="text-2xl font-extrabold text-slate-900">{{ $venues->flatten()->count() }}</dd></div>
    </dl>
</x-page-header>

<section class="container-x py-12">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="section-title">Tanggal Pelaksanaan</h2>
            <p class="mt-1 text-sm text-slate-500">Pendaftaran tiap jadwal ditutup H-{{ $closeDays }}. Klik tanggal untuk langsung mendaftar.</p>
        </div>
        @if($years->count() > 1)
            <div class="flex gap-1 rounded-xl bg-slate-100 p-1 text-sm font-medium">
                @foreach($years as $y)
                    <a href="{{ route('schedule.index', ['tahun' => $y]) }}" @class(['rounded-lg px-3 py-1.5', 'bg-white text-slate-900 shadow-sm' => $y == $year, 'text-slate-500' => $y != $year])>{{ $y }}</a>
                @endforeach
            </div>
        @endif
    </div>

    @if($dates->isEmpty())
        <div class="card mt-8 p-10 text-center text-slate-500">Jadwal tahun {{ $year }} belum tersedia.</div>
    @else
    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach(range(1, 12) as $m)
            @php $items = $months->get($m, collect()); $monthDate = \Carbon\Carbon::create($year, $m, 1); @endphp
            <div @class(['card overflow-hidden', 'opacity-60' => $items->isNotEmpty() && $items->every(fn ($d) => ! $d->is_open)])>
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-5 py-3">
                    <h3 class="font-bold tracking-wide text-slate-900 uppercase">{{ $monthDate->translatedFormat('F') }}</h3>
                    <span class="text-xs text-slate-500">{{ $items->count() }} jadwal</span>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse($items as $d)
                        <li>
                            @if($d->is_open)
                                <a href="{{ route('register.create', ['tanggal' => $d->start_date->toDateString()]) }}" class="group flex items-center justify-between gap-3 px-5 py-3 hover:bg-brand-50/60">
                                    <span>
                                        <span class="block font-semibold text-slate-900">{{ $d->label }}</span>
                                        <span class="block text-xs text-slate-500">{{ $d->days_label }}{{ $d->note ? ' · '.$d->note : '' }}</span>
                                    </span>
                                    <span class="text-xs font-semibold text-brand-700 opacity-0 transition group-hover:opacity-100">Daftar →</span>
                                </a>
                            @else
                                <div class="flex items-center justify-between gap-3 px-5 py-3 text-slate-400">
                                    <span>
                                        <span class="block font-medium line-through">{{ $d->label }}</span>
                                        <span class="block text-xs">{{ $d->days_label }}</span>
                                    </span>
                                    <span class="text-[11px] font-medium">{{ $d->end_date->lt(today()) ? 'Selesai' : 'Ditutup' }}</span>
                                </div>
                            @endif
                        </li>
                    @empty
                        <li class="px-5 py-3 text-sm text-slate-400">Tidak ada jadwal</li>
                    @endforelse
                </ul>
            </div>
        @endforeach
    </div>
    @endif
</section>

<section class="bg-slate-50/70 py-14">
    <div class="container-x">
        <h2 class="section-title">Pilihan Kota & Hotel</h2>
        <p class="mt-1 text-sm text-slate-500">Kegiatan diselenggarakan di hotel-hotel berikut. Pilih lokasi saat mendaftar.</p>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($venues as $city => $hotels)
                <div class="card p-5">
                    <p class="flex items-center gap-2 text-xs font-bold tracking-wide text-brand-700 uppercase"><x-heroicon-s-map-pin class="size-4"/> {{ $city }}</p>
                    <ul class="mt-3 space-y-3">
                        @foreach($hotels as $h)
                            <li>
                                <a href="{{ route('register.create', ['hotel' => $h->id]) }}" class="font-semibold text-slate-900 hover:text-brand-700">{{ $h->name }}</a>
                                @if($h->address)<p class="text-xs text-slate-500">{{ $h->address }}</p>@endif
                                @if($h->maps_url)<a href="{{ $h->maps_url }}" target="_blank" rel="noopener" class="text-xs font-medium text-brand-700 hover:underline">Lihat peta</a>@endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="container-x py-14">
    <div class="card grid items-center gap-6 bg-slate-950 p-8 text-slate-300 sm:p-10 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <h2 class="text-2xl font-bold text-white">Sudah menentukan tema, tanggal, dan kota?</h2>
            <p class="mt-2">Daftarkan satu atau beberapa peserta sekaligus. Informasi biaya kontribusi akan disampaikan admin melalui WhatsApp.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row lg:justify-end">
            <a href="{{ route('register.create') }}" class="btn btn-primary">Daftar Sekarang</a>
            <a href="{{ wa_link('Halo Admin, saya ingin bertanya tentang jadwal bimtek '.$year.'.') }}" target="_blank" rel="noopener" class="btn btn-wa">Tanya via WA</a>
        </div>
    </div>
</section>
@endsection
