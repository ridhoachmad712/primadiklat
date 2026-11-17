@props(['topic'])
<article class="group card flex flex-col overflow-hidden transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-slate-200/60">
    <a href="{{ route('topics.show', $topic) }}" class="block aspect-[16/9] overflow-hidden bg-slate-100">
        @if($topic->thumbnail_url)
            <img src="{{ $topic->thumbnail_url }}" alt="" class="size-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
        @else
            <x-placeholder-cover :seed="$topic->id" :label="$topic->type_label"/>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-5">
        @if($topic->category)<p class="text-xs font-semibold text-brand-700">{{ $topic->category->name }}</p>@endif
        <h3 class="mt-1.5 font-bold leading-snug text-slate-900"><a href="{{ route('topics.show', $topic) }}" class="hover:text-brand-700">{{ $topic->title }}</a></h3>
        @if($topic->excerpt)<p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ $topic->excerpt }}</p>@endif
        <div class="mt-auto flex items-center gap-2 pt-5">
            <a href="{{ route('register.create', ['tema' => $topic->slug]) }}" class="btn btn-primary flex-1 !py-2">Daftar</a>
            <a href="{{ route('topics.show', $topic) }}" class="btn btn-outline !py-2">Detail</a>
        </div>
    </div>
</article>
