{{-- Section heading from admin config: optional eyebrow, title, optional intro. --}}
<div class="min-w-0">
    @if(filled($s['eyebrow']))<p class="eyebrow mb-2">{{ $s['eyebrow'] }}</p>@endif
    <h2 class="section-title">{{ $s['title'] }}</h2>
    @if(filled($s['intro']))<p class="mt-2 max-w-2xl text-slate-600">{{ $s['intro'] }}{{ $extra ?? '' }}</p>@endif
</div>
