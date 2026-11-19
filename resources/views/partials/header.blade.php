@php
    $nav = [
        ['Beranda', route('home'), request()->routeIs('home')],
        ['Jadwal', route('schedule.index'), request()->routeIs('schedule.*', 'trainings.calendar')],
        ['Tema Bimtek', route('topics.index'), request()->routeIs('topics.*')],
        ['Daftar', route('register.create'), request()->routeIs('register.*', 'registrations.*')],
        ['In-House', route('inhouse.create'), request()->routeIs('inhouse.*')],
        ['Berita', route('posts.index'), request()->routeIs('posts.*')],
        ['Kontak', route('contact'), request()->routeIs('contact')],
    ];
@endphp
<div class="hidden border-b border-slate-100 bg-slate-50 text-xs text-slate-500 md:block">
    <div class="container-x flex h-9 items-center justify-between">
        <div class="flex items-center gap-5">
            <span class="inline-flex items-center gap-1.5"><x-heroicon-o-phone class="size-3.5"/> {{ setting('phone') }}</span>
            <span class="inline-flex items-center gap-1.5"><x-heroicon-o-envelope class="size-3.5"/> {{ setting('email') }}</span>
            <span class="inline-flex items-center gap-1.5"><x-heroicon-o-clock class="size-3.5"/> {{ setting('office_hours') }}</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('certificates.index') }}" class="hover:text-brand-700">Cek Sertifikat</a>
            <a href="{{ route('faq') }}" class="hover:text-brand-700">FAQ</a>
            <a href="{{ route('about') }}" class="hover:text-brand-700">Tentang Kami</a>
        </div>
    </div>
</div>

<header x-data="{ open: false, search: false }" class="sticky top-0 z-30 border-b border-slate-200/70 bg-white/85 backdrop-blur-md">
    <div class="container-x flex h-16 items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="tap flex shrink-0 items-center gap-2.5" aria-label="{{ setting('site_name') }}">
            @if(setting('logo'))
                <img src="{{ storage_url(setting('logo')) }}" alt="{{ setting('site_name') }}" class="h-9 w-auto">
            @else
                <span class="grid size-9 place-items-center rounded-xl bg-brand-600 text-white">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/></svg>
                </span>
                <span class="leading-tight">
                    <span class="block text-[15px] font-extrabold tracking-tight text-slate-900">Prima<span class="text-brand-600">Diklat</span></span>
                    <span class="block text-[10px] font-medium tracking-wide text-slate-500 uppercase">Bimtek & Diklat</span>
                </span>
            @endif
        </a>

        <nav class="hidden items-center lg:flex" aria-label="Utama">
            @foreach($nav as [$label, $url, $active])
                <a href="{{ $url }}" @class(['nav-link', 'active' => $active]) @if($active) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-1.5">
            <button type="button" @click="search = !search; $nextTick(() => $refs.q?.focus())" class="btn-ghost btn !p-3" aria-label="Cari">
                <x-heroicon-o-magnifying-glass class="size-5"/>
            </button>
            <a href="{{ route('register.create') }}" class="btn btn-primary hidden sm:inline-flex">Daftar Sekarang</a>
            <button type="button" @click="open = !open" class="btn-ghost btn !p-2.5 lg:hidden" :aria-expanded="open" aria-label="Menu">
                <x-heroicon-o-bars-3 class="size-6" x-show="!open"/>
                <x-heroicon-o-x-mark class="size-6" x-show="open" x-cloak/>
            </button>
        </div>
    </div>

    <div x-show="search" x-collapse x-cloak class="border-t border-slate-100">
        <form action="{{ route('search') }}" class="container-x flex gap-2 py-3">
            <input x-ref="q" type="search" name="q" value="{{ request('q') }}" placeholder="Cari tema bimtek, kota, atau artikel…" class="input">
            <button class="btn btn-primary">Cari</button>
        </form>
    </div>

    <div x-show="open" x-collapse x-cloak class="border-t border-slate-100 lg:hidden">
        <nav class="container-x grid gap-1 py-3">
            @foreach($nav as [$label, $url, $active])
                <a href="{{ $url }}" @class(['rounded-lg px-3 py-3 text-sm font-medium', 'bg-brand-50 text-brand-700' => $active, 'text-slate-700 hover:bg-slate-50' => ! $active])>{{ $label }}</a>
            @endforeach
            <div class="my-2 border-t border-slate-100"></div>
            <a href="{{ route('certificates.index') }}" class="rounded-lg px-3 py-3 text-sm text-slate-600 hover:bg-slate-50">Cek Sertifikat</a>
            <a href="{{ route('faq') }}" class="rounded-lg px-3 py-3 text-sm text-slate-600 hover:bg-slate-50">FAQ</a>
            <a href="{{ route('about') }}" class="rounded-lg px-3 py-3 text-sm text-slate-600 hover:bg-slate-50">Tentang Kami</a>
        </nav>
    </div>
</header>
