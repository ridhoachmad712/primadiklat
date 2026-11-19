@props(['training'])
@php
    $t = $training;
    $label = $t->availability_label;
    $badge = match ($label) {
        'Pendaftaran Dibuka' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/15',
        'Berlangsung' => 'bg-sky-50 text-sky-700 ring-sky-600/15',
        'Kuota Penuh', 'Dibatalkan' => 'bg-rose-50 text-rose-700 ring-rose-600/15',
        default => 'bg-slate-100 text-slate-600 ring-slate-500/10',
    };
@endphp
<article class="group card relative flex flex-col overflow-hidden transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-xl hover:shadow-slate-200/60">
    <div class="relative aspect-[16/9] overflow-hidden bg-slate-100">
        @if($t->thumbnail_url)
            <img src="{{ $t->thumbnail_url }}" alt="" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <x-placeholder-cover :seed="$t->id" :label="$t->type_label"/>
        @endif
        <div class="absolute top-3 left-3 flex min-w-14 flex-col items-center rounded-xl bg-white/95 px-2.5 py-1.5 text-center shadow-sm backdrop-blur">
            <span class="text-lg leading-none font-extrabold text-slate-900">{{ $t->start_date->format('d') }}</span>
            <span class="mt-0.5 text-[10px] font-semibold tracking-wide text-brand-600 uppercase">{{ $t->start_date->translatedFormat('M Y') }}</span>
        </div>
        <span class="absolute top-3 right-3 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset {{ $badge }} bg-opacity-95">{{ $label }}</span>
    </div>
    <div class="flex flex-1 flex-col p-4 sm:p-5">
        <div class="flex flex-wrap items-center gap-1.5 text-xs">
            <span class="font-semibold text-brand-700">{{ $t->type_label }}</span>
            @if($t->category)<span class="text-slate-300">•</span><span class="text-slate-500">{{ $t->category->name }}</span>@endif
        </div>
        <h3 class="mt-2 line-clamp-2 text-base leading-snug font-bold text-slate-900">
            <a href="{{ route('trainings.show', $t) }}" class="after:absolute after:inset-0">{{ $t->title }}</a>
        </h3>
        <dl class="mt-4 space-y-2 text-sm text-slate-600">
            <div class="flex items-center gap-2"><x-heroicon-o-calendar-days class="size-4 shrink-0 text-slate-400"/><dd>{{ $t->date_range }}</dd></div>
            <div class="flex items-center gap-2"><x-heroicon-o-map-pin class="size-4 shrink-0 text-slate-400"/><dd class="truncate">{{ $t->method === 'online' ? 'Online' : ($t->city ?? '-') }}{{ $t->venue && $t->method !== 'online' ? ' · '.$t->venue : '' }}</dd></div>
        </dl>
        <div class="flex-1 min-h-5"></div>
        <div class="flex items-end justify-between border-t border-slate-100 pt-4">
            <div>
                @if($t->lowest_price)
                    <span class="block text-[11px] text-slate-500">Mulai dari</span>
                    <span class="text-base font-bold text-slate-900">{{ rupiah($t->lowest_price) }}</span>
                @else
                    <span class="text-sm text-slate-500">Hubungi kami</span>
                @endif
            </div>
            <span class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700">Detail</span>
        </div>
    </div>
</article>
