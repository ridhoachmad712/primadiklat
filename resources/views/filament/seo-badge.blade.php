{{-- Floating SEO score, fixed to the bottom-right corner while editing. Re-rendered by Livewire on every live update. --}}
@php
    $palette = [
        'good' => ['fg' => '#15803d', 'ring' => '#16a34a', 'label' => 'Baik'],
        'ok' => ['fg' => '#a16207', 'ring' => '#ca8a04', 'label' => 'Cukup'],
        'bad' => ['fg' => '#b91c1c', 'ring' => '#dc2626', 'label' => 'Perlu perbaikan'],
    ];
    $c = $palette[$level];
    $todo = collect($checks)->where('status', '!=', 'good')->count();
@endphp
<button type="button"
        x-data
        x-on:click="$dispatch('expand-section', { id: 'seo-panel' }); $nextTick(() => document.getElementById('seo-panel')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
        aria-label="Skor SEO {{ $score }} dari 100, {{ $c['label'] }}. Buka panel SEO"
        style="position: fixed; right: 20px; bottom: 20px; z-index: 40; display: flex; align-items: center; gap: 10px;
               padding: 8px 14px 8px 8px; border-radius: 9999px; border: 1px solid #e5e7eb; background: #fff; color: #111827;
               box-shadow: 0 10px 25px -5px rgba(0,0,0,.25); cursor: pointer; font: inherit; text-align: left;">
    <span style="flex: none; width: 44px; height: 44px; border-radius: 9999px; display: grid; place-items: center;
                 background: conic-gradient({{ $c['ring'] }} {{ $score * 3.6 }}deg, #e5e7eb 0);">
        <span style="width: 34px; height: 34px; border-radius: 9999px; background: #fff; display: grid; place-items: center; font-weight: 800; font-size: 14px; color: {{ $c['fg'] }};">{{ $score }}</span>
    </span>
    <span style="line-height: 1.25;">
        <span style="display: block; font-size: 12px; color: #4b5563;">
            Skor SEO
            <span wire:loading.inline style="color: #6b7280;">· menghitung…</span>
        </span>
        <span style="display: block; font-size: 13px; font-weight: 700; color: {{ $c['fg'] }};">{{ $c['label'] }}</span>
        <span style="display: block; font-size: 11px; color: #6b7280;">{{ $todo ? $todo.' saran perbaikan' : 'Semua terpenuhi' }}</span>
    </span>
</button>
