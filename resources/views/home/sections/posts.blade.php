<div class="container-x">
    <div class="flex items-end justify-between gap-4">
        @include('home.sections._heading')
        <a href="{{ route('posts.index') }}" class="tap shrink-0 text-sm font-semibold text-brand-700 hover:underline">Lihat semua</a>
    </div>
    <div class="mt-6 grid gap-x-8 divide-y divide-slate-100 sm:mt-8 md:grid-cols-3 md:gap-y-8 md:divide-y-0">
        @foreach($posts as $post)
            <article class="group relative py-4 md:py-0">
                <p class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="font-semibold text-brand-700">{{ $post->category_label }}</span>
                    <span class="text-slate-300" aria-hidden="true">•</span>
                    <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->translatedFormat('j F Y') }}</time>
                </p>
                <h3 class="mt-1.5 line-clamp-2 leading-snug font-bold text-slate-900 group-hover:text-brand-700">
                    <a href="{{ route('posts.show', $post) }}" class="after:absolute after:inset-0">{{ $post->title }}</a>
                </h3>
                <p class="mt-1 line-clamp-2 hidden text-sm text-slate-600 md:block">{{ $post->summary }}</p>
            </article>
        @endforeach
    </div>
</div>
