@extends('layouts.app')

@section('title', 'Pertanyaan Umum (FAQ)')

@push('head')
<script type="application/ld+json">
{!! json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faqs->map(fn ($f) => ['@type' => 'Question', 'name' => $f->question, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer]])->values()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<x-page-header title="Pertanyaan Umum" eyebrow="FAQ" subtitle="Jawaban atas pertanyaan yang sering diajukan calon peserta." :crumbs="['FAQ' => null]"/>

<section class="container-x max-w-3xl py-12">
    <div class="divide-y divide-slate-200 card" x-data="{ open: 0 }">
        @forelse($faqs as $i => $faq)
            <div>
                <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between gap-4 p-5 text-left" :aria-expanded="open === {{ $i }}">
                    <span class="font-semibold text-slate-900">{{ $faq->question }}</span>
                    <x-heroicon-m-chevron-down class="size-5 shrink-0 text-slate-400 transition" ::class="open === {{ $i }} && 'rotate-180'"/>
                </button>
                <div x-show="open === {{ $i }}" x-collapse @if($i !== 0) x-cloak @endif>
                    <p class="px-5 pb-5 text-slate-600 whitespace-pre-line">{{ $faq->answer }}</p>
                </div>
            </div>
        @empty
            <p class="p-8 text-center text-slate-500">Belum ada FAQ.</p>
        @endforelse
    </div>
    <div class="mt-10 text-center">
        <p class="text-slate-600">Pertanyaan Anda belum terjawab?</p>
        <div class="mt-4 flex justify-center gap-2">
            <a href="{{ wa_link() }}" target="_blank" rel="noopener" class="btn btn-wa">Chat WhatsApp</a>
            <a href="{{ route('contact') }}" class="btn btn-outline">Kirim Pesan</a>
        </div>
    </div>
</section>
@endsection
