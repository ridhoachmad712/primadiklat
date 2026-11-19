<div class="container-x">
    @include('home.sections._heading')
    <div class="swipe-row mt-6 sm:mt-10 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3">
        @foreach($testimonials as $t)
            <figure class="card flex flex-col p-4 sm:p-6">
                <div class="flex gap-0.5 text-amber-400" aria-label="Nilai {{ $t->rating }} dari 5">@for($i = 0; $i < $t->rating; $i++)<x-heroicon-s-star class="size-4"/>@endfor</div>
                <blockquote class="mt-4 flex-1 text-[15px] leading-relaxed text-slate-700">“{{ $t->content }}”</blockquote>
                <figcaption class="mt-6 flex items-center gap-3 border-t border-slate-100 pt-5">
                    @if($t->photo_url)
                        <img src="{{ $t->photo_url }}" alt="" class="size-10 rounded-full object-cover">
                    @else
                        <span class="grid size-10 place-items-center rounded-full bg-brand-100 text-sm font-bold text-brand-700">{{ \Illuminate\Support\Str::of($t->name)->replaceMatches('/^(Drs?\.|Ir\.|H\.)\s*/', '')->substr(0, 1) }}</span>
                    @endif
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $t->name }}</p>
                        <p class="text-xs text-slate-500">{{ $t->position }}{{ $t->institution ? ', '.$t->institution : '' }}</p>
                    </div>
                </figcaption>
            </figure>
        @endforeach
    </div>
</div>
