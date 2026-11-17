@extends('pdf.layout')

@section('title', 'Kuitansi '.$invoice->receipt_number)

@section('style')
    .receipt td { padding: 9px 0; vertical-align: top; border-bottom: 1px dotted #cbd5e1; }
    .receipt td.label { width: 150px; color: #475569; }
    .amount { display: inline-block; border: 2px solid #1d4ed8; background: #eff6ff; padding: 8px 16px; font-size: 14pt; font-weight: bold; color: #1d4ed8; }
@endsection

@section('content')
@php
    $inv = $invoice; $t = $inv->training;
    $people = $inv->registrations->where('status', '!=', 'cancelled')->values();
@endphp
<table>
    <tr>
        <td><p class="doc-title">KUITANSI</p><p class="muted" style="margin: 2px 0 0;">No. {{ $inv->receipt_number }}</p></td>
        <td class="right"><span class="stamp">LUNAS</span></td>
    </tr>
</table>

<table class="receipt" style="margin-top: 18px;">
    <tr><td class="label">Telah terima dari</td><td><strong>{{ $inv->institution }}</strong> (u.p. {{ $inv->contact_name }})</td></tr>
    <tr><td class="label">Uang sejumlah</td><td><em>{{ ucfirst(terbilang($inv->amount)) }} rupiah</em></td></tr>
    <tr><td class="label">Untuk pembayaran</td><td>Kontribusi {{ $people->count() }} (<em>{{ terbilang($people->count()) }}</em>) orang peserta kegiatan <strong>{{ $t->title }}</strong>, {{ $t->date_range }} di {{ $t->location }}, atas nama: {{ $people->pluck('name')->join(', ', ' dan ') }}.</td></tr>
    <tr><td class="label">Referensi</td><td>Invoice {{ $inv->number }}</td></tr>
</table>

<table style="margin-top: 24px;">
    <tr>
        <td style="vertical-align: bottom; width: 55%;"><span class="amount">{{ rupiah($inv->amount) }}</span></td>
        <td style="vertical-align: top;">
            <p style="margin: 0;">{{ setting('address') ? \Illuminate\Support\Str::of(setting('address'))->afterLast(',')->trim() : '' }}{{ setting('address') ? ', ' : '' }}{{ $inv->paid_at?->translatedFormat('j F Y') }}</p>
            <p style="margin: 0;">{{ setting('invitation_signatory') }},</p>
            <div style="height: 56px;"></div>
            <p style="margin: 0;"><strong><u>{{ setting('invitation_signatory_name') }}</u></strong></p>
        </td>
    </tr>
</table>
@endsection
