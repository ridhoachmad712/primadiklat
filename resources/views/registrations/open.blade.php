@extends('layouts.app')

@section('title', 'Daftar Bimtek')
@section('description', 'Daftar bimtek online: pilih tema, tanggal, dan kota pelaksanaan. Bisa mendaftarkan beberapa peserta sekaligus.')

@section('content')
@php
    $byCategory = $topics->groupBy(fn ($t) => $t->category?->name ?? 'Lainnya');
    $byMonth = $dates->groupBy(fn ($d) => $d->start_date->translatedFormat('F Y'));
    $byCity = $venues->groupBy('city');
    $state = [
        'topic' => (string) old('topic', $selected['topic'] ?? ''),
        'date' => (string) old('schedule_date_id', $selected['schedule_date_id'] ?? ''),
        'venue' => (string) old('venue_id', $selected['venue_id'] ?? ''),
        'customTopic' => old('custom_topic', ''),
        'topicLabels' => $topics->mapWithKeys(fn ($t) => [(string) $t->id => $t->title]),
        'dateLabels' => $dates->mapWithKeys(fn ($d) => [(string) $d->id => $d->label.' ('.$d->days_label.')']),
        'venueLabels' => $venues->mapWithKeys(fn ($v) => [(string) $v->id => $v->name.', '.$v->city]),
        'topicSlugs' => $topics->mapWithKeys(fn ($t) => [(string) $t->id => $t->slug]),
    ];
@endphp
<x-page-header title="Daftar Bimtek" eyebrow="Pendaftaran Online"
    subtitle="Pilih tema, tanggal, dan kota pelaksanaan. Satu formulir bisa untuk beberapa peserta dari instansi yang sama."
    :crumbs="['Jadwal' => route('schedule.index'), 'Daftar' => null]"/>

<div class="container-x pt-6 sm:pt-10">
    <ol class="chip-row gap-3 sm:grid sm:grid-cols-4 sm:gap-4">
        @foreach([
            ['Pilih tema, tanggal & kota', 'Tema belum ada? Usulkan.'],
            ['Isi data peserta', 'Bisa satu rombongan.'],
            ['Konfirmasi biaya via WA', 'Admin menghubungi Anda.'],
            ['Bayar & ikuti kegiatan', 'Kuitansi & sertifikat terbit.'],
        ] as $i => [$title, $desc])
            <li class="flex w-56 items-start gap-3 rounded-2xl border border-slate-200 bg-white p-3 sm:w-auto sm:p-4">
                <span class="grid size-7 shrink-0 place-items-center rounded-full bg-brand-600 text-xs font-bold text-white">{{ $i + 1 }}</span>
                <span>
                    <span class="block text-sm font-semibold text-slate-900">{{ $title }}</span>
                    <span class="block text-xs text-slate-500">{{ $desc }}</span>
                </span>
            </li>
        @endforeach
    </ol>
</div>

<div class="container-x grid gap-8 py-6 sm:py-10 lg:grid-cols-3 lg:gap-10" x-data="registrationForm(@js($state))">
    <div class="lg:col-span-2">
        @if($dates->isEmpty() || $venues->isEmpty())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
                Belum ada jadwal yang dibuka untuk pendaftaran. Silakan <a href="{{ wa_link('Halo Admin, saya ingin menanyakan jadwal bimtek terbaru.') }}" class="font-semibold underline" target="_blank" rel="noopener">hubungi admin via WhatsApp</a>.
            </div>
        @else
        <form method="POST" action="{{ route('register.store') }}" class="space-y-6 sm:space-y-8" @submit="submitting = true">
            @csrf
            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

            @if($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                    <p class="font-semibold">Mohon periksa kembali isian Anda.</p>
                </div>
            @endif

            <fieldset class="card p-4 sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-8 place-items-center rounded-full bg-brand-600 text-sm font-bold text-white">1</span>
                    <h2 class="text-lg font-bold">Pilih Kegiatan</h2>
                </div>

                <div class="mt-5 space-y-6 sm:mt-6">
                    <div>
                        <label for="topic" class="label">Tema bimtek <span class="text-rose-600">*</span></label>
                        <select id="topic" name="topic" x-model="topic" required class="input">
                            <option value="">Pilih tema</option>
                            @foreach($byCategory as $cat => $items)
                                <optgroup label="{{ $cat }}">
                                    @foreach($items as $tp)<option value="{{ $tp->id }}">{{ $tp->title }}</option>@endforeach
                                </optgroup>
                            @endforeach
                            <option value="other">Tema lain (usulkan sendiri)…</option>
                        </select>
                        @error('topic')<p class="error">{{ $message }}</p>@enderror
                        <div x-show="topic === 'other'" x-collapse x-cloak class="mt-3">
                            <input name="custom_topic" x-model="customTopic" :required="topic === 'other'" class="input" placeholder="Tuliskan tema yang dibutuhkan instansi Anda">
                            <p class="mt-1.5 text-xs text-slate-500">Tema usulan akan dikonfirmasi admin terlebih dahulu sebelum kegiatan ditetapkan.</p>
                            @error('custom_topic')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <a :href="topic && topic !== 'other' ? '{{ url('/tema') }}/' + topicSlugs[topic] : '{{ route('topics.index') }}'" target="_blank" class="tap mt-2 inline-flex items-center gap-1 text-xs font-medium text-brand-700 hover:underline">
                            <x-heroicon-m-information-circle class="size-4"/> <span x-text="topic && topic !== 'other' ? 'Lihat materi tema ini' : 'Lihat katalog tema'"></span>
                        </a>
                    </div>

                    <div>
                        <label for="schedule_date_id" class="label">Tanggal pelaksanaan <span class="text-rose-600">*</span></label>
                        <select id="schedule_date_id" name="schedule_date_id" x-model="date" required class="input">
                            <option value="">Pilih tanggal</option>
                            @foreach($byMonth as $month => $items)
                                <optgroup label="{{ $month }}">
                                    @foreach($items as $d)<option value="{{ $d->id }}">{{ $d->label }} ({{ $d->days_label }}){{ $d->note ? ' · '.$d->note : '' }}</option>@endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-xs text-slate-500">Pendaftaran tiap jadwal ditutup H-{{ \App\Models\ScheduleDate::closeDays() }} sebelum kegiatan. <a href="{{ route('schedule.index') }}" target="_blank" class="font-medium text-brand-700 hover:underline">Lihat jadwal setahun</a></p>
                        @error('schedule_date_id')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <p class="label">Kota & hotel pelaksanaan <span class="text-rose-600">*</span></p>
                        @error('venue_id')<p class="error mb-2">{{ $message }}</p>@enderror
                        <div class="grid grid-cols-2 gap-2 sm:gap-3">
                            @foreach($byCity as $city => $items)
                                @foreach($items as $v)
                                    <label class="flex cursor-pointer gap-2 rounded-xl border border-slate-200 p-2.5 transition sm:gap-3 sm:p-3.5 has-checked:border-brand-500 has-checked:bg-brand-50/50 has-checked:ring-4 has-checked:ring-brand-500/10">
                                        <input type="radio" name="venue_id" value="{{ $v->id }}" x-model="venue" class="mt-0.5 shrink-0 accent-brand-600 sm:mt-1" required>
                                        <span class="min-w-0">
                                            <span class="block text-[11px] font-bold tracking-wide text-brand-700 uppercase sm:text-xs">{{ $city }}</span>
                                            <span class="block text-[13px] leading-snug font-semibold text-slate-900 sm:text-sm">{{ $v->name }}</span>
                                            @if($v->address)<span class="mt-0.5 hidden text-xs text-slate-500 sm:block">{{ $v->address }}</span>@endif
                                        </span>
                                    </label>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            </fieldset>

            <div class="flex gap-3 rounded-2xl border border-brand-100 bg-brand-50 p-4 text-sm sm:p-5 text-brand-900">
                <x-heroicon-o-chat-bubble-left-right class="size-6 shrink-0 text-brand-600"/>
                <div>
                    <p class="font-semibold">Informasi biaya</p>
                    <p class="mt-1">{{ setting('price_notice') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="grid size-8 place-items-center rounded-full bg-brand-600 text-sm font-bold text-white">2</span>
                <h2 class="text-lg font-bold">Data Instansi & Peserta</h2>
            </div>

            @include('registrations.partials.people')

            <div class="flex items-center gap-3">
                <span class="grid size-8 place-items-center rounded-full bg-brand-600 text-sm font-bold text-white">3</span>
                <h2 class="text-lg font-bold">Kirim Pendaftaran</h2>
            </div>

            {{-- Mobile summary (sidebar is below the form on small screens) --}}
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm lg:hidden">
                <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Periksa pilihan Anda</p>
                <dl class="mt-3 grid gap-2">
                    <div class="flex gap-2"><dt class="w-20 shrink-0 text-slate-500">Tema</dt><dd class="font-semibold text-slate-900" x-text="topicText || 'Belum dipilih'"></dd></div>
                    <div class="flex gap-2"><dt class="w-20 shrink-0 text-slate-500">Tanggal</dt><dd class="font-semibold text-slate-900" x-text="dateLabels[date] || 'Belum dipilih'"></dd></div>
                    <div class="flex gap-2"><dt class="w-20 shrink-0 text-slate-500">Tempat</dt><dd class="font-semibold text-slate-900" x-text="venueLabels[venue] || 'Belum dipilih'"></dd></div>
                    <div class="flex gap-2"><dt class="w-20 shrink-0 text-slate-500">Peserta</dt><dd class="font-semibold text-slate-900" x-text="people.length + ' orang'"></dd></div>
                </dl>
            </div>

            @include('registrations.partials.finish')
        </form>
        @endif
    </div>

    <aside>
        <div class="sticky top-24 space-y-5">
            <div class="card hidden p-5 lg:block">
                <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Ringkasan pilihan</p>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex gap-3"><x-heroicon-o-book-open class="mt-0.5 size-4 shrink-0 text-slate-400"/><div><dt class="text-xs text-slate-500">Tema</dt><dd class="font-semibold text-slate-900" x-text="topicText || 'Belum dipilih'"></dd></div></div>
                    <div class="flex gap-3"><x-heroicon-o-calendar-days class="mt-0.5 size-4 shrink-0 text-slate-400"/><div><dt class="text-xs text-slate-500">Tanggal</dt><dd class="font-semibold text-slate-900" x-text="dateLabels[date] || 'Belum dipilih'"></dd></div></div>
                    <div class="flex gap-3"><x-heroicon-o-map-pin class="mt-0.5 size-4 shrink-0 text-slate-400"/><div><dt class="text-xs text-slate-500">Tempat</dt><dd class="font-semibold text-slate-900" x-text="venueLabels[venue] || 'Belum dipilih'"></dd></div></div>
                    <div class="flex gap-3"><x-heroicon-o-users class="mt-0.5 size-4 shrink-0 text-slate-400"/><div><dt class="text-xs text-slate-500">Peserta</dt><dd class="font-semibold text-slate-900" x-text="people.length + ' orang'"></dd></div></div>
                </dl>
            </div>

            <div class="card bg-slate-950 p-5 text-sm text-slate-300">
                <p class="font-semibold text-white">Butuh bantuan?</p>
                <p class="mt-1">Admin siap membantu memilih tema, jadwal, dan menjelaskan biaya kontribusi.</p>
                <a href="{{ wa_link('Halo Admin, saya ingin bertanya tentang pendaftaran bimtek.') }}" target="_blank" rel="noopener" class="btn btn-wa mt-5 w-full">Tanya Admin via WhatsApp</a>
            </div>
        </div>
    </aside>
</div>
@endsection

@include('registrations.partials.script', ['prices' => []])
