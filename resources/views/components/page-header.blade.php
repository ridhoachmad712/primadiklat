@props(['title', 'subtitle' => null, 'eyebrow' => null, 'crumbs' => []])
<section class="relative overflow-hidden border-b border-slate-200/70 bg-gradient-to-b from-brand-50/70 to-white">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(40rem_20rem_at_85%_-10%,color-mix(in_oklab,var(--color-brand-600)_10%,transparent),transparent)]"></div>
    <div class="container-x relative py-8 sm:py-16">
        @if($crumbs)
            <nav class="mb-4 flex flex-wrap items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="tap hover:text-brand-700">Beranda</a>
                @foreach($crumbs as $label => $url)
                    <x-heroicon-m-chevron-right class="size-3.5 text-slate-300"/>
                    @if($url)<a href="{{ $url }}" class="tap hover:text-brand-700">{{ $label }}</a>@else<span class="text-slate-700">{{ $label }}</span>@endif
                @endforeach
            </nav>
        @endif
        @if($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endif
        <h1 class="mt-2 max-w-3xl text-3xl font-extrabold sm:text-4xl">{{ $title }}</h1>
        @if($subtitle)<p class="mt-3 max-w-2xl text-base text-slate-600">{{ $subtitle }}</p>@endif
        {{ $slot }}
    </div>
</section>
