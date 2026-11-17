@extends('layouts.app')

@section('title', 'Pendaftaran: '.$training->title)

@section('content')
@php $t = $training; @endphp
<x-page-header title="Formulir Pendaftaran" eyebrow="Daftar Online" :subtitle="$t->title"
    :crumbs="['Jadwal' => route('trainings.index'), \Illuminate\Support\Str::limit($t->title, 40) => route('trainings.show', $t), 'Daftar' => null]"/>

<div class="container-x grid gap-10 py-10 lg:grid-cols-3">
    <div class="lg:col-span-2">
        @if(! $t->can_register)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
                Pendaftaran online untuk kegiatan ini sudah ditutup ({{ $t->availability_label }}). Silakan hubungi admin melalui WhatsApp.
            </div>
        @else
        <form method="POST" action="{{ route('registrations.store', $t) }}" class="space-y-8" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

            @if($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                    <p class="font-semibold">Mohon periksa kembali isian Anda.</p>
                    @error('training')<p class="mt-1">{{ $message }}</p>@enderror
                </div>
            @endif

            <fieldset class="card p-6">
                <legend class="sr-only">Data peserta</legend>
                <h2 class="text-lg font-bold">Data Peserta</h2>
                <p class="mt-1 text-sm text-slate-500">Nama akan dicetak pada sertifikat. Pastikan penulisan dan gelar sudah benar.</p>
                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="label">Nama lengkap & gelar <span class="text-rose-500">*</span></label>
                        <input id="name" name="name" value="{{ old('name') }}" required class="input" autocomplete="name">
                        @error('name')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="nip" class="label">NIP / NIK</label>
                        <input id="nip" name="nip" value="{{ old('nip') }}" class="input" inputmode="numeric">
                        @error('nip')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="position" class="label">Jabatan</label>
                        <input id="position" name="position" value="{{ old('position') }}" class="input" placeholder="Kasubbag Keuangan">
                    </div>
                    <div>
                        <label for="email" class="label">Email <span class="text-rose-500">*</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required class="input" autocomplete="email">
                        @error('email')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone" class="label">No. HP / WhatsApp <span class="text-rose-500">*</span></label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required class="input" placeholder="08xxxxxxxxxx" autocomplete="tel">
                        @error('phone')<p class="error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </fieldset>

            <fieldset class="card p-6">
                <h2 class="text-lg font-bold">Instansi</h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="institution" class="label">Nama instansi / OPD <span class="text-rose-500">*</span></label>
                        <input id="institution" name="institution" value="{{ old('institution') }}" required class="input" placeholder="BPKAD Kabupaten …">
                        @error('institution')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="institution_address" class="label">Alamat instansi</label>
                        <input id="institution_address" name="institution_address" value="{{ old('institution_address') }}" class="input">
                    </div>
                    <div>
                        <label for="city" class="label">Kota / Kabupaten</label>
                        <input id="city" name="city" value="{{ old('city') }}" class="input">
                    </div>
                </div>
            </fieldset>

            @if($t->prices)
            <fieldset class="card p-6">
                <h2 class="text-lg font-bold">Pilih Paket</h2>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    @foreach($t->prices as $i => $p)
                        <label class="relative flex cursor-pointer gap-3 rounded-xl border border-slate-200 p-4 transition has-checked:border-brand-500 has-checked:bg-brand-50/50 has-checked:ring-4 has-checked:ring-brand-500/10">
                            <input type="radio" name="package" value="{{ $p['name'] }}" class="mt-1 accent-brand-600" @checked(old('package', $t->prices[0]['name']) === $p['name']) required>
                            <span>
                                <span class="block text-sm font-semibold text-slate-900">{{ $p['name'] }}</span>
                                <span class="block text-base font-bold text-brand-700">{{ rupiah($p['price']) }}</span>
                                @if(! empty($p['note']))<span class="mt-1 block text-xs text-slate-500">{{ $p['note'] }}</span>@endif
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('package')<p class="error">{{ $message }}</p>@enderror
            </fieldset>
            @endif

            <div class="card p-6">
                <label for="notes" class="label">Catatan (opsional)</label>
                <textarea id="notes" name="notes" rows="3" class="input" placeholder="Contoh: mendaftar 3 orang dari instansi yang sama, kebutuhan khusus, dll.">{{ old('notes') }}</textarea>
                <label class="mt-5 flex items-start gap-3 text-sm text-slate-600">
                    <input type="checkbox" name="agree" value="1" class="mt-0.5 size-4 accent-brand-600" @checked(old('agree')) required>
                    <span>Saya menyetujui <a href="{{ url('/halaman/syarat-dan-ketentuan') }}" target="_blank" class="font-medium text-brand-700 hover:underline">syarat & ketentuan</a> serta <a href="{{ url('/halaman/kebijakan-privasi') }}" target="_blank" class="font-medium text-brand-700 hover:underline">kebijakan privasi</a>.</span>
                </label>
                @error('agree')<p class="error">{{ $message }}</p>@enderror
            </div>

            <button class="btn btn-primary w-full !py-3.5 text-base sm:w-auto" :disabled="submitting">
                <span x-show="!submitting">Kirim Pendaftaran</span>
                <span x-show="submitting" x-cloak>Mengirim…</span>
            </button>
        </form>
        @endif
    </div>

    <aside>
        <div class="card sticky top-24 overflow-hidden">
            <div class="aspect-[16/9] bg-slate-100">
                @if($t->thumbnail_url)<img src="{{ $t->thumbnail_url }}" alt="" class="size-full object-cover">@else<x-placeholder-cover :seed="$t->id" :label="$t->type_label"/>@endif
            </div>
            <div class="p-5">
                <h3 class="font-bold leading-snug">{{ $t->title }}</h3>
                <dl class="mt-4 space-y-2 text-sm text-slate-600">
                    <div class="flex gap-2"><x-heroicon-o-calendar-days class="size-4 shrink-0 text-slate-400"/>{{ $t->date_range }}</div>
                    <div class="flex gap-2"><x-heroicon-o-map-pin class="size-4 shrink-0 text-slate-400"/>{{ $t->location }}</div>
                    @if($t->quota)<div class="flex gap-2"><x-heroicon-o-users class="size-4 shrink-0 text-slate-400"/>Sisa {{ $t->remaining_quota }} kursi</div>@endif
                </dl>
                <a href="{{ route('trainings.invitation', $t) }}" target="_blank" class="btn btn-outline mt-5 w-full"><x-heroicon-o-arrow-down-tray class="size-4"/> Surat Undangan</a>
            </div>
        </div>
    </aside>
</div>
@endsection
