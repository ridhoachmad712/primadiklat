{{--
    SEO score card for the article form. Compact: one line per problem (click for the tip),
    passed checks folded into a single line. Inline styles: Filament's theme does not scan this file.
--}}
@php
    $palette = [
        'good' => ['fg' => '#15803d', 'bg' => '#f0fdf4', 'ring' => '#16a34a', 'label' => 'Baik'],
        'ok' => ['fg' => '#a16207', 'bg' => '#fefce8', 'ring' => '#ca8a04', 'label' => 'Cukup'],
        'bad' => ['fg' => '#b91c1c', 'bg' => '#fef2f2', 'ring' => '#dc2626', 'label' => 'Perlu perbaikan'],
    ];
    $c = $palette[$level];
    $icon = ['good' => '✓', 'ok' => '!', 'bad' => '✕'];
    $all = collect($checks);
    $problems = $all->where('status', '!=', 'good')->sortBy(fn ($x) => $x['status'] === 'bad' ? 0 : 1)->groupBy('group');
    $passed = $all->where('status', 'good');
    $dot = fn ($status) => 'flex: none; width: 16px; height: 16px; border-radius: 9999px; display: inline-grid; place-items: center; font-size: 10px; font-weight: 700; color: #fff; background: '.$palette[$status]['ring'].';';
@endphp
<div x-data="{ open: {}, passed: false }" style="font-family: inherit; border-radius: 12px; background: #fff; border: 1px solid #e5e7eb; overflow: hidden; color: #1f2937; font-size: 13px; line-height: 1.4;">
    <div style="display: flex; align-items: center; gap: 12px; padding: 10px 14px; background: {{ $c['bg'] }};">
        <div role="img" aria-label="Skor SEO {{ $score }} dari 100"
             style="flex: none; width: 48px; height: 48px; border-radius: 9999px; display: grid; place-items: center;
                    background: conic-gradient({{ $c['ring'] }} {{ $score * 3.6 }}deg, #e5e7eb 0);">
            <div style="width: 38px; height: 38px; border-radius: 9999px; background: #fff; display: grid; place-items: center; font-weight: 800; font-size: 15px; color: {{ $c['fg'] }};">{{ $score }}</div>
        </div>
        <div>
            <p style="margin: 0; font-weight: 700; font-size: 14px;">Skor SEO: <span style="color: {{ $c['fg'] }};">{{ $c['label'] }}</span></p>
            <p style="margin: 1px 0 0; font-size: 12px; color: #4b5563;">
                {{ $all->count() - $passed->count() }} perlu diperbaiki · {{ $passed->count() }} sudah baik. Klik baris untuk melihat saran.
            </p>
        </div>
    </div>

    @foreach($problems as $group => $items)
        <div style="padding: 6px 14px 4px; border-top: 1px solid #f3f4f6;">
            <p style="margin: 0 0 2px; font-size: 10.5px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #6b7280;">{{ $group }}</p>
            @foreach($items as $check)
                @php $key = md5($check['group'].$check['hint']); @endphp
                <div style="padding: 1px 0;">
                    <button type="button" x-on:click="open['{{ $key }}'] = ! open['{{ $key }}']" x-bind:aria-expanded="!! open['{{ $key }}']"
                            style="all: unset; box-sizing: border-box; width: 100%; display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 3px 0;">
                        <span aria-label="{{ $palette[$check['status']]['label'] }}" style="{{ $dot($check['status']) }}">{{ $icon[$check['status']] }}</span>
                        <span style="flex: 1; color: #111827;">{{ $check['label'] }}</span>
                        @if($check['hint'])<span aria-hidden="true" style="color: #6b7280; font-size: 11px;" x-text="open['{{ $key }}'] ? 'tutup ▴' : 'saran ▾'">saran ▾</span>@endif
                    </button>
                    @if($check['hint'])
                        <p x-bind:style="{ display: open['{{ $key }}'] ? 'block' : 'none' }" style="margin: 0 0 4px 24px; color: #4b5563; font-size: 12px;">{{ $check['hint'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach

    @if($passed->isNotEmpty())
        <div style="padding: 6px 14px; border-top: 1px solid #f3f4f6;">
            <button type="button" x-on:click="passed = ! passed" x-bind:aria-expanded="passed"
                    style="all: unset; box-sizing: border-box; width: 100%; display: flex; align-items: center; gap: 8px; cursor: pointer; color: #15803d; font-weight: 600;">
                <span style="{{ $dot('good') }}">✓</span>
                <span style="flex: 1;">{{ $passed->count() }} pemeriksaan sudah baik</span>
                <span aria-hidden="true" style="color: #6b7280; font-weight: 400; font-size: 11px;" x-text="passed ? 'tutup ▴' : 'lihat ▾'">lihat ▾</span>
            </button>
            <ul x-bind:style="{ display: passed ? 'block' : 'none' }" style="margin: 4px 0 0; padding: 0 0 0 24px; list-style: none; color: #374151;">
                @foreach($passed as $check)<li style="padding: 1px 0;">{{ $check['label'] }}</li>@endforeach
            </ul>
        </div>
    @endif

    <p style="margin: 0; padding: 6px 14px 8px; font-size: 11px; color: #6b7280; border-top: 1px solid #f3f4f6;">
        Panduan menulis, bukan nilai dari Google.
    </p>
</div>
