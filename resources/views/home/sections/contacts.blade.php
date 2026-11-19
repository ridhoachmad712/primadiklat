<div class="container-x grid gap-6 lg:grid-cols-3 lg:items-center lg:gap-12">
    @include('home.sections._heading', ['extra' => filled(setting('office_hours')) ? ' '.setting('office_hours').'.' : ''])
    <div class="grid gap-3 sm:grid-cols-2 lg:col-span-2">
        @forelse($contacts as $c)
            <div class="card flex items-center gap-3 p-4">
                <span class="grid size-11 shrink-0 place-items-center rounded-full bg-brand-50 text-base font-bold text-brand-700">{{ mb_substr($c['name'], 0, 1) }}</span>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-semibold text-slate-900">{{ $c['name'] }}</p>
                    @if(filled($c['role'] ?? null))<p class="truncate text-xs text-slate-500">{{ $c['role'] }}</p>@endif
                </div>
                <a href="{{ wa_link(setting('whatsapp_message'), $c['whatsapp']) }}" target="_blank" rel="noopener" class="btn btn-wa shrink-0 !px-3 !py-2" aria-label="WhatsApp {{ $c['name'] }}">
                    <x-icon-whatsapp class="size-5"/> <span class="hidden sm:inline">Chat</span>
                </a>
            </div>
        @empty
            <div class="card flex items-center gap-3 p-4 sm:col-span-2">
                <span class="grid size-11 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-700"><x-icon-whatsapp class="size-6"/></span>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-slate-900">Admin {{ setting('site_name') }}</p>
                    <p class="text-xs text-slate-500">{{ setting('phone') }} · {{ setting('email') }}</p>
                </div>
                <a href="{{ wa_link() }}" target="_blank" rel="noopener" class="btn btn-wa shrink-0 !px-3 !py-2">Chat</a>
            </div>
        @endforelse
    </div>
</div>
