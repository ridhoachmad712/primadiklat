@extends('layouts.app')

@section('title', 'Status Pendaftaran '.$registration->code)

@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
@php
    $r = $registration; $t = $r->training; $inv = $invoice; $people = $inv->registrations;
    $wa = "Halo Admin, saya sudah mendaftar.\nKode: {$r->code}\nTagihan: {$inv->number}\nInstansi: {$inv->institution}\nJumlah peserta: {$people->count()}\nKegiatan: {$t->title} ({$t->date_range})";
    $badge = ['unpaid' => 'bg-slate-100 text-slate-700 ring-slate-200', 'waiting' => 'bg-amber-50 text-amber-700 ring-amber-200', 'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200'][$inv->status] ?? '';
@endphp
<section class="container-x max-w-3xl py-14">
    <div class="text-center">
        @if($inv->status === 'paid')
            <span class="mx-auto grid size-16 place-items-center rounded-full bg-emerald-50 text-emerald-600 ring-8 ring-emerald-50/50"><x-heroicon-s-check-badge class="size-9"/></span>
            <h1 class="mt-6 text-3xl font-extrabold">Pembayaran Lunas</h1>
            <p class="mt-2 text-slate-600">Terima kasih. Sampai jumpa di kegiatan!</p>
        @else
            <span class="mx-auto grid size-16 place-items-center rounded-full bg-emerald-50 text-emerald-600 ring-8 ring-emerald-50/50"><x-heroicon-s-check-circle class="size-9"/></span>
            <h1 class="mt-6 text-3xl font-extrabold">Pendaftaran Berhasil</h1>
            <p class="mt-2 text-slate-600">Terima kasih, {{ rtrim($inv->contact_name, '.') }}. Data {{ $people->count() > 1 ? $people->count().' peserta' : 'Anda' }} sudah kami terima.</p>
        @endif
    </div>

    @if(session('success'))
        <div class="mt-8 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="card mt-10 overflow-hidden">
        <div class="flex flex-col items-center justify-between gap-3 border-b border-dashed border-slate-200 bg-slate-50 p-6 sm:flex-row">
            <div class="text-center sm:text-left">
                <p class="text-xs text-slate-500">Kode pendaftaran</p>
                <p class="font-mono text-2xl font-bold tracking-wider text-slate-900">{{ $r->code }}</p>
                <p class="mt-1 text-xs text-slate-500">No. tagihan <span class="font-mono">{{ $inv->number }}</span></p>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $badge }}">{{ $inv->status_label }}</span>
        </div>
        <dl class="grid gap-4 p-6 text-sm sm:grid-cols-2">
            <div class="sm:col-span-2"><dt class="text-slate-500">Kegiatan</dt><dd class="font-semibold text-slate-900">{{ $t->title }}</dd></div>
            <div><dt class="text-slate-500">Tanggal</dt><dd class="font-semibold text-slate-900">{{ $t->date_range }}</dd></div>
            <div><dt class="text-slate-500">Tempat</dt><dd class="font-semibold text-slate-900">{{ $t->location }}</dd></div>
            <div><dt class="text-slate-500">Instansi</dt><dd class="font-semibold text-slate-900">{{ $inv->institution }}</dd></div>
            <div><dt class="text-slate-500">Kontak</dt><dd class="font-semibold text-slate-900">{{ $inv->contact_phone }} · {{ $inv->contact_email }}</dd></div>
        </dl>

        <div class="border-t border-slate-100 px-6 py-5">
            <p class="text-sm font-semibold text-slate-900">Peserta ({{ $people->count() }})</p>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs text-slate-500">
                        <tr class="border-b border-slate-100"><th class="py-2 pr-3 font-medium">Nama</th><th class="py-2 pr-3 font-medium">Kode</th><th class="py-2 pr-3 font-medium">Paket</th><th class="py-2 text-right font-medium">Biaya</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($people as $p)
                            <tr @class(['text-slate-400 line-through' => $p->status === 'cancelled'])>
                                <td class="py-2.5 pr-3"><span class="font-medium text-slate-900">{{ $p->name }}</span>@if($p->position)<span class="block text-xs text-slate-500">{{ $p->position }}</span>@endif</td>
                                <td class="py-2.5 pr-3 font-mono text-xs">{{ $p->code }}</td>
                                <td class="py-2.5 pr-3">{{ $p->package ?? '-' }}</td>
                                <td class="py-2.5 text-right">{{ $p->price ? rupiah($p->price) : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-slate-200"><td colspan="3" class="pt-3 font-semibold text-slate-900">Total tagihan</td><td class="pt-3 text-right text-lg font-bold text-brand-700">{{ rupiah($inv->amount) }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 border-t border-slate-100 p-6">
            <a href="{{ route('registrations.invoice', $r->code) }}" target="_blank" class="btn btn-outline"><x-heroicon-o-document-text class="size-4"/> Invoice</a>
            @if($inv->status === 'paid')
                <a href="{{ route('registrations.receipt', $r->code) }}" target="_blank" class="btn btn-primary"><x-heroicon-o-receipt-percent class="size-4"/> Kuitansi</a>
            @endif
            <a href="{{ route('trainings.invitation', $t) }}" target="_blank" class="btn btn-outline"><x-heroicon-o-envelope-open class="size-4"/> Surat Undangan</a>
        </div>
    </div>

    {{-- Payment --}}
    @if($inv->amount > 0 && $inv->status !== 'cancelled')
    <div class="card mt-6 p-6">
        <h2 class="text-lg font-bold">Pembayaran</h2>

        @if($inv->status === 'paid')
            <p class="mt-2 text-sm text-slate-600">Pembayaran telah diverifikasi pada {{ $inv->paid_at?->translatedFormat('j F Y') }}. Nomor kuitansi <span class="font-mono font-semibold">{{ $inv->receipt_number }}</span>.</p>
        @else
            @if($inv->admin_note)
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                    <p class="font-semibold">Bukti pembayaran sebelumnya belum dapat diterima</p>
                    <p class="mt-1">{{ $inv->admin_note }}</p>
                </div>
            @endif

            <div class="mt-4 grid gap-6 sm:grid-cols-2">
                <div class="text-sm">
                    @if(setting('bank_info'))
                        <p class="font-semibold text-slate-900">Transfer ke</p>
                        <p class="mt-1 whitespace-pre-line text-slate-600">{{ setting('bank_info') }}</p>
                    @endif
                    <p class="mt-3 font-semibold text-slate-900">Jumlah</p>
                    <p class="text-xl font-bold text-brand-700">{{ rupiah($inv->amount) }}</p>
                    @if(setting('payment_instructions'))<p class="mt-3 text-xs text-slate-500">{{ setting('payment_instructions') }}</p>@endif
                </div>

                <div>
                    @if($inv->status === 'waiting')
                        <div class="rounded-xl bg-amber-50 p-4 text-sm text-amber-800">
                            <p class="font-semibold">Bukti pembayaran sedang diverifikasi</p>
                            <p class="mt-1">Diunggah {{ $inv->proof_uploaded_at?->translatedFormat('j F Y, H:i') }}. Anda akan menerima email setelah diverifikasi.</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('registrations.proof', $r->code) }}" enctype="multipart/form-data" class="mt-3" x-data="{ name: '' }">
                        @csrf
                        <label class="label" for="proof">{{ $inv->status === 'waiting' ? 'Ganti bukti pembayaran' : 'Unggah bukti pembayaran' }}</label>
                        <label for="proof" class="flex cursor-pointer flex-col items-center gap-1 rounded-xl border-2 border-dashed border-slate-200 px-4 py-6 text-center text-sm text-slate-500 transition hover:border-brand-400 hover:bg-brand-50/40">
                            <x-heroicon-o-arrow-up-tray class="size-6 text-slate-400"/>
                            <span x-text="name || 'Pilih file JPG, PNG, atau PDF (maks. 4 MB)'"></span>
                        </label>
                        <input id="proof" name="proof" type="file" accept=".jpg,.jpeg,.png,.pdf" required class="sr-only" @change="name = $event.target.files[0]?.name || ''">
                        @error('proof')<p class="error">{{ $message }}</p>@enderror
                        <button class="btn btn-primary mt-3 w-full">Kirim Bukti Pembayaran</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
    @endif

    <div class="mt-6 rounded-2xl bg-brand-50 p-5 text-sm text-brand-900">
        <p class="font-semibold">Langkah selanjutnya</p>
        <ol class="mt-2 list-decimal space-y-1 pl-5">
            <li>Simpan tautan halaman ini atau kode pendaftaran. Konfirmasi juga dikirim ke email kontak.</li>
            <li>Unduh surat undangan dan invoice untuk proses SPPD/pencairan di instansi Anda.</li>
            <li>Lakukan pembayaran lalu unggah buktinya di halaman ini. Kuitansi resmi terbit setelah diverifikasi.</li>
            <li>Setelah kegiatan selesai, sertifikat dapat diunduh di menu <a href="{{ route('certificates.index') }}" class="font-semibold underline">Cek Sertifikat</a>.</li>
        </ol>
    </div>

    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
        <a href="{{ wa_link($wa) }}" target="_blank" rel="noopener" class="btn btn-wa">Hubungi Admin via WhatsApp</a>
        <button type="button" onclick="window.print()" class="btn btn-ghost"><x-heroicon-o-printer class="size-4"/> Cetak</button>
    </div>
</section>
@endsection
