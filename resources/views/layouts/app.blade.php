<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $siteName = setting('site_name', 'Prima Diklat');
        $pageTitle = trim($__env->yieldContent('title'));
        $fullTitle = $pageTitle ? $pageTitle.' | '.$siteName : $siteName.' – '.setting('site_tagline');
        $metaDesc = trim($__env->yieldContent('description')) ?: setting('site_description');
        // Page image, else the share image from Settings, else one generated from the site name.
        $ogImage = trim($__env->yieldContent('og_image')) ?: (setting('og_image') ? storage_url(setting('og_image')) : route('og.default'));
        $ogImageAlt = trim($__env->yieldContent('og_image_alt')) ?: ($pageTitle ?: $siteName);
    @endphp
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($metaDesc), 160) }}">
    @hasSection('noindex')<meta name="robots" content="noindex, follow">@endif
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle ?: $siteName }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($metaDesc), 200) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="{{ $ogImageAlt }}">
    <meta property="og:locale" content="id_ID">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="{{ \App\Support\BrandPalette::shades(setting('brand_color'))[600] }}">
    <link rel="icon" href="{{ setting('favicon') ? storage_url(setting('favicon')) : asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if(setting('brand_color', 'blue') !== 'blue')<style>{!! \App\Support\BrandPalette::css(setting('brand_color')) !!}</style>@endif
    @stack('head')
</head>
<body class="flex min-h-screen flex-col">
    <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2">Lewati ke konten</a>

    @include('partials.header')

    <main id="konten" class="flex-1">
        @yield('content')
    </main>

    @include('partials.footer')

    @php $mobileBar = ! request()->routeIs('register.*', 'registrations.*'); @endphp

    {{-- Mobile bottom action bar --}}
    @if($mobileBar)
        <div class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 px-4 pt-2.5 pb-[calc(0.625rem+env(safe-area-inset-bottom))] backdrop-blur-md lg:hidden">
            <div class="flex gap-2">
                <a href="{{ wa_link() }}" target="_blank" rel="noopener" class="btn btn-wa shrink-0 !px-3.5" aria-label="Chat WhatsApp">
                    <x-icon-whatsapp class="size-5"/>
                    <span>Tanya</span>
                </a>
                <a href="{{ route('schedule.index') }}" class="btn btn-outline min-w-0 flex-1 !px-3">Jadwal</a>
                <a href="@hasSection('mobile_cta')@yield('mobile_cta')@else{{ route('register.create') }}@endif" class="btn btn-primary flex-[1.6] !px-3 whitespace-nowrap">Daftar Sekarang</a>
            </div>
        </div>
    @endif

    {{-- Floating WhatsApp --}}
    <a href="{{ wa_link() }}" target="_blank" rel="noopener"
       @class(['group fixed right-4 bottom-4 z-40 items-center gap-2 rounded-full bg-emerald-700 py-3 pr-4 pl-3 text-sm font-semibold text-white shadow-lg shadow-emerald-700/30 transition hover:bg-emerald-800 sm:right-6 sm:bottom-6', 'hidden lg:flex' => $mobileBar, 'flex' => ! $mobileBar])
       aria-label="Chat WhatsApp">
        <x-icon-whatsapp/>
        <span class="hidden sm:inline">Tanya Admin</span>
    </a>

    @stack('scripts')
</body>
</html>
