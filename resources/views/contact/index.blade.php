@extends('layouts.app')

@section('title', 'Hubungi Kami')

@section('content')
<x-page-header title="Hubungi Kami" eyebrow="Kontak" subtitle="Ada pertanyaan seputar jadwal, biaya, atau kerja sama? Kami siap membantu." :crumbs="['Kontak' => null]"/>

<div class="container-x grid gap-12 py-12 lg:grid-cols-5">
    <div class="space-y-4 lg:col-span-2">
        @foreach([
            ['heroicon-o-chat-bubble-left-right', 'WhatsApp', setting('whatsapp'), wa_link()],
            ['heroicon-o-phone', 'Telepon', setting('phone'), 'tel:'.preg_replace('/\D/', '', (string) setting('phone'))],
            ['heroicon-o-envelope', 'Email', setting('email'), 'mailto:'.setting('email')],
            ['heroicon-o-map-pin', 'Alamat', setting('address'), null],
            ['heroicon-o-clock', 'Jam layanan', setting('office_hours'), null],
        ] as [$icon, $label, $value, $href])
            <div class="card flex gap-4 p-5">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700"><x-dynamic-component :component="$icon" class="size-5"/></span>
                <div>
                    <p class="text-xs text-slate-500">{{ $label }}</p>
                    @if($href)<a href="{{ $href }}" class="font-semibold text-slate-900 hover:text-brand-700" @if(str_starts_with($href, 'http')) target="_blank" rel="noopener" @endif>{{ $value }}</a>@else<p class="font-semibold text-slate-900">{{ $value }}</p>@endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="lg:col-span-3">
        @if(session('success'))
            <div class="mb-6 flex gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-800"><x-heroicon-s-check-circle class="size-6 shrink-0"/><p>{{ session('success') }}</p></div>
        @endif
        <form method="POST" action="{{ route('contact.store') }}" class="card space-y-5 p-4 sm:p-8">
            @csrf
            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="label" for="name">Nama <span class="text-rose-600">*</span></label>
                    <input id="name" name="name" value="{{ old('name') }}" required class="input">
                    @error('name')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="email">Email <span class="text-rose-600">*</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required class="input">
                    @error('email')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="phone">No. HP</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" class="input">
                </div>
                <div>
                    <label class="label" for="subject">Subjek <span class="text-rose-600">*</span></label>
                    <input id="subject" name="subject" value="{{ old('subject') }}" required class="input">
                    @error('subject')<p class="error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="label" for="message">Pesan <span class="text-rose-600">*</span></label>
                <textarea id="message" name="message" rows="5" required class="input">{{ old('message') }}</textarea>
                @error('message')<p class="error">{{ $message }}</p>@enderror
            </div>
            <button class="btn btn-primary">Kirim Pesan</button>
        </form>
        @if(setting('maps_embed'))
            <iframe src="{{ setting('maps_embed') }}" class="mt-6 h-72 w-full rounded-2xl border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Peta lokasi"></iframe>
        @endif
    </div>
</div>
@endsection
