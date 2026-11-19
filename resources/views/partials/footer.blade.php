@php
    $socials = array_filter([
        'Instagram' => setting('instagram'),
        'Facebook' => setting('facebook'),
        'YouTube' => setting('youtube'),
        'LinkedIn' => setting('linkedin'),
    ]);
    $footerCategories = \App\Models\Category::active()->take(6)->get();
@endphp
<footer class="mt-16 border-t sm:mt-24 border-slate-200 bg-slate-950 text-slate-400">
    <div class="container-x grid grid-cols-2 gap-x-6 gap-y-10 py-10 sm:py-14 lg:grid-cols-12">
        <div class="col-span-2 lg:col-span-4">
            <div class="flex items-center gap-2.5">
                <span class="grid size-9 place-items-center rounded-xl bg-brand-600 text-white">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/></svg>
                </span>
                <span class="text-lg font-extrabold tracking-tight text-white">Prima<span class="text-brand-400">Diklat</span></span>
            </div>
            <p class="mt-4 max-w-sm text-sm leading-relaxed">{{ setting('about_short') }}</p>
            @if($socials)
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach($socials as $name => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener" class="rounded-lg border border-white/10 px-3 py-1.5 text-xs text-slate-300 hover:border-white/30 hover:text-white">{{ $name }}</a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="lg:col-span-2">
            <h3 class="text-sm font-semibold text-white">Program</h3>
            <ul class="mt-4 space-y-2.5 text-sm pointer-coarse:space-y-0">
                @foreach($footerCategories as $c)
                    <li><a href="{{ route('topics.index', ['kategori' => $c->slug]) }}" class="tap hover:text-white">{{ $c->name }}</a></li>
                @endforeach
            </ul>
        </div>

        <div class="lg:col-span-2">
            <h3 class="text-sm font-semibold text-white">Informasi</h3>
            <ul class="mt-4 space-y-2.5 text-sm pointer-coarse:space-y-0">
                <li><a href="{{ route('schedule.index') }}" class="tap hover:text-white">Jadwal Bimtek</a></li>
                <li><a href="{{ route('trainings.calendar') }}" class="tap hover:text-white">Kalender Kegiatan</a></li>
                <li><a href="{{ route('galleries.index') }}" class="tap hover:text-white">Galeri</a></li>
                <li><a href="{{ route('inhouse.create') }}" class="tap hover:text-white">In-House Training</a></li>
                <li><a href="{{ route('certificates.index') }}" class="tap hover:text-white">Cek Sertifikat</a></li>
                <li><a href="{{ route('faq') }}" class="tap hover:text-white">FAQ</a></li>
                <li><a href="{{ url('/halaman/syarat-dan-ketentuan') }}" class="tap hover:text-white">Syarat & Ketentuan</a></li>
                <li><a href="{{ url('/halaman/kebijakan-privasi') }}" class="tap hover:text-white">Kebijakan Privasi</a></li>
            </ul>
        </div>

        <div class="col-span-2 lg:col-span-4">
            <h3 class="text-sm font-semibold text-white">Hubungi Kami</h3>
            <ul class="mt-4 space-y-3 text-sm">
                <li class="flex gap-3"><x-heroicon-o-map-pin class="mt-0.5 size-4 shrink-0 text-slate-500"/> <span>{{ setting('address') }}</span></li>
                <li class="flex gap-3"><x-heroicon-o-phone class="mt-0.5 size-4 shrink-0 text-slate-500"/> <span>{{ setting('phone') }}</span></li>
                <li class="flex gap-3"><x-heroicon-o-envelope class="mt-0.5 size-4 shrink-0 text-slate-500"/> <a href="mailto:{{ setting('email') }}" class="tap hover:text-white">{{ setting('email') }}</a></li>
                <li class="flex gap-3"><x-heroicon-o-clock class="mt-0.5 size-4 shrink-0 text-slate-500"/> <span>{{ setting('office_hours') }}</span></li>
            </ul>
            <a href="{{ wa_link() }}" target="_blank" rel="noopener" class="btn btn-wa mt-5">Chat WhatsApp</a>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="container-x flex flex-col gap-2 py-6 pb-24 text-xs lg:pb-6 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ date('Y') }} {{ setting('site_name') }}. Hak cipta dilindungi undang-undang.</p>
            <p><a href="{{ route('sitemap') }}" class="tap hover:text-white">Sitemap</a></p>
        </div>
    </div>
</footer>
