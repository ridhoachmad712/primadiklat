@extends('layouts.app')

@section('title', 'Pendaftaran Berhasil')

@section('content')
@php
    $r = $registration; $t = $r->training;
    $wa = "Halo Admin, saya sudah mendaftar.\nKode: {$r->code}\nNama: {$r->name}\nInstansi: {$r->institution}\nKegiatan: {$t->title} ({$t->date_range})";
@endphp
<section class="container-x max-w-3xl py-14">
    <div class="text-center">
        <span class="mx-auto grid size-16 place-items-center rounded-full bg-emerald-50 text-emerald-600 ring-8 ring-emerald-50/50"><x-heroicon-s-check-circle class="size-9"/></span>
        <h1 class="mt-6 text-3xl font-extrabold">Pendaftaran Berhasil</h1>
        <p class="mt-2 text-slate-600">Terima kasih, {{ $r->name }}. Data Anda sudah kami terima.</p>
    </div>

    <div class="card mt-10 overflow-hidden">
        <div class="flex flex-col items-center justify-between gap-2 border-b border-dashed border-slate-200 bg-slate-50 p-6 sm:flex-row">
            <div>
                <p class="text-xs text-slate-500">Kode pendaftaran</p>
                <p class="font-mono text-2xl font-bold tracking-wider text-slate-900" x-data>{{ $r->code }}</p>
            </div>
            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">{{ $r->status_label }}</span>
        </div>
        <dl class="grid gap-4 p-6 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500">Kegiatan</dt><dd class="font-semibold text-slate-900">{{ $t->title }}</dd></div>
            <div><dt class="text-slate-500">Tanggal</dt><dd class="font-semibold text-slate-900">{{ $t->date_range }}</dd></div>
            <div><dt class="text-slate-500">Tempat</dt><dd class="font-semibold text-slate-900">{{ $t->location }}</dd></div>
            <div><dt class="text-slate-500">Paket</dt><dd class="font-semibold text-slate-900">{{ $r->package ?? '-' }} @if($r->price)· {{ rupiah($r->price) }}@endif</dd></div>
            <div><dt class="text-slate-500">Instansi</dt><dd class="font-semibold text-slate-900">{{ $r->institution }}</dd></div>
            <div><dt class="text-slate-500">Kontak</dt><dd class="font-semibold text-slate-900">{{ $r->phone }} · {{ $r->email }}</dd></div>
        </dl>
        @if(setting('bank_info'))
            <div class="border-t border-slate-100 p-6 text-sm">
                <p class="font-semibold text-slate-900">Informasi pembayaran</p>
                <p class="mt-2 whitespace-pre-line text-slate-600">{{ setting('bank_info') }}</p>
            </div>
        @endif
    </div>

    <div class="mt-6 rounded-2xl bg-brand-50 p-5 text-sm text-brand-900">
        <p class="font-semibold">Langkah selanjutnya</p>
        <ol class="mt-2 list-decimal space-y-1 pl-5">
            <li>Simpan kode pendaftaran Anda. Konfirmasi juga dikirim ke email.</li>
            <li>Unduh surat undangan resmi untuk proses SPPD di instansi Anda.</li>
            <li>Kirim konfirmasi ke admin melalui WhatsApp agar segera diverifikasi.</li>
        </ol>
    </div>

    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
        <a href="{{ wa_link($wa) }}" target="_blank" rel="noopener" class="btn btn-wa">Konfirmasi via WhatsApp</a>
        <a href="{{ route('trainings.invitation', $t) }}" target="_blank" class="btn btn-outline"><x-heroicon-o-arrow-down-tray class="size-4"/> Unduh Surat Undangan</a>
        <button type="button" onclick="window.print()" class="btn btn-ghost"><x-heroicon-o-printer class="size-4"/> Cetak</button>
    </div>
</section>
@endsection
