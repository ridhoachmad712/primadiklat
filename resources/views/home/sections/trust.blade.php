<div class="container-x">
    @include('home.sections._heading')
    <div @class(['mt-6 grid gap-8 sm:mt-8 lg:gap-12', 'lg:grid-cols-2' => $legal])>
        @if($legal)
            <div>
                <dl class="card divide-y divide-slate-100">
                    @foreach($legal as $label => $value)
                        <div class="flex gap-3 px-4 py-3 text-sm sm:px-5">
                            <x-heroicon-s-check-badge class="mt-0.5 size-5 shrink-0 text-brand-600"/>
                            @if(is_int($label))
                                <dd class="font-medium text-slate-900">{{ $value }}</dd>
                            @else
                                <div class="min-w-0 sm:flex sm:flex-1 sm:gap-3">
                                    <dt class="text-slate-500 sm:w-48 sm:shrink-0">{{ $label }}</dt>
                                    <dd class="font-semibold break-words text-slate-900">{{ $value }}</dd>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </dl>
                @if(setting('legal_document'))
                    <a href="{{ storage_url(setting('legal_document')) }}" target="_blank" rel="noopener" class="btn btn-outline mt-4"><x-heroicon-o-arrow-down-tray class="size-4"/> Unduh dokumen legalitas</a>
                @endif
            </div>
        @endif

        <div class="flex flex-col justify-center">
            @if($stats)
                <dl @class(['grid gap-2 sm:gap-4', 'grid-cols-3' => count($stats) === 3, 'grid-cols-2' => count($stats) === 2, 'max-w-xs' => count($stats) === 1, 'max-w-2xl' => ! $legal])>
                    @foreach($stats as $label => $value)
                        <div class="rounded-2xl border border-slate-200 bg-white p-3 text-center sm:p-5">
                            <dd class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $value }}</dd>
                            <dt class="mt-1 text-xs text-slate-500 sm:text-sm">{{ $label }}</dt>
                        </div>
                    @endforeach
                </dl>
            @endif
            @if($partners->isNotEmpty())
                <p @class(['text-sm font-medium text-slate-500', 'mt-8' => $stats])>Dipercaya oleh instansi pemerintah dan lembaga di seluruh Indonesia</p>
                <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-4 sm:gap-x-8">
                    @foreach($partners as $p)
                        @if($p->logo_url)
                            <img src="{{ $p->logo_url }}" alt="{{ $p->name }}" class="h-9 w-auto opacity-70 grayscale transition hover:opacity-100 hover:grayscale-0">
                        @else
                            <span class="text-sm font-bold tracking-tight text-slate-500">{{ $p->name }}</span>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
