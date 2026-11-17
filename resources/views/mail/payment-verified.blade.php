@php($t = $invoice->training)
@php($code = $invoice->public_code)
<x-mail::message>
# Pembayaran Anda Telah Diterima

Yth. Bapak/Ibu **{{ $invoice->contact_name }}**,

Pembayaran untuk tagihan **{{ $invoice->number }}** sebesar **{{ rupiah($invoice->amount) }}** telah kami verifikasi.

<x-mail::panel>
**{{ $t->title }}**<br>
Tanggal: {{ $t->date_range }}<br>
Tempat: {{ $t->location }}<br>
Peserta: {{ $invoice->registrations->pluck('name')->join(', ') }}<br>
No. kuitansi: **{{ $invoice->receipt_number }}**
</x-mail::panel>

<x-mail::button :url="route('registrations.receipt', $code)">
Unduh Kuitansi
</x-mail::button>

Status pendaftaran dapat dilihat kapan saja di {{ route('registrations.show', $code) }}.

Salam,<br>
{{ setting('site_name') }}
</x-mail::message>
