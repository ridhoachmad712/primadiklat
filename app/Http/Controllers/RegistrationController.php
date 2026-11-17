<?php

namespace App\Http\Controllers;

use App\Mail\NewRegistrationNotification;
use App\Mail\PaymentProofUploaded;
use App\Mail\RegistrationReceived;
use App\Models\Invoice;
use App\Models\Registration;
use App\Models\Training;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    public const MAX_PARTICIPANTS = 20;

    public function create(Training $training)
    {
        abort_unless($training->status === 'published', 404);

        return view('registrations.create', ['training' => $training->loadCount(['registrations' => fn ($q) => $q->where('status', '!=', 'cancelled')])]);
    }

    public function store(Request $request, Training $training)
    {
        abort_unless($training->status === 'published', 404);

        // Honeypot: bots fill hidden fields.
        if (filled($request->input('website'))) {
            return redirect()->route('trainings.show', $training);
        }

        if (! $training->can_register) {
            return back()->withErrors(['training' => 'Maaf, pendaftaran untuk kegiatan ini sudah ditutup.'])->withInput();
        }

        $packages = collect($training->prices ?? [])->pluck('name')->all();
        $phone = ['regex:/^[0-9+\-\s]{9,20}$/'];

        $data = $request->validate([
            'institution' => ['required', 'string', 'max:200'],
            'institution_address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'participants' => ['required', 'array', 'min:1', 'max:'.self::MAX_PARTICIPANTS],
            'participants.*.name' => ['required', 'string', 'max:150'],
            'participants.*.nip' => ['nullable', 'string', 'max:30'],
            'participants.*.position' => ['nullable', 'string', 'max:150'],
            'participants.*.email' => ['nullable', 'email', 'max:150'],
            'participants.*.phone' => ['nullable', 'string', ...$phone],
            'participants.0.email' => ['required', 'email', 'max:150'],
            'participants.0.phone' => ['required', 'string', ...$phone],
            'package' => [$packages ? 'required' : 'nullable', Rule::in($packages)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'agree' => ['accepted'],
        ], [
            'agree.accepted' => 'Anda harus menyetujui syarat dan ketentuan.',
            'participants.*.phone.regex' => 'Nomor HP tidak valid.',
            'participants.*.name.required' => 'Nama peserta wajib diisi.',
            'participants.0.email.required' => 'Email peserta pertama (kontak) wajib diisi.',
            'participants.0.phone.required' => 'Nomor HP peserta pertama (kontak) wajib diisi.',
            'participants.max' => 'Maksimal '.self::MAX_PARTICIPANTS.' peserta per pendaftaran.',
        ], [
            'institution' => 'instansi', 'package' => 'paket',
            'participants.*.email' => 'email', 'participants.*.phone' => 'nomor HP',
        ]);

        $people = array_values($data['participants']);
        $remaining = $training->remaining_quota;
        if ($remaining !== null && count($people) > $remaining) {
            return back()->withErrors(['participants' => "Sisa kuota hanya {$remaining} kursi."])->withInput();
        }

        $price = collect($training->prices ?? [])->firstWhere('name', $data['package'] ?? null)['price'] ?? null;
        $contact = $people[0];

        $invoice = DB::transaction(function () use ($training, $data, $people, $price, $contact) {
            $invoice = Invoice::create([
                'training_id' => $training->id,
                'institution' => $data['institution'],
                'contact_name' => $contact['name'],
                'contact_email' => $contact['email'],
                'contact_phone' => $contact['phone'],
            ]);

            foreach ($people as $p) {
                $training->registrations()->create([
                    'invoice_id' => $invoice->id,
                    'name' => $p['name'],
                    'nip' => $p['nip'] ?? null,
                    'position' => $p['position'] ?? null,
                    'email' => ($p['email'] ?? null) ?: $contact['email'],
                    'phone' => ($p['phone'] ?? null) ?: $contact['phone'],
                    'institution' => $data['institution'],
                    'institution_address' => $data['institution_address'] ?? null,
                    'city' => $data['city'] ?? null,
                    'package' => $data['package'] ?? null,
                    'price' => $price,
                    'notes' => $data['notes'] ?? null,
                ]);
            }
            $invoice->recalculate();

            return $invoice;
        });

        $registration = $invoice->registrations()->orderBy('id')->first();

        try {
            Mail::to($invoice->contact_email)->send(new RegistrationReceived($registration));
            if ($notify = setting('notify_email', setting('email'))) {
                Mail::to($notify)->send(new NewRegistrationNotification($registration));
            }
        } catch (\Throwable $e) {
            Log::warning('Registration mail failed: '.$e->getMessage());
        }

        return redirect()->route('registrations.show', $registration->code);
    }

    public function show(string $code)
    {
        $registration = $this->findByCode($code);
        $invoice = $registration->invoice->load('registrations');

        return view('registrations.show', compact('registration', 'invoice'));
    }

    public function uploadProof(Request $request, string $code)
    {
        $invoice = $this->findByCode($code)->invoice;
        abort_unless($invoice->can_upload_proof, 403, 'Tagihan ini tidak menerima bukti pembayaran.');

        $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ], [], ['proof' => 'bukti pembayaran']);

        $old = $invoice->payment_proof;
        $invoice->update([
            'payment_proof' => $request->file('proof')->store('payment-proofs', 'public'),
            'proof_uploaded_at' => now(),
            'status' => 'waiting',
            'admin_note' => null,
        ]);
        if ($old) {
            Storage::disk('public')->delete($old);
        }

        try {
            if ($notify = setting('notify_email', setting('email'))) {
                Mail::to($notify)->send(new PaymentProofUploaded($invoice));
            }
        } catch (\Throwable $e) {
            Log::warning('Payment proof mail failed: '.$e->getMessage());
        }

        return redirect()->route('registrations.show', $code)->with('success', 'Bukti pembayaran terkirim. Kami akan memverifikasinya dalam 1×24 jam kerja.');
    }

    public function invoice(string $code)
    {
        $invoice = $this->findByCode($code)->invoice->load('registrations', 'training');

        return Pdf::loadView('pdf.invoice', compact('invoice'))->setPaper('a4')
            ->stream('Invoice-'.$invoice->number.'.pdf');
    }

    public function receipt(string $code)
    {
        $invoice = $this->findByCode($code)->invoice->load('registrations', 'training');
        abort_unless($invoice->status === 'paid', 404);

        return Pdf::loadView('pdf.receipt', compact('invoice'))->setPaper('a4')
            ->stream('Kuitansi-'.$invoice->receipt_number.'.pdf');
    }

    private function findByCode(string $code): Registration
    {
        $registration = Registration::with('training', 'invoice')->where('code', $code)->firstOrFail();
        if (! $registration->invoice) {
            Invoice::createFor(collect([$registration]));
            $registration->load('invoice');
        }

        return $registration;
    }
}
