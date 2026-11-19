<div class="container-x">
    <div class="flex items-end justify-between gap-4">
        @include('home.sections._heading')
        <a href="{{ route('topics.index') }}" class="btn btn-outline hidden shrink-0 sm:inline-flex">Semua {{ $topics->count() }} tema</a>
    </div>
    @if($categories->isNotEmpty())
        <div class="chip-row mt-6">
            @foreach($categories as $c)
                <a href="{{ route('topics.index', ['kategori' => $c->slug]) }}" class="chip bg-white ring-1 ring-slate-200 hover:bg-slate-100">{{ $c->name }} <span class="text-slate-500">{{ $c->topics_count }}</span></a>
            @endforeach
        </div>
    @endif
    @if($featuredTopics->isEmpty())
        <div class="card mt-8 p-6 text-center text-slate-500 sm:p-10">Daftar tema segera diumumkan. Anda tetap dapat mendaftar dengan mengusulkan tema.</div>
    @else
        <div class="swipe-row mt-6 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">
            @foreach($featuredTopics as $topic)<x-topic-card :topic="$topic"/>@endforeach
        </div>
        <a href="{{ route('topics.index') }}" class="btn btn-outline mt-4 w-full sm:hidden">Lihat semua {{ $topics->count() }} tema</a>
    @endif
</div>
