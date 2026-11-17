<?php

namespace App\Http\Controllers;

use App\Mail\NewRegistrationNotification;
use App\Mail\RegistrationReceived;
use App\Models\Registration;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
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

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'nip' => ['nullable', 'string', 'max:30'],
            'position' => ['nullable', 'string', 'max:150'],
            'institution' => ['required', 'string', 'max:200'],
            'institution_address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\-\s]{9,20}$/'],
            'package' => [$packages ? 'required' : 'nullable', Rule::in($packages)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'agree' => ['accepted'],
        ], [
            'agree.accepted' => 'Anda harus menyetujui syarat dan ketentuan.',
            'phone.regex' => 'Nomor HP tidak valid.',
        ], [
            'name' => 'nama', 'institution' => 'instansi', 'phone' => 'nomor HP', 'package' => 'paket',
        ]);

        unset($data['agree']);
        $data['price'] = collect($training->prices ?? [])->firstWhere('name', $data['package'] ?? null)['price'] ?? null;

        $registration = $training->registrations()->create($data);

        try {
            Mail::to($registration->email)->send(new RegistrationReceived($registration));
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
        $registration = Registration::with('training')->where('code', $code)->firstOrFail();

        return view('registrations.show', compact('registration'));
    }
}
