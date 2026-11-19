@extends('layouts.app')

@section('title', 'Cek Sertifikat')
@if(request()->filled('kode')) @section('noindex', '1') @endif
@section('description', 'Cek keabsahan dan unduh sertifikat peserta bimtek dan diklat Prima Diklat.')

@section('content')
<x-page-header title="Cek & Unduh Sertifikat" eyebrow="Layanan Peserta" subtitle="Masukkan kode pendaftaran atau nomor sertifikat untuk memeriksa keabsahan dan mengunduh sertifikat Anda." :crumbs="['Cek Sertifikat' => null]">
    <form method="GET" class="mt-8 flex max-w-xl flex-col gap-2 sm:flex-row">
        <label class="sr-only" for="kode">Kode</label>
        <input id="kode" name="kode" value="{{ $keyword }}" required placeholder="PD-2611-XXXXX atau PD/2026/0001" class="input !py-3 font-mono">
        <button class="btn btn-primary !py-3">Periksa</button>
    </form>
</x-page-header>

<section class="container-x max-w-3xl py-12">
    @if($keyword === '')
        <div class="grid gap-4 text-sm text-slate-600 sm:grid-cols-2">
            <div class="card p-5"><p class="font-semibold text-slate-900">Kode pendaftaran</p><p class="mt-1">Dikirim saat Anda mendaftar online, contoh <span class="font-mono">PD-2611-AB12C</span>.</p></div>
            <div class="card p-5"><p class="font-semibold text-slate-900">Nomor sertifikat</p><p class="mt-1">Tercetak pada sertifikat, contoh <span class="font-mono">PD/2026/0001</span>.</p></div>
        </div>
    @elseif(! $result)
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-6 text-rose-800">
            <p class="font-semibold">Data tidak ditemukan</p>
            <p class="mt-1 text-sm">Kode “{{ $keyword }}” tidak terdaftar. Periksa kembali penulisan, atau hubungi admin kami.</p>
            <a href="{{ wa_link('Halo Admin, saya ingin menanyakan sertifikat dengan kode '.$keyword) }}" target="_blank" rel="noopener" class="btn btn-wa mt-4">Hubungi Admin</a>
        </div>
    @else
        @php $r = $result; $valid = $r->status === 'attended' && $r->certificate_number; @endphp
        <div class="card overflow-hidden">
            <div @class(['flex items-center gap-3 p-5', 'bg-emerald-50 text-emerald-800' => $valid, 'bg-amber-50 text-amber-800' => ! $valid])>
                @if($valid)<x-heroicon-s-check-badge class="size-7"/>@else<x-heroicon-s-clock class="size-7"/>@endif
                <div>
                    <p class="font-bold">{{ $valid ? 'Sertifikat valid' : 'Sertifikat belum terbit' }}</p>
                    <p class="text-sm opacity-80">{{ $valid ? 'Peserta tercatat telah menyelesaikan kegiatan.' : 'Status: '.$r->status_label.'. Sertifikat terbit setelah kegiatan selesai.' }}</p>
                </div>
            </div>
            <dl class="grid gap-4 p-6 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">Nama peserta</dt><dd class="font-semibold text-slate-900">{{ $r->name }}</dd></div>
                <div><dt class="text-slate-500">Instansi</dt><dd class="font-semibold text-slate-900">{{ $r->institution }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500">Kegiatan</dt><dd class="font-semibold text-slate-900">{{ $r->training?->title }}</dd></div>
                <div><dt class="text-slate-500">Tanggal</dt><dd class="font-semibold text-slate-900">{{ $r->training?->date_range }}</dd></div>
                <div><dt class="text-slate-500">No. sertifikat</dt><dd class="font-mono font-semibold text-slate-900">{{ $r->certificate_number ?? '-' }}</dd></div>
            </dl>
            @if($valid)
                <div class="border-t border-slate-100 p-6">
                    @if($r->has_certificate)
                        <a href="{{ $r->certificate_download_url }}" target="_blank" class="btn btn-primary"><x-heroicon-o-arrow-down-tray class="size-4"/> Unduh Sertifikat (PDF)</a>
                    @else
                        <p class="text-sm text-slate-600">Sertifikat dapat diunduh setelah pembayaran diverifikasi. <a href="{{ route('registrations.show', $r->code) }}" class="font-semibold text-brand-700 hover:underline">Lihat status pembayaran</a>.</p>
                    @endif
                </div>
            @endif
        </div>
    @endif
</section>
@endsection
