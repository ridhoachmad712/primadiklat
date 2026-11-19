@extends('layouts.app')

@section('title', $training->meta_title ?: $training->title)
@section('description', $training->meta_description ?: ($training->excerpt ?: strip_tags((string) $training->description)))
@if($training->thumbnail_url) @section('og_image', $training->thumbnail_url) @endif
@section('og_type', 'article')
@if($training->noindex) @section('noindex', '1') @endif
@if($training->thumbnail_url && $training->image_alt) @section('og_image_alt', $training->image_alt) @endif

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Event',
    'name' => $training->title,
    'description' => $training->excerpt,
    'startDate' => $training->start_date->toDateString(),
    'endDate' => $training->end_date->toDateString(),
    'eventStatus' => $training->status === 'cancelled' ? 'https://schema.org/EventCancelled' : 'https://schema.org/EventScheduled',
    'eventAttendanceMode' => ['online' => 'https://schema.org/OnlineEventAttendanceMode', 'hybrid' => 'https://schema.org/MixedEventAttendanceMode'][$training->method] ?? 'https://schema.org/OfflineEventAttendanceMode',
    'location' => $training->method === 'online'
        ? ['@type' => 'VirtualLocation', 'url' => route('trainings.show', $training)]
        : ['@type' => 'Place', 'name' => $training->venue ?? $training->city, 'address' => $training->venue_address ?? $training->city],
    'organizer' => ['@type' => 'Organization', 'name' => setting('site_name'), 'url' => url('/')],
    'offers' => collect($training->prices ?? [])->map(fn ($p) => ['@type' => 'Offer', 'name' => $p['name'], 'price' => (int) $p['price'], 'priceCurrency' => 'IDR', 'url' => route('trainings.show', $training)])->values(),
    'image' => $training->thumbnail_url,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('mobile_cta', $training->can_register ? route('registrations.create', $training) : route('register.create'))

@section('content')
@php
    $t = $training;
    $waText = "Halo Admin, saya ingin bertanya tentang kegiatan:\n*{$t->title}*\n{$t->date_range} · {$t->location}\n".route('trainings.show', $t);
    $share = rawurlencode($t->title.' – '.route('trainings.show', $t));
@endphp

<section class="border-b border-slate-200/70 bg-gradient-to-b from-brand-50/60 to-white">
    <div class="container-x py-10">
        <nav class="mb-5 flex flex-wrap items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-brand-700">Beranda</a>
            <x-heroicon-m-chevron-right class="size-3.5 text-slate-300"/>
            <a href="{{ route('trainings.index') }}" class="hover:text-brand-700">Jadwal</a>
            @if($t->category)
                <x-heroicon-m-chevron-right class="size-3.5 text-slate-300"/>
                <a href="{{ route('trainings.index', ['kategori' => $t->category->slug]) }}" class="hover:text-brand-700">{{ $t->category->name }}</a>
            @endif
        </nav>
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-brand-600 px-2.5 py-1 text-xs font-semibold text-white">{{ $t->type_label }}</span>
            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">{{ $t->method_label }}</span>
            <span @class(['rounded-full px-2.5 py-1 text-xs font-semibold ring-1',
                'bg-emerald-50 text-emerald-700 ring-emerald-200' => $t->can_register,
                'bg-slate-100 text-slate-600 ring-slate-200' => ! $t->can_register])>{{ $t->availability_label }}</span>
        </div>
        <h1 class="mt-4 max-w-4xl text-3xl leading-tight font-extrabold sm:text-4xl">{{ $t->title }}</h1>
        @if($t->excerpt)<p class="mt-3 max-w-3xl text-lg text-slate-600">{{ $t->excerpt }}</p>@endif
    </div>
</section>

<div class="container-x grid gap-8 py-6 sm:py-10 lg:grid-cols-3 lg:gap-10">
    {{-- MAIN --}}
    <div class="min-w-0 lg:col-span-2">
        <div class="aspect-[16/9] overflow-hidden rounded-2xl bg-slate-100">
            @if($t->thumbnail_url)
                <img src="{{ $t->thumbnail_url }}" alt="{{ $t->image_alt ?: $t->title }}" class="size-full object-cover">
            @else
                <x-placeholder-cover :seed="$t->id" :label="$t->type_label"/>
            @endif
        </div>

        {{-- Quick facts --}}
        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach([
                ['heroicon-o-calendar-days', 'Tanggal', $t->date_range],
                ['heroicon-o-clock', 'Durasi', $t->duration_days.' hari'],
                ['heroicon-o-map-pin', 'Lokasi', $t->method === 'online' ? 'Online' : ($t->city ?? '-')],
                ['heroicon-o-users', 'Kuota', $t->quota ? ($t->remaining_quota.' dari '.$t->quota.' kursi') : 'Tidak dibatasi'],
            ] as [$icon, $label, $value])
                <div class="card p-4">
                    <x-dynamic-component :component="$icon" class="size-5 text-brand-600"/>
                    <p class="mt-2 text-xs text-slate-500">{{ $label }}</p>
                    <p class="text-sm font-semibold text-slate-900">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div x-data="{ tab: 'deskripsi' }" class="mt-10">
            <div class="flex gap-1 overflow-x-auto border-b border-slate-200 text-sm font-semibold [scrollbar-width:none]" role="tablist">
                @foreach(['deskripsi' => 'Deskripsi', 'materi' => 'Materi', 'biaya' => 'Biaya & Fasilitas', 'lokasi' => 'Lokasi'] as $key => $label)
                    <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'border-brand-600 text-brand-700' : 'border-transparent text-slate-500 hover:text-slate-800'"
                        class="-mb-px shrink-0 border-b-2 px-4 py-3 transition">{{ $label }}</button>
                @endforeach
            </div>

            <div x-show="tab === 'deskripsi'" class="prose prose-slate mt-8 max-w-none prose-headings:tracking-tight prose-a:text-brand-700">
                {!! $t->description ?: '<p>Informasi detail akan segera diperbarui.</p>' !!}
                @if($t->target_participants)
                    <h3>Sasaran Peserta</h3>
                    <p>{{ $t->target_participants }}</p>
                @endif
            </div>

            <div x-show="tab === 'materi'" x-cloak class="prose prose-slate mt-8 max-w-none">
                {!! $t->materials ?: '<p>Materi akan diinformasikan kemudian.</p>' !!}
            </div>

            <div x-show="tab === 'biaya'" x-cloak class="mt-8">
                @if($t->prices)
                    <div @class(['grid gap-4', 'sm:grid-cols-2' => count($t->prices) === 2, 'sm:grid-cols-3' => count($t->prices) >= 3])>
                        @foreach($t->prices as $i => $p)
                            <div @class(['card relative p-5', 'border-brand-300 ring-4 ring-brand-500/10' => $i === 0])>
                                @if($i === 0 && count($t->prices) > 1)<span class="absolute -top-2.5 left-5 rounded-full bg-brand-600 px-2 py-0.5 text-[10px] font-bold text-white uppercase">Populer</span>@endif
                                <p class="text-sm font-semibold text-slate-700">{{ $p['name'] }}</p>
                                <p class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900">{{ rupiah($p['price']) }}</p>
                                <p class="text-xs text-slate-500">per peserta</p>
                                @if(! empty($p['note']))<p class="mt-3 text-sm text-slate-600">{{ $p['note'] }}</p>@endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-600">Biaya kegiatan: hubungi admin kami.</p>
                @endif
                @if($t->facilities)
                    <h3 class="mt-8 font-semibold text-slate-900">Fasilitas peserta</h3>
                    <ul class="mt-4 grid gap-2.5 sm:grid-cols-2">
                        @foreach($t->facilities as $f)
                            <li class="flex items-center gap-2.5 text-sm text-slate-700"><x-heroicon-s-check-circle class="size-5 text-emerald-500"/> {{ $f }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div x-show="tab === 'lokasi'" x-cloak class="mt-8">
                @if($t->method === 'online')
                    <div class="card p-4 sm:p-6"><p class="font-semibold">Kegiatan dilaksanakan secara daring (online).</p><p class="mt-1 text-sm text-slate-600">Tautan Zoom Meeting dikirim ke email dan WhatsApp peserta H-1.</p></div>
                @else
                    <div class="card p-4 sm:p-6">
                        <p class="font-semibold text-slate-900">{{ $t->venue ?? 'Tempat akan diinformasikan' }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $t->venue_address }} {{ $t->city ? '· '.$t->city : '' }}</p>
                        @if($t->venue || $t->venue_address)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(trim($t->venue.' '.$t->venue_address.' '.$t->city)) }}" target="_blank" rel="noopener" class="btn btn-outline mt-4"><x-heroicon-o-map class="size-4"/> Buka di Google Maps</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Share --}}
        <div class="mt-12 flex flex-wrap items-center gap-2 border-t border-slate-200 pt-6 text-sm">
            <span class="mr-2 text-slate-500">Bagikan:</span>
            <a href="https://wa.me/?text={{ $share }}" target="_blank" rel="noopener" class="chip hover:bg-slate-200">WhatsApp</a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('trainings.show', $t)) }}" target="_blank" rel="noopener" class="chip hover:bg-slate-200">Facebook</a>
            <a href="https://t.me/share/url?url={{ urlencode(route('trainings.show', $t)) }}&text={{ urlencode($t->title) }}" target="_blank" rel="noopener" class="chip hover:bg-slate-200">Telegram</a>
            <button type="button" x-data="{ ok: false }" @click="navigator.clipboard.writeText('{{ route('trainings.show', $t) }}'); ok = true; setTimeout(() => ok = false, 1500)" class="chip hover:bg-slate-200">
                <span x-text="ok ? 'Tersalin ✓' : 'Salin tautan'">Salin tautan</span>
            </button>
        </div>
    </div>

    {{-- SIDEBAR --}}
    <aside class="lg:col-span-1">
        <div class="card sticky top-24 p-4 sm:p-6">
            <p class="text-xs text-slate-500">Biaya mulai dari</p>
            <p class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $t->lowest_price ? rupiah($t->lowest_price) : 'Hubungi kami' }}</p>

            <dl class="mt-5 space-y-3 border-t border-slate-100 pt-5 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Tanggal</dt><dd class="text-right font-medium text-slate-900">{{ $t->date_range }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Tempat</dt><dd class="text-right font-medium text-slate-900">{{ $t->location }}</dd></div>
                @if($t->registration_deadline)
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Batas daftar</dt><dd class="text-right font-medium text-slate-900">{{ $t->registration_deadline->translatedFormat('j F Y') }}</dd></div>
                @endif
                @if($t->quota)
                    <div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Sisa kuota</dt><dd class="font-medium text-slate-900">{{ $t->remaining_quota }} / {{ $t->quota }}</dd></div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand-600" style="width: {{ min(100, round($t->registered_count / max(1, $t->quota) * 100)) }}%"></div></div>
                    </div>
                @endif
            </dl>

            <div class="mt-6 grid gap-2">
                @if($t->can_register)
                    <a href="{{ route('registrations.create', $t) }}" class="btn btn-primary w-full !py-3">Daftar Sekarang</a>
                @else
                    <span class="btn w-full cursor-not-allowed bg-slate-100 !py-3 text-slate-500">{{ $t->availability_label }}</span>
                @endif
                <a href="{{ $t->brochure_url ?? route('trainings.invitation', $t) }}" target="_blank" class="btn btn-outline w-full"><x-heroicon-o-arrow-down-tray class="size-4"/> Unduh {{ $t->brochure_url ? 'Brosur' : 'Surat Undangan' }}</a>
                @if($t->brochure_url)
                    <a href="{{ route('trainings.invitation', $t) }}" target="_blank" class="btn btn-ghost w-full text-xs"><x-heroicon-o-document-text class="size-4"/> Surat Undangan PDF</a>
                @endif
                <a href="{{ wa_link($waText) }}" target="_blank" rel="noopener" class="btn btn-wa w-full">Tanya via WhatsApp</a>
            </div>
        </div>
    </aside>
</div>

@if($t->galleries->isNotEmpty())
<section class="container-x py-6">
    <h2 class="text-xl font-bold">Dokumentasi</h2>
    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach($t->galleries->flatMap->image_urls->take(8) as $img)
            <img src="{{ $img }}" alt="" loading="lazy" class="aspect-square w-full rounded-xl object-cover">
        @endforeach
    </div>
</section>
@endif

@if($related->isNotEmpty())
<section class="container-x py-12">
    <h2 class="text-xl font-bold">Kegiatan lain yang mungkin Anda minati</h2>
    <div class="swipe-row mt-6 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">
        @foreach($related as $r)<x-training-card :training="$r"/>@endforeach
    </div>
</section>
@endif
@endsection
