@extends('pdf.layout')

@section('title', 'Invoice '.$invoice->number)

@section('content')
@php
    $inv = $invoice; $t = $inv->training;
    $people = $inv->registrations->where('status', '!=', 'cancelled')->values();
    $due = $t->registration_deadline ?? $t->start_date;
@endphp
<table>
    <tr>
        <td style="vertical-align: top;">
            <p class="doc-title">INVOICE</p>
            <p class="muted" style="margin: 2px 0 0;">No. {{ $inv->number }}</p>
        </td>
        <td class="right" style="vertical-align: top;">
            @if($inv->status === 'paid')<span class="stamp">LUNAS</span>@endif
        </td>
    </tr>
</table>

<table style="margin-top: 16px;">
    <tr>
        <td style="width: 55%; vertical-align: top; padding-right: 16px;">
            <div class="box">
                <div class="small">Ditagihkan kepada</div>
                <div style="font-weight: bold; margin-top: 2px;">{{ $inv->institution }}</div>
                <div>u.p. {{ $inv->contact_name }}</div>
                @if($people->first()?->institution_address)<div class="muted">{{ $people->first()->institution_address }}{{ $people->first()->city ? ', '.$people->first()->city : '' }}</div>@endif
                <div class="muted">{{ $inv->contact_phone }} · {{ $inv->contact_email }}</div>
            </div>
        </td>
        <td style="vertical-align: top;">
            <table class="info">
                <tr><td class="muted" style="width: 95px;">Tanggal</td><td>{{ $inv->created_at->translatedFormat('j F Y') }}</td></tr>
                <tr><td class="muted">Jatuh tempo</td><td>{{ $due?->translatedFormat('j F Y') ?? '-' }}</td></tr>
                <tr><td class="muted">Status</td><td><strong>{{ $inv->status_label }}</strong></td></tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin: 18px 0 6px;"><strong>Kegiatan:</strong> {{ $t->title }}<br>
<span class="muted">{{ $t->date_range }} · {{ $t->location }}</span></p>

<table class="grid">
    <tr><th style="width: 28px;">No</th><th>Nama Peserta</th><th>Paket</th><th class="right" style="width: 110px;">Biaya</th></tr>
    @foreach($people as $i => $p)
        <tr>
            <td class="center">{{ $i + 1 }}</td>
            <td>{{ $p->name }}@if($p->nip)<br><span class="small">NIP. {{ $p->nip }}</span>@endif</td>
            <td>{{ $p->package ?? '-' }}</td>
            <td class="right">{{ rupiah($p->price) }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="3" class="right"><strong>TOTAL</strong></td>
        <td class="right"><strong>{{ rupiah($inv->amount) }}</strong></td>
    </tr>
</table>
<p style="margin: 6px 0 0;"><em>Terbilang: {{ ucfirst(terbilang($inv->amount)) }} rupiah</em></p>

@if($inv->status !== 'paid' && setting('bank_info'))
<div class="box" style="margin-top: 16px;">
    <strong>Pembayaran melalui transfer ke:</strong>
    <div style="white-space: pre-line;">{{ setting('bank_info') }}</div>
    <div class="small" style="margin-top: 6px;">Cantumkan nomor invoice pada berita transfer. Unggah bukti transfer di {{ route('registrations.show', $inv->registrations->first()->code) }}</div>
</div>
@endif

<div class="sign">
    <p style="margin: 0;">{{ setting('invitation_signatory') }},</p>
    <div style="height: 56px;"></div>
    <p style="margin: 0;"><strong><u>{{ setting('invitation_signatory_name') }}</u></strong></p>
</div>
@endsection
