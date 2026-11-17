@php($t = $registration->training)
<x-mail::message>
# Pendaftaran Anda Kami Terima

Yth. Bapak/Ibu **{{ $registration->name }}**,

Terima kasih telah mendaftar kegiatan berikut:

<x-mail::panel>
**{{ $t->title }}**<br>
Tanggal: {{ $t->date_range }}<br>
Tempat: {{ $t->location }}<br>
Paket: {{ $registration->package ?? '-' }} @if($registration->price) ({{ rupiah($registration->price) }}) @endif<br>
Kode pendaftaran: **{{ $registration->code }}**
</x-mail::panel>

Simpan kode pendaftaran di atas. Tim kami akan menghubungi Anda untuk konfirmasi.

@if(setting('bank_info'))
**Informasi pembayaran:**<br>
{!! nl2br(e(setting('bank_info'))) !!}
@endif

<x-mail::button :url="route('trainings.invitation', $t)">
Unduh Surat Undangan
</x-mail::button>

Pertanyaan? Hubungi kami di {{ setting('phone') }} atau WhatsApp {{ setting('whatsapp') }}.

Salam,<br>
{{ setting('site_name') }}
</x-mail::message>
