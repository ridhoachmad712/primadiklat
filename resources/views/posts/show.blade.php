@extends('layouts.app')

@section('title', $post->meta_title ?: $post->title)
@section('description', $post->meta_description ?: $post->summary)
@section('og_type', 'article')
@if($post->thumbnail_url) @section('og_image', $post->thumbnail_url) @section('og_image_alt', $post->image_alt ?: $post->title) @endif
@if($post->noindex || ($preview ?? false)) @section('noindex', '1') @endif

@push('head')
    @php
        $publisher = array_filter([
            '@type' => 'Organization',
            'name' => setting('site_name'),
            'url' => route('home'),
            'logo' => setting('logo') ? ['@type' => 'ImageObject', 'url' => storage_url(setting('logo'))] : null,
        ]);
    @endphp
    @if($post->published_at)<meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">@endif
    <meta property="article:modified_time" content="{{ $post->updated_at->toIso8601String() }}">
    <meta property="article:section" content="{{ $post->category_label }}">
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => $post->category === 'berita' ? 'NewsArticle' : 'Article',
        'headline' => \Illuminate\Support\Str::limit($post->meta_title ?: $post->title, 110, ''),
        'description' => $post->meta_description ?: $post->summary,
        'image' => [$post->thumbnail_url ?: (setting('og_image') ? storage_url(setting('og_image')) : route('og.default'))],
        'datePublished' => $post->published_at?->toIso8601String(),
        'dateModified' => $post->updated_at->toIso8601String(),
        'author' => $post->author ? ['@type' => 'Person', 'name' => $post->author->name] : $publisher,
        'publisher' => $publisher,
        'articleSection' => $post->category_label,
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('posts.show', $post)],
        'inLanguage' => 'id-ID',
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
@if($preview ?? false)
    <div class="border-b border-amber-200 bg-amber-50 text-amber-900">
        <p class="container-x py-3 text-sm"><strong>Pratinjau draf.</strong> Artikel ini belum terbit dan hanya bisa dilihat oleh penulisnya serta Editor.</p>
    </div>
@endif
<article>
    <header class="container-x max-w-3xl pt-12 pb-8">
        <nav class="mb-6 flex flex-wrap items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-brand-700">Beranda</a>
            <x-heroicon-m-chevron-right class="size-3.5 text-slate-300"/>
            <a href="{{ route('posts.index') }}" class="hover:text-brand-700">Berita</a>
        </nav>
        <a href="{{ route('posts.index', ['kategori' => $post->category]) }}" class="eyebrow">{{ $post->category_label }}</a>
        <h1 class="mt-3 text-3xl leading-tight font-extrabold sm:text-4xl">{{ $post->title }}</h1>
        <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-500">
            <span>{{ $post->author?->name ?? setting('site_name') }}</span>
            <span class="text-slate-300">•</span>
            <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->translatedFormat('j F Y') }}</time>
            <span class="text-slate-300">•</span>
            <span>{{ $post->reading_time }} menit baca</span>
        </div>
    </header>

    <div class="container-x max-w-5xl">
        <div class="aspect-[2/1] overflow-hidden rounded-3xl bg-slate-100">
            @if($post->thumbnail_url)<img src="{{ $post->thumbnail_url }}" alt="{{ $post->image_alt ?: $post->title }}" fetchpriority="high" class="size-full object-cover">@else<x-placeholder-cover :seed="$post->id + 2"/>@endif
        </div>
    </div>

    <div class="container-x max-w-3xl py-10">
        <div class="prose prose-lg prose-slate max-w-none prose-headings:tracking-tight prose-a:text-brand-700 prose-img:rounded-2xl">
            {!! $post->content !!}
        </div>

        <div class="mt-12 flex flex-wrap items-center gap-2 border-t border-slate-200 pt-6 text-sm">
            <span class="mr-2 text-slate-500">Bagikan:</span>
            <a href="https://wa.me/?text={{ rawurlencode($post->title.' – '.route('posts.show', $post)) }}" target="_blank" rel="noopener" class="chip hover:bg-slate-200">WhatsApp</a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('posts.show', $post)) }}" target="_blank" rel="noopener" class="chip hover:bg-slate-200">Facebook</a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('posts.show', $post)) }}" target="_blank" rel="noopener" class="chip hover:bg-slate-200">LinkedIn</a>
        </div>

        <div class="mt-10 rounded-2xl bg-brand-600 p-6 text-white sm:flex sm:items-center sm:justify-between sm:gap-6">
            <div>
                <p class="font-bold text-white">Ingin mendalami topik ini?</p>
                <p class="mt-1 text-sm text-brand-100">Lihat jadwal bimtek & diklat terbaru dari Prima Diklat.</p>
            </div>
            <a href="{{ route('schedule.index') }}" class="btn mt-4 shrink-0 bg-white text-brand-700 hover:bg-brand-50 sm:mt-0">Lihat Jadwal</a>
        </div>
    </div>
</article>

@if($latest->isNotEmpty())
<section class="container-x border-t border-slate-200 py-14">
    <h2 class="text-xl font-bold">Artikel lainnya</h2>
    <div class="mt-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($latest as $p)<x-post-card :post="$p"/>@endforeach
    </div>
</section>
@endif
@endsection
