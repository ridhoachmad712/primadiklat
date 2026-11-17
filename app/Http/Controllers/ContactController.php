<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\InhouseRequest;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function inhouse()
    {
        return view('contact.inhouse');
    }

    public function storeInhouse(Request $request)
    {
        if (filled($request->input('website'))) {
            return back();
        }

        $data = $request->validate([
            'institution' => ['required', 'string', 'max:200'],
            'contact_name' => ['required', 'string', 'max:150'],
            'position' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\-\s]{9,20}$/'],
            'topic' => ['required', 'string', 'max:255'],
            'participants' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'preferred_date' => ['nullable', 'date', 'after:today'],
            'location' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
        ], [], ['institution' => 'instansi', 'contact_name' => 'nama', 'phone' => 'nomor HP', 'topic' => 'topik']);

        InhouseRequest::create($data);

        return back()->with('success', 'Terima kasih! Permintaan in-house training Anda sudah kami terima. Tim kami akan menghubungi Anda dalam 1×24 jam kerja.');
    }

    public function contact()
    {
        return view('contact.index');
    }

    public function storeContact(Request $request)
    {
        if (filled($request->input('website'))) {
            return back();
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:3000'],
        ], [], ['name' => 'nama', 'subject' => 'subjek', 'message' => 'pesan']);

        ContactMessage::create($data);

        return back()->with('success', 'Pesan Anda terkirim. Kami akan membalas secepatnya.');
    }
}
