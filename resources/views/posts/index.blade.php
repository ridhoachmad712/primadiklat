@extends('layouts.app')

@section('title', 'Berita & Artikel')
@section('description', 'Berita kegiatan, artikel, info regulasi, dan pengumuman terbaru seputar bimtek dan diklat aparatur.')

@section('content')
<x-page-header title="Berita & Artikel" eyebrow="Publikasi" subtitle="Kabar kegiatan, info regulasi, dan artikel seputar pengembangan kompetensi aparatur." :crumbs="['Berita' => null]">
    <div class="mt-6 flex flex-wrap gap-2">
        <a href="{{ route('posts.index') }}" @class(['rounded-full px-3.5 py-1.5 text-sm font-medium', 'bg-slate-900 text-white' => ! $category, 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' => $category])>Semua</a>
        @foreach(\App\Models\Post::CATEGORIES as $key => $label)
            <a href="{{ route('posts.index', ['kategori' => $key]) }}" @class(['rounded-full px-3.5 py-1.5 text-sm font-medium', 'bg-slate-900 text-white' => $category === $key, 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' => $category !== $key])>{{ $label }}</a>
        @endforeach
    </div>
</x-page-header>

<section class="container-x py-12">
    @if($posts->isEmpty())
        <p class="card p-10 text-center text-slate-500">Belum ada artikel.</p>
    @else
        <div class="grid gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($posts as $post)<x-post-card :post="$post"/>@endforeach
        </div>
        {{ $posts->links() }}
    @endif
</section>
@endsection
