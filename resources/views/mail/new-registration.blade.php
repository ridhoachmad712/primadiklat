<x-mail::message>
# Pendaftar Baru

<x-mail::table>
| | |
|:--|:--|
| Kegiatan | {{ $registration->training->title }} |
| Nama | {{ $registration->name }} |
| Jabatan | {{ $registration->position ?? '-' }} |
| Instansi | {{ $registration->institution }} |
| Email | {{ $registration->email }} |
| HP/WA | {{ $registration->phone }} |
| Paket | {{ $registration->package ?? '-' }} |
| Kode | {{ $registration->code }} |
</x-mail::table>

<x-mail::button :url="url('/admin/registrations/'.$registration->id.'/edit')">
Buka di Admin
</x-mail::button>
</x-mail::message>
