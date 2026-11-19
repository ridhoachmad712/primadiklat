@extends('layouts.app')

@section('content')
@php
    $topicIndex = $topics->map(fn ($t) => ['t' => $t->title, 's' => $t->slug])->values();
@endphp

{{-- HERO: cari tema dulu, tanggal & kota opsional --}}
<section class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(50rem_30rem_at_90%_-20%,color-mix(in_oklab,var(--color-brand-600)_14%,transparent),transparent),radial-gradient(30rem_20rem_at_0%_110%,rgba(245,158,11,0.10),transparent)]"></div>
    <div class="container-x relative grid items-center gap-10 py-8 sm:py-14 lg:grid-cols-12 lg:gap-12 lg:py-20">
        <div class="min-w-0 lg:col-span-7">
            <h1 class="text-[2.1rem] leading-[1.1] font-extrabold sm:text-5xl lg:text-[3.4rem]">{{ setting('hero_title') }}</h1>
            <p class="mt-4 max-w-xl text-base text-slate-600 sm:mt-5 sm:text-lg">{{ setting('hero_subtitle') }}</p>

            <form action="{{ route('topics.index') }}" method="GET" x-data="heroSearch(@js($topicIndex))" @submit="go($event)"
                  class="mt-6 max-w-2xl rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-200/50 sm:mt-8">
                <div class="flex gap-2">
                    <label class="sr-only" for="hero-q">Cari tema bimtek</label>
                    <div class="relative min-w-0 flex-1">
                        <x-heroicon-o-magnifying-glass class="pointer-events-none absolute top-1/2 left-3 size-5 -translate-y-1/2 text-slate-400"/>
                        <input id="hero-q" name="q" x-model="q" list="hero-topics" autocomplete="off" type="search"
                               placeholder="Cari tema bimtek…"
                               class="w-full rounded-xl border-0 bg-slate-50 py-3 pr-3 pl-10 text-base text-slate-900 outline-none placeholder:text-slate-500 focus:ring-2 focus:ring-brand-500/30 sm:text-sm">
                        <datalist id="hero-topics">
                            @foreach($topics as $tp)<option value="{{ $tp->title }}"></option>@endforeach
                        </datalist>
                    </div>
                    <button class="btn btn-primary shrink-0 py-3" x-text="match ? 'Daftar' : 'Cari'">Cari</button>
                </div>

                <div x-show="more" x-collapse x-cloak>
                    <div class="grid gap-2 pt-2 sm:grid-cols-2">
                        <label class="sr-only" for="hero-tanggal">Tanggal</label>
                        <select id="hero-tanggal" x-model="tanggal" class="min-w-0 rounded-xl border-0 bg-slate-50 px-3 py-3 text-base text-slate-700 outline-none sm:text-sm">
                            <option value="">Tanggal (opsional)</option>
                            @foreach($openDates->groupBy(fn ($d) => $d->start_date->translatedFormat('F Y')) as $m => $items)
                                <optgroup label="{{ $m }}">@foreach($items as $d)<option value="{{ $d->start_date->toDateString() }}">{{ $d->label }}</option>@endforeach</optgroup>
                            @endforeach
                        </select>
                        <label class="sr-only" for="hero-kota">Kota</label>
                        <select id="hero-kota" x-model="hotel" class="min-w-0 rounded-xl border-0 bg-slate-50 px-3 py-3 text-base text-slate-700 outline-none sm:text-sm">
                            <option value="">Kota (opsional)</option>
                            @foreach($cities as $city => $items)
                                <optgroup label="{{ $city }}">@foreach($items as $v)<option value="{{ $v->id }}">{{ $city }} · {{ $v->name }}</option>@endforeach</optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="button" x-show="!more" @click="more = true" class="tap mt-1 inline-flex items-center gap-1 px-2 py-1.5 text-xs font-semibold text-brand-700 hover:underline">
                    <x-heroicon-m-plus class="size-4"/> Pilih tanggal & kota sekaligus
                </button>
            </form>

            @if($featuredTopics->isNotEmpty())
                <div class="chip-row mt-5 items-center text-sm">
                    <span class="text-slate-500">Populer:</span>
                    @foreach($featuredTopics->take(4) as $tp)
                        <a href="{{ route('topics.show', $tp) }}" class="tap rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-700 hover:border-brand-300 hover:text-brand-700">{{ \Illuminate\Support\Str::of($tp->title)->replaceMatches('/^(Bimtek|Diklat|Workshop|Pelatihan)\s+/i', '')->limit(30) }}</a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="min-w-0 lg:col-span-5">
            @if(setting('hero_image'))
                <img src="{{ storage_url(setting('hero_image')) }}" alt="Kegiatan {{ setting('site_name') }}" class="aspect-[16/10] w-full rounded-3xl object-cover" fetchpriority="high">
            @endif
            <div @class(['overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-300/40', 'relative mx-3 -mt-16 sm:mx-6' => setting('hero_image')])>
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
                    <div>
                        <p class="text-xs font-semibold text-brand-700">Jadwal Terdekat</p>
                        <p class="font-bold text-slate-900">Bimtek 2 hari, setiap pekan</p>
                    </div>
                    <x-heroicon-o-calendar-days class="size-7 text-slate-300"/>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse($openDates->take(3) as $d)
                        <li>
                            <a href="{{ route('register.create', ['tanggal' => $d->start_date->toDateString()]) }}" class="group flex items-center gap-3 px-4 py-3 hover:bg-slate-50 sm:gap-4 sm:px-6 sm:py-3.5">
                                <span class="flex w-12 shrink-0 flex-col items-center rounded-xl border border-slate-200 py-1">
                                    <span class="text-lg leading-none font-extrabold text-slate-900">{{ $d->start_date->format('d') }}</span>
                                    <span class="text-[10px] font-semibold text-brand-600 uppercase">{{ $d->start_date->translatedFormat('M') }}</span>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-semibold text-slate-900">{{ $d->label }}</span>
                                    <span class="block text-xs text-slate-500">{{ $d->days_label }} · semua tema & kota</span>
                                </span>
                                <span class="text-xs font-semibold text-brand-700 group-hover:underline">Daftar</span>
                            </a>
                        </li>
                    @empty
                        <li class="px-6 py-8 text-center text-sm text-slate-500">Jadwal baru segera diumumkan.</li>
                    @endforelse
                </ul>
                <a href="{{ route('schedule.index') }}" class="flex items-center justify-center gap-1 border-t border-slate-100 bg-slate-50 px-6 py-3 text-sm font-semibold text-brand-700 hover:bg-brand-50">
                    Lihat jadwal {{ $year }} ({{ $yearCount }} tanggal, {{ $cities->count() }} kota)
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Sections below the hero: order, visibility and texts come from Pengaturan Situs > Beranda --}}
@foreach($sections as $s)
    <section id="{{ $s['key'] }}" @class(['py-12 sm:py-20', 'border-y border-slate-200/70 bg-slate-50/60' => $loop->odd])>
        @include('home.sections.'.$s['key'], ['s' => $s])
    </section>
@endforeach
@endsection

@push('scripts')
<script>
    function heroSearch(topics) {
        return {
            q: '', tanggal: '', hotel: '', more: false,
            get match() {
                const q = this.q.trim().toLowerCase();
                return q ? topics.find(t => t.t.toLowerCase() === q) : null;
            },
            go(e) {
                // Exact topic chosen (or only date/city picked): straight to the registration form; otherwise search the catalog.
                if (!this.match && this.q.trim()) return;
                e.preventDefault();
                const params = new URLSearchParams();
                if (this.match) params.set('tema', this.match.s);
                if (this.tanggal) params.set('tanggal', this.tanggal);
                if (this.hotel) params.set('hotel', this.hotel);
                window.location = @js(route('register.create')) + (params.toString() ? '?' + params : '');
            },
        };
    }
</script>
@endpush
