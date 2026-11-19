@if ($paginator->hasPages())
    <nav class="mt-12 flex items-center justify-center gap-1" aria-label="Halaman">
        @if ($paginator->onFirstPage())
            <span class="grid size-10 place-items-center rounded-xl text-slate-300"><x-heroicon-o-chevron-left class="size-4"/></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="grid size-10 place-items-center rounded-xl text-slate-600 hover:bg-slate-100" aria-label="Sebelumnya"><x-heroicon-o-chevron-left class="size-4"/></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="grid size-10 place-items-center text-sm text-slate-500">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="grid size-10 place-items-center rounded-xl bg-brand-600 text-sm font-semibold text-white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="grid size-10 place-items-center rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="grid size-10 place-items-center rounded-xl text-slate-600 hover:bg-slate-100" aria-label="Berikutnya"><x-heroicon-o-chevron-right class="size-4"/></a>
        @else
            <span class="grid size-10 place-items-center rounded-xl text-slate-300"><x-heroicon-o-chevron-right class="size-4"/></span>
        @endif
    </nav>
@endif
