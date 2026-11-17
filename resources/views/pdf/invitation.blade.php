<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Undangan {{ $training->title }}</title>
<style>
    @page { margin: 2cm 2.2cm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10.5pt; color: #1e293b; line-height: 1.5; }
    .kop { border-bottom: 3px double #1d4ed8; padding-bottom: 10px; margin-bottom: 18px; }
    .kop td { vertical-align: middle; }
    .logo { width: 52px; height: 52px; background: #1d4ed8; color: #fff; border-radius: 10px; text-align: center; font-weight: bold; font-size: 20pt; line-height: 52px; }
    .org { font-size: 17pt; font-weight: bold; color: #0f172a; letter-spacing: .5px; }
    .org span { color: #1d4ed8; }
    .tag { font-size: 8.5pt; color: #64748b; }
    table { border-collapse: collapse; width: 100%; }
    .meta td { padding: 1px 0; vertical-align: top; }
    .detail { margin: 12px 0 12px 20px; width: auto; }
    .detail td { padding: 3px 8px 3px 0; vertical-align: top; }
    .prices { margin: 8px 0 14px; }
    .prices th, .prices td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
    .prices th { background: #eff6ff; font-size: 9.5pt; }
    .right { text-align: right; }
    .sign { margin-top: 28px; width: 45%; margin-left: 55%; }
    .small { font-size: 8.5pt; color: #64748b; }
    h3 { font-size: 10.5pt; margin: 14px 0 4px; }
    ol, ul { margin: 4px 0; padding-left: 18px; }
</style>
</head>
<body>
@php $t = $training; @endphp
<table class="kop">
    <tr>
        <td style="width: 64px;"><div class="logo">P</div></td>
        <td>
            <div class="org">PRIMA <span>DIKLAT</span></div>
            <div class="tag">{{ setting('site_tagline') }}</div>
            <div class="tag">{{ setting('address') }} · Telp. {{ setting('phone') }} · {{ setting('email') }}</div>
        </td>
    </tr>
</table>

<table class="meta">
    <tr><td style="width: 80px;">Nomor</td><td style="width: 10px;">:</td><td>{{ $number }}</td><td class="right">{{ now()->translatedFormat('j F Y') }}</td></tr>
    <tr><td>Lampiran</td><td>:</td><td>1 (satu) berkas</td><td></td></tr>
    <tr><td>Perihal</td><td>:</td><td><strong>Undangan {{ $t->type_label }}</strong></td><td></td></tr>
</table>

<p style="margin-top: 16px;">Kepada Yth.<br><strong>Bapak/Ibu Pimpinan Instansi</strong><br>di –<br>&nbsp;&nbsp;&nbsp;&nbsp;Tempat</p>

<p>Dengan hormat,</p>
<p style="text-align: justify;">Dalam rangka meningkatkan kompetensi dan profesionalisme sumber daya manusia aparatur, {{ setting('site_name') }} akan menyelenggarakan kegiatan <strong>{{ $t->title }}</strong>, yang akan dilaksanakan pada:</p>

<table class="detail">
    <tr><td style="width: 110px;">Hari/Tanggal</td><td>:</td><td>{{ $t->start_date->translatedFormat('l') }}{{ $t->duration_days > 1 ? ' – '.$t->end_date->translatedFormat('l') : '' }}, {{ $t->date_range }}</td></tr>
    @if($t->method === 'online')
        <tr><td>Tempat</td><td>:</td><td>Daring melalui Zoom Meeting</td></tr>
    @else
        <tr><td>Tempat</td><td>:</td><td>{{ $t->venue ?? '-' }}</td></tr>
        @if($t->venue_address)<tr><td>Alamat</td><td>:</td><td>{{ $t->venue_address }}</td></tr>@endif
    @endif
    <tr><td>Peserta</td><td>:</td><td>{{ $t->target_participants ?: 'Pejabat dan staf terkait' }}</td></tr>
</table>

@if($t->prices)
<h3>Kontribusi Peserta</h3>
<table class="prices">
    <tr><th>Paket</th><th>Keterangan</th><th class="right">Biaya / Peserta</th></tr>
    @foreach($t->prices as $p)
        <tr><td>{{ $p['name'] }}</td><td>{{ $p['note'] ?? '-' }}</td><td class="right">{{ rupiah($p['price']) }}</td></tr>
    @endforeach
</table>
@endif

@if(! $t->prices)
<h3>Kontribusi Peserta</h3>
<p style="margin: 0 0 10px;">{{ setting('price_notice') }} WhatsApp: {{ setting('whatsapp') }}.</p>
@endif

@if($t->facilities)
<p style="margin: 0;"><strong>Fasilitas:</strong> {{ implode(', ', $t->facilities) }}.</p>
@endif

@if(setting('bank_info'))
<h3>Pembayaran</h3>
<p style="margin: 0; white-space: pre-line;">{{ setting('bank_info') }}</p>
@endif

<h3>Pendaftaran</h3>
<p style="margin: 0;">Pendaftaran dapat dilakukan secara online melalui <strong>{{ route('trainings.show', $t) }}</strong> atau menghubungi WhatsApp {{ setting('whatsapp') }}@if($t->registration_deadline), paling lambat tanggal {{ $t->registration_deadline->translatedFormat('j F Y') }}@endif.</p>

<p style="text-align: justify; margin-top: 14px;">Mengingat pentingnya kegiatan ini, kami mengharapkan Bapak/Ibu dapat menugaskan pejabat/staf terkait untuk mengikuti kegiatan tersebut. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.</p>

<div class="sign">
    <p style="margin: 0;">{{ setting('invitation_signatory') }},</p>
    <div style="height: 64px;"></div>
    <p style="margin: 0;"><strong><u>{{ setting('invitation_signatory_name') }}</u></strong></p>
</div>

<p class="small" style="margin-top: 30px;">Dokumen ini dibuat secara elektronik oleh sistem {{ setting('site_name') }} dan sah tanpa tanda tangan basah.</p>
</body>
</html>
