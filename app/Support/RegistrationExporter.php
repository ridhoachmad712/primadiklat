<?php

namespace App\Support;

use App\Models\Registration;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RegistrationExporter
{
    /** @param Collection<int, Registration> $registrations */
    public static function download(Collection $registrations, string $filename = 'pendaftar'): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'pd').'.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);

        $writer->addRow(Row::fromValues([
            'No', 'Kode', 'Kegiatan', 'Tanggal Kegiatan', 'Nama', 'NIP', 'Jabatan', 'Instansi', 'Alamat Instansi',
            'Kota', 'Email', 'No. HP/WA', 'Paket', 'Biaya', 'Status', 'Pembayaran', 'No. Sertifikat', 'Catatan', 'Tanggal Daftar',
        ], (new Style)->setFontBold()));

        $registrations->loadMissing('training')->values()->each(function (Registration $r, int $i) use ($writer) {
            $writer->addRow(Row::fromValues([
                $i + 1,
                $r->code,
                $r->training?->title,
                $r->training?->date_range,
                $r->name,
                (string) $r->nip,
                $r->position,
                $r->institution,
                $r->institution_address,
                $r->city,
                $r->email,
                (string) $r->phone,
                $r->package,
                (int) $r->price,
                Registration::STATUSES[$r->status] ?? $r->status,
                Registration::PAYMENT_STATUSES[$r->payment_status] ?? $r->payment_status,
                $r->certificate_number,
                $r->notes,
                $r->created_at?->format('d/m/Y H:i'),
            ]));
        });

        $writer->close();

        return response()
            ->download($path, str($filename)->slug().'-'.now()->format('Ymd-His').'.xlsx')
            ->deleteFileAfterSend();
    }
}
