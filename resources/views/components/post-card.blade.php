@props(['post'])
<article class="group relative flex flex-col">
    <div class="aspect-[16/10] overflow-hidden rounded-2xl bg-slate-100">
        @if($post->thumbnail_url)
            <img src="{{ $post->thumbnail_url }}" alt="" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <x-placeholder-cover :seed="$post->id + 2" :label="$post->category_label"/>
        @endif
    </div>
    <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
        <span class="font-semibold text-brand-700">{{ $post->category_label }}</span>
        <span class="text-slate-300">•</span>
        <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->translatedFormat('j F Y') }}</time>
    </div>
    <h3 class="mt-2 line-clamp-2 text-lg leading-snug font-bold text-slate-900 group-hover:text-brand-700">
        <a href="{{ route('posts.show', $post) }}" class="after:absolute after:inset-0">{{ $post->title }}</a>
    </h3>
    <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ $post->summary }}</p>
</article>
