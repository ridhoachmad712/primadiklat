<x-mail::message>
# Bukti Pembayaran Baru

<x-mail::table>
| | |
|:--|:--|
| No. tagihan | {{ $invoice->number }} |
| Kegiatan | {{ $invoice->training->title }} |
| Instansi | {{ $invoice->institution }} |
| Kontak | {{ $invoice->contact_name }} ({{ $invoice->contact_phone }}) |
| Jumlah peserta | {{ $invoice->registrations()->count() }} |
| Total | {{ rupiah($invoice->amount) }} |
</x-mail::table>

<x-mail::button :url="url('/admin/invoices?activeTab=waiting')">
Verifikasi di Admin
</x-mail::button>
</x-mail::message>
