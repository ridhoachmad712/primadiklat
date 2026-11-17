@props(['seed' => 1, 'label' => null])
@php
    $palettes = [
        ['#1d4ed8', '#60a5fa'], ['#0f766e', '#5eead4'], ['#7c3aed', '#c4b5fd'],
        ['#b45309', '#fcd34d'], ['#be123c', '#fda4af'], ['#0369a1', '#7dd3fc'],
    ];
    [$a, $b] = $palettes[$seed % count($palettes)];
@endphp
<div {{ $attributes->merge(['class' => 'relative size-full overflow-hidden']) }} style="background: linear-gradient(135deg, {{ $a }} 0%, {{ $b }} 100%)">
    <svg class="absolute inset-0 size-full opacity-[0.12]" aria-hidden="true">
        <defs><pattern id="grid-{{ $seed }}" width="28" height="28" patternUnits="userSpaceOnUse"><path d="M28 0H0v28" fill="none" stroke="white" stroke-width="1"/></pattern></defs>
        <rect width="100%" height="100%" fill="url(#grid-{{ $seed }})"/>
    </svg>
    <svg class="absolute -right-6 -bottom-8 size-40 text-white/20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/></svg>
    @if($label)
        <span class="absolute bottom-3 left-3 text-xs font-bold tracking-[0.2em] text-white/80 uppercase">{{ $label }}</span>
    @endif
</div>
