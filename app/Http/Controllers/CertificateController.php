<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;

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
}
