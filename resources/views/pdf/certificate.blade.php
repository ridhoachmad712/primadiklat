<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Sertifikat {{ $r->name }}</title>
<style>
    @page { margin: 0; }
    body { margin: 0; font-family: DejaVu Sans, sans-serif; color: #1e293b; }
    .page { position: relative; width: 297mm; height: 209mm; overflow: hidden; }
    .bg { position: absolute; top: 0; left: 0; width: 297mm; height: 210mm; }
    .frame { position: absolute; top: 9mm; left: 9mm; right: 9mm; bottom: 9mm; border: 2.5mm solid #1d4ed8; }
    .frame-inner { position: absolute; top: 13mm; left: 13mm; right: 13mm; bottom: 13mm; border: 0.4mm solid #93c5fd; }
    .corner { position: absolute; width: 26mm; height: 26mm; background: #f59e0b; }
    .content { position: absolute; top: 22mm; left: 24mm; right: 24mm; text-align: center; }
    .brand { font-size: 13pt; font-weight: bold; letter-spacing: 3px; color: #0f172a; }
    .brand span { color: #1d4ed8; }
    .title { font-size: 40pt; font-weight: bold; letter-spacing: 10px; color: #1d4ed8; margin: 8mm 0 0; }
    .number { font-size: 10pt; color: #64748b; margin-top: 1mm; }
    .given { font-size: 11pt; margin-top: 8mm; color: #475569; }
    .name { font-size: 26pt; font-weight: bold; color: #0f172a; margin-top: 3mm; }
    .rule { width: 130mm; margin: 2mm auto 0; border-top: 0.5mm solid #cbd5e1; }
    .meta { font-size: 10pt; color: #475569; margin-top: 2mm; }
    .body { font-size: 11pt; margin-top: 6mm; color: #334155; line-height: 1.5; }
    .training { font-size: 14pt; font-weight: bold; color: #0f172a; margin: 2mm 18mm 0; line-height: 1.3; }
    .footer { position: absolute; left: 30mm; right: 30mm; bottom: 22mm; }
    .footer td { vertical-align: bottom; }
    .qr img { width: 26mm; height: 26mm; }
    .qr-note { font-size: 7pt; color: #64748b; width: 60mm; margin-top: 1mm; }
    .sign { text-align: center; font-size: 10pt; }
</style>
</head>
<body>
<div class="page">
    @if($background)
        <img class="bg" src="{{ $background }}" alt="">
    @else
        <div class="corner" style="top: 0; left: 0;"></div>
        <div class="corner" style="bottom: 0; right: 0;"></div>
        <div class="frame"></div>
        <div class="frame-inner"></div>
    @endif

    <div class="content">
        <div class="brand">PRIMA <span>DIKLAT</span></div>
        <div class="title">SERTIFIKAT</div>
        <div class="number">Nomor: {{ $r->certificate_number }}</div>

        <div class="given">Diberikan kepada</div>
        <div class="name">{{ $r->name }}</div>
        <div class="rule"></div>
        <div class="meta">
            @if($r->nip)NIP/NIK. {{ $r->nip }} · @endif{{ $r->position ? $r->position.', ' : '' }}{{ $r->institution }}
        </div>

        <div class="body">{{ setting('certificate_text', 'Atas partisipasinya sebagai PESERTA dalam kegiatan') }}</div>
        <div class="training">{{ \Illuminate\Support\Str::startsWith(mb_strtolower($t->title), mb_strtolower($t->type_label)) ? '' : $t->type_label.' ' }}{{ $t->title }}</div>
        <div class="meta">yang diselenggarakan pada {{ $t->date_range }} di {{ $t->method === 'online' ? 'secara daring' : $t->location }}</div>
    </div>

    <div class="qr" style="position: absolute; left: 30mm; top: 146mm;">
        <img src="{{ $qr }}" alt="QR">
        <div class="qr-note">Pindai untuk memeriksa keabsahan sertifikat di {{ parse_url($verifyUrl, PHP_URL_HOST) }}</div>
    </div>
    <div class="sign" style="position: absolute; right: 30mm; top: 146mm; width: 90mm;">
        <div>{{ ($t->end_date ?? now())->translatedFormat('j F Y') }}</div>
        <div>{{ setting('certificate_signatory', setting('invitation_signatory')) }}</div>
        <div style="height: 16mm;"></div>
        <div><strong><u>{{ setting('certificate_signatory_name', setting('invitation_signatory_name')) }}</u></strong></div>
    </div>
</div>
</body>
</html>
