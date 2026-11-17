<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->query('kode'));
        $result = null;

        if ($keyword !== '') {
            $result = Registration::with('training')
                ->where(fn ($q) => $q->where('code', $keyword)->orWhere('certificate_number', $keyword))
                ->first();
        }

        return view('certificates.index', compact('keyword', 'result'));
    }

    public function download(Request $request, string $code)
    {
        $r = Registration::with('training')->where('code', $code)->firstOrFail();

        // Admins may preview before payment is settled; the public needs a fully issued certificate.
        $adminPreview = $request->boolean('preview') && auth()->check()
            && $r->status === 'attended' && filled($r->certificate_number);
        abort_unless($r->has_certificate || $adminPreview, 404);

        if ($r->certificate_file && Storage::disk('public')->exists($r->certificate_file)) {
            return redirect(storage_url($r->certificate_file));
        }

        $verifyUrl = route('certificates.index', ['kode' => $r->certificate_number]);
        $qr = (new QRCode(new QROptions([
            'outputType' => 'png',
            'scale' => 6,
            'quietzoneSize' => 1,
            'outputBase64' => true,
        ])))->render($verifyUrl);

        $background = null;
        if (($bg = setting('certificate_background')) && Storage::disk('public')->exists($bg)) {
            $background = 'data:'.Storage::disk('public')->mimeType($bg).';base64,'.base64_encode(Storage::disk('public')->get($bg));
        }

        $pdf = Pdf::loadView('pdf.certificate', [
            'r' => $r,
            't' => $r->training,
            'qr' => $qr,
            'verifyUrl' => $verifyUrl,
            'background' => $background,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Sertifikat-'.str($r->name)->slug().'.pdf');
    }
}
