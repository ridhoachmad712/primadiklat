@php($invoice = $registration->invoice)
@php($people = $invoice?->registrations ?? collect([$registration]))
<x-mail::message>
# Pendaftar Baru

<x-mail::table>
| | |
|:--|:--|
| Kegiatan | {{ $registration->training->title }} |
| Instansi | {{ $registration->institution }} |
| Kontak | {{ $invoice?->contact_name ?? $registration->name }} |
| Email | {{ $registration->email }} |
| HP/WA | {{ $registration->phone }} |
| Paket | {{ $registration->package ?? '-' }} |
| Tagihan | {{ $invoice?->number ?? '-' }} · {{ rupiah($invoice?->amount ?? $registration->price) }} |
</x-mail::table>

**Peserta ({{ $people->count() }}):**

@foreach($people as $p)
- {{ $p->name }}@if($p->position) — {{ $p->position }}@endif ({{ $p->code }})
@endforeach

<x-mail::button :url="url('/admin/registrations?tableSearch='.urlencode($registration->institution))">
Buka di Admin
</x-mail::button>
</x-mail::message>
