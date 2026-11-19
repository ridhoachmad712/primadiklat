@extends('layouts.app')

@section('title', 'In-House Training')
@section('description', 'Selenggarakan bimtek dan diklat khusus di instansi Anda. Materi, jadwal, dan lokasi disesuaikan dengan kebutuhan.')

@section('content')
<x-page-header title="In-House Training" eyebrow="Pelatihan Khusus Instansi" subtitle="Pelatihan yang dirancang khusus untuk instansi Anda: materi, jadwal, narasumber, dan lokasi menyesuaikan kebutuhan." :crumbs="['In-House Training' => null]"/>

<div class="container-x grid gap-12 py-12 lg:grid-cols-5">
    <div class="lg:col-span-2">
        <h2 class="text-xl font-bold">Keuntungan in-house training</h2>
        <ul class="mt-6 space-y-5">
            @foreach([
                ['Materi disesuaikan', 'Kurikulum dirancang dari permasalahan nyata di instansi Anda.'],
                ['Lebih hemat', 'Tanpa biaya perjalanan dinas untuk seluruh peserta.'],
                ['Jadwal fleksibel', 'Waktu pelaksanaan mengikuti agenda instansi.'],
                ['Peserta lebih banyak', 'Seluruh tim dapat mengikuti dalam satu kegiatan.'],
            ] as [$title, $desc])
                <li class="flex gap-4">
                    <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700"><x-heroicon-o-check class="size-5"/></span>
                    <div><p class="font-semibold text-slate-900">{{ $title }}</p><p class="text-sm text-slate-600">{{ $desc }}</p></div>
                </li>
            @endforeach
        </ul>
        <div class="card mt-8 p-5 text-sm">
            <p class="font-semibold text-slate-900">Lebih suka bicara langsung?</p>
            <p class="mt-1 text-slate-600">Tim kami siap berdiskusi via WhatsApp.</p>
            <a href="{{ wa_link('Halo Admin, saya ingin konsultasi in-house training.') }}" target="_blank" rel="noopener" class="btn btn-wa mt-4">Chat WhatsApp</a>
        </div>
    </div>

    <div class="lg:col-span-3">
        @if(session('success'))
            <div class="mb-6 flex gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-800"><x-heroicon-s-check-circle class="size-6 shrink-0"/><p>{{ session('success') }}</p></div>
        @endif
        <form method="POST" action="{{ route('inhouse.store') }}" class="card space-y-5 p-4 sm:p-8">
            @csrf
            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
            <div>
                <label class="label" for="institution">Nama instansi <span class="text-rose-600">*</span></label>
                <input id="institution" name="institution" value="{{ old('institution') }}" required class="input">
                @error('institution')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="label" for="contact_name">Nama penanggung jawab <span class="text-rose-600">*</span></label>
                    <input id="contact_name" name="contact_name" value="{{ old('contact_name') }}" required class="input">
                    @error('contact_name')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="position">Jabatan</label>
                    <input id="position" name="position" value="{{ old('position') }}" class="input">
                </div>
                <div>
                    <label class="label" for="email">Email <span class="text-rose-600">*</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required class="input">
                    @error('email')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="phone">No. HP / WhatsApp <span class="text-rose-600">*</span></label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required class="input">
                    @error('phone')<p class="error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="label" for="topic">Topik pelatihan yang dibutuhkan <span class="text-rose-600">*</span></label>
                <input id="topic" name="topic" value="{{ old('topic') }}" required class="input" placeholder="Contoh: Penyusunan Laporan Keuangan SKPD">
                @error('topic')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-5 sm:grid-cols-3">
                <div>
                    <label class="label" for="participants">Jumlah peserta</label>
                    <input id="participants" type="number" min="1" name="participants" value="{{ old('participants') }}" class="input">
                    @error('participants')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="preferred_date">Rencana tanggal</label>
                    <input id="preferred_date" type="date" name="preferred_date" value="{{ old('preferred_date') }}" class="input">
                    @error('preferred_date')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="location">Lokasi</label>
                    <input id="location" name="location" value="{{ old('location') }}" class="input" placeholder="Kota / tempat">
                </div>
            </div>
            <div>
                <label class="label" for="message">Keterangan tambahan</label>
                <textarea id="message" name="message" rows="4" class="input">{{ old('message') }}</textarea>
            </div>
            <button class="btn btn-primary w-full !py-3 sm:w-auto">Kirim Permintaan</button>
        </form>
    </div>
</div>
@endsection
