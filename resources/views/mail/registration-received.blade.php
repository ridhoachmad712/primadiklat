@php($t = $registration->training)
@php($invoice = $registration->invoice)
@php($people = $invoice?->registrations ?? collect([$registration]))
<x-mail::message>
# Pendaftaran Anda Kami Terima

Yth. Bapak/Ibu **{{ $invoice?->contact_name ?? $registration->name }}**,

Terima kasih telah mendaftar kegiatan berikut:

<x-mail::panel>
**{{ $t->title }}**<br>
Tanggal: {{ $t->date_range }}<br>
Tempat: {{ $t->location }}<br>
Instansi: {{ $registration->institution }}
</x-mail::panel>

<x-mail::table>
| Peserta | Kode | Biaya |
|:--|:--|--:|
@foreach($people as $p)
| {{ $p->name }} | {{ $p->code }} | {{ $p->price ? rupiah($p->price) : '-' }} |
@endforeach
| **Total** | | **{{ rupiah($invoice?->amount ?? $registration->price) }}** |
</x-mail::table>

@if(setting('bank_info') && ($invoice?->amount ?? 0) > 0)
**Informasi pembayaran:**<br>
{!! nl2br(e(setting('bank_info'))) !!}

Setelah transfer, unggah bukti pembayaran melalui halaman status pendaftaran berikut. Invoice dan surat undangan juga dapat diunduh di sana.
@endif

<x-mail::button :url="route('registrations.show', $registration->code)">
Lihat Status & Unggah Bukti Bayar
</x-mail::button>

Pertanyaan? Hubungi kami di {{ setting('phone') }} atau WhatsApp {{ setting('whatsapp') }}.

Salam,<br>
{{ setting('site_name') }}
</x-mail::message>
