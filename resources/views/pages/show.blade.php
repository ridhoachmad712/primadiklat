@extends('layouts.app')

@section('title', $page->title)
@section('description', $page->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $page->content), 160))

@section('content')
<x-page-header :title="$page->title" :crumbs="[$page->title => null]"/>
<section class="container-x max-w-3xl py-12">
    <div class="prose prose-lg prose-slate max-w-none prose-headings:tracking-tight prose-a:text-brand-700">
        {!! $page->content !!}
    </div>
</section>
@endsection
