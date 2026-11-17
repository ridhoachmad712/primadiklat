@php($t = $invoice->training)
@php($code = $invoice->public_code)
<x-mail::message>
# Tagihan Kegiatan Anda

Yth. Bapak/Ibu **{{ $invoice->contact_name }}**,

Berikut biaya kontribusi untuk pendaftaran {{ $invoice->institution }}:

<x-mail::panel>
**{{ $t->title }}**<br>
{{ $t->date_range }} · {{ $t->location }}<br>
No. tagihan: **{{ $invoice->number }}**
</x-mail::panel>

<x-mail::table>
| Peserta | Biaya |
|:--|--:|
@foreach($invoice->registrations->where('status', '!=', 'cancelled') as $p)
| {{ $p->name }} | {{ rupiah($p->price) }} |
@endforeach
| **Total** | **{{ rupiah($invoice->amount) }}** |
</x-mail::table>

@if(setting('bank_info'))
**Transfer ke:**<br>
{!! nl2br(e(setting('bank_info'))) !!}
@endif

<x-mail::button :url="route('registrations.show', $code)">
Lihat Invoice & Unggah Bukti Transfer
</x-mail::button>

Salam,<br>
{{ setting('site_name') }}
</x-mail::message>
