<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>@yield('title')</title>
<style>
    @page { margin: 1.8cm 2cm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1e293b; line-height: 1.45; }
    table { border-collapse: collapse; width: 100%; }
    .kop { border-bottom: 3px double #1d4ed8; padding-bottom: 10px; margin-bottom: 18px; }
    .kop td { vertical-align: middle; }
    .logo { width: 48px; height: 48px; background: #1d4ed8; color: #fff; border-radius: 10px; text-align: center; font-weight: bold; font-size: 19pt; line-height: 48px; }
    .org { font-size: 16pt; font-weight: bold; color: #0f172a; letter-spacing: .5px; }
    .org span { color: #1d4ed8; }
    .tag { font-size: 8pt; color: #64748b; }
    .doc-title { font-size: 18pt; font-weight: bold; color: #0f172a; letter-spacing: 2px; margin: 0; }
    .muted { color: #64748b; }
    .small { font-size: 8pt; color: #64748b; }
    .right { text-align: right; }
    .center { text-align: center; }
    .grid td { padding: 6px 8px; border: 1px solid #cbd5e1; vertical-align: top; }
    .grid th { padding: 6px 8px; border: 1px solid #cbd5e1; background: #eff6ff; text-align: left; font-size: 9pt; }
    .info td { padding: 2px 0; vertical-align: top; }
    .box { border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 12px; }
    .stamp { display: inline-block; border: 2px solid #059669; color: #059669; font-weight: bold; padding: 4px 12px; border-radius: 4px; letter-spacing: 2px; transform: rotate(-8deg); }
    .sign { width: 42%; margin-left: 58%; margin-top: 26px; }
    @yield('style')
</style>
</head>
<body>
<table class="kop">
    <tr>
        <td style="width: 60px;"><div class="logo">P</div></td>
        <td>
            <div class="org">PRIMA <span>DIKLAT</span></div>
            <div class="tag">{{ setting('site_tagline') }}</div>
            <div class="tag">{{ setting('address') }} · Telp. {{ setting('phone') }} · {{ setting('email') }}</div>
        </td>
    </tr>
</table>

@yield('content')

<p class="small" style="margin-top: 26px;">Dokumen ini dibuat secara elektronik oleh sistem {{ setting('site_name') }} dan sah tanpa tanda tangan basah.</p>
</body>
</html>
