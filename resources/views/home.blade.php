@extends('layouts.app')

@section('content')
{{-- HERO: quick pick tema + tanggal + kota --}}
<section class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(50rem_30rem_at_90%_-20%,rgba(29,78,216,0.14),transparent),radial-gradient(30rem_20rem_at_0%_110%,rgba(245,158,11,0.10),transparent)]"></div>
    <div class="container-x relative grid items-center gap-12 py-14 lg:grid-cols-12 lg:py-20">
        <div class="lg:col-span-7">
            <span class="inline-flex items-center gap-2 rounded-full border border-brand-100 bg-white px-3 py-1 text-xs font-semibold text-brand-700">
                <span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span></span>
                Pendaftaran dibuka · {{ $openDates->count() }} jadwal di {{ $cities->count() }} kota
            </span>
            <h1 class="mt-5 text-4xl leading-[1.1] font-extrabold sm:text-5xl lg:text-[3.4rem]">{{ setting('hero_title') }}</h1>
            <p class="mt-5 max-w-xl text-lg text-slate-600">{{ setting('hero_subtitle') }}</p>

            <form action="{{ route('register.create') }}" method="GET" class="mt-8 grid max-w-2xl gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-200/50 sm:grid-cols-2">
                <label class="sr-only" for="hero-tema">Tema</label>
                <select id="hero-tema" name="tema" class="min-w-0 rounded-xl border-0 bg-slate-50 px-3 py-3 text-sm text-slate-700 outline-none sm:col-span-2">
                    <option value="">Semua tema</option>
                    @foreach($topics as $tp)<option value="{{ $tp->slug }}">{{ \Illuminate\Support\Str::limit($tp->title, 60) }}</option>@endforeach
                </select>
                <label class="sr-only" for="hero-tanggal">Tanggal</label>
                <select id="hero-tanggal" name="tanggal" class="min-w-0 rounded-xl border-0 bg-slate-50 px-3 py-3 text-sm text-slate-700 outline-none">
                    <option value="">Tanggal</option>
                    @foreach($openDates->groupBy(fn ($d) => $d->start_date->translatedFormat('F Y')) as $m => $items)
                        <optgroup label="{{ $m }}">@foreach($items as $d)<option value="{{ $d->start_date->toDateString() }}">{{ $d->label }}</option>@endforeach</optgroup>
                    @endforeach
                </select>
                <label class="sr-only" for="hero-kota">Kota</label>
                <select id="hero-kota" name="hotel" class="min-w-0 rounded-xl border-0 bg-slate-50 px-3 py-3 text-sm text-slate-700 outline-none">
                    <option value="">Kota</option>
                    @foreach($cities as $city => $items)
                        <optgroup label="{{ $city }}">@foreach($items as $v)<option value="{{ $v->id }}">{{ $city }} · {{ $v->name }}</option>@endforeach</optgroup>
                    @endforeach
                </select>
                <button class="btn btn-primary sm:col-span-2">Lanjut Daftar</button>
            </form>

            <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-slate-500">
                <span class="inline-flex items-center gap-1.5"><x-heroicon-s-check-badge class="size-4 text-brand-600"/> Bebas pilih tema, tanggal & kota</span>
                <span class="inline-flex items-center gap-1.5"><x-heroicon-s-check-badge class="size-4 text-brand-600"/> Daftar rombongan sekaligus</span>
                <span class="inline-flex items-center gap-1.5"><x-heroicon-s-check-badge class="size-4 text-brand-600"/> Undangan resmi untuk SPPD</span>
            </div>
        </div>

        <div class="lg:col-span-5">
            <div class="relative">
                <div class="absolute -inset-4 -z-10 rotate-2 rounded-[2rem] bg-brand-100/50"></div>
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-300/40">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                        <div>
                            <p class="text-xs font-semibold text-brand-700">Jadwal Terdekat</p>
                            <p class="font-bold text-slate-900">Bimtek 2 hari, setiap pekan</p>
                        </div>
                        <x-heroicon-o-calendar-days class="size-7 text-slate-300"/>
                    </div>
                    <ul class="divide-y divide-slate-100">
                        @forelse($openDates->take(5) as $d)
                            <li>
                                <a href="{{ route('register.create', ['tanggal' => $d->start_date->toDateString()]) }}" class="group flex items-center gap-4 px-6 py-3.5 hover:bg-slate-50">
                                    <span class="flex w-12 shrink-0 flex-col items-center rounded-xl border border-slate-200 py-1">
                                        <span class="text-lg leading-none font-extrabold text-slate-900">{{ $d->start_date->format('d') }}</span>
                                        <span class="text-[10px] font-semibold text-brand-600 uppercase">{{ $d->start_date->translatedFormat('M') }}</span>
                                    </span>
                                    <span class="flex-1">
                                        <span class="block text-sm font-semibold text-slate-900">{{ $d->label }}</span>
                                        <span class="block text-xs text-slate-500">{{ $d->days_label }} · {{ $cities->count() }} kota tersedia</span>
                                    </span>
                                    <span class="text-xs font-semibold text-brand-700 transition group-hover:translate-x-0.5">Daftar →</span>
                                </a>
                            </li>
                        @empty
                            <li class="px-6 py-8 text-center text-sm text-slate-500">Jadwal baru segera diumumkan.</li>
                        @endforelse
                    </ul>
                    <a href="{{ route('schedule.index') }}" class="block border-t border-slate-100 bg-slate-50 px-6 py-3 text-center text-sm font-semibold text-brand-700 hover:bg-brand-50">Lihat jadwal Januari – Desember {{ $year }}</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- STATS --}}
<section class="border-y border-slate-200/70 bg-slate-50/60">
    <div class="container-x grid grid-cols-2 divide-slate-200 py-8 md:grid-cols-4 md:divide-x">
        @foreach([
            [$yearDates->count(), 'Jadwal tahun '.$year],
            [$cities->count(), 'Kota pelaksanaan'],
            [$topics->count().'+', 'Tema bimtek'],
            [number_format($stats['alumni'], 0, ',', '.').'+', 'Alumni peserta'],
        ] as [$value, $label])
            <div class="px-4 py-3 text-center">
                <p class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $value }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $label }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- TOPICS --}}
<section class="container-x py-20">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="eyebrow">Tema Bimtek</p>
            <h2 class="section-title mt-2">Pilih Tema Sesuai Kebutuhan Instansi</h2>
            <p class="mt-2 max-w-2xl text-slate-600">Setiap tema dapat diikuti pada seluruh tanggal dan kota pelaksanaan.</p>
        </div>
        <a href="{{ route('topics.index') }}" class="btn btn-outline">Semua tema <x-heroicon-m-arrow-right class="size-4"/></a>
    </div>
    @if($categories->isNotEmpty())
        <div class="mt-8 flex flex-wrap gap-2">
            @foreach($categories as $c)
                <a href="{{ route('topics.index', ['kategori' => $c->slug]) }}" class="chip hover:bg-slate-200">{{ $c->name }} <span class="opacity-60">{{ $c->topics_count }}</span></a>
            @endforeach
        </div>
    @endif
    @if($featuredTopics->isEmpty())
        <div class="card mt-10 p-10 text-center text-slate-500">Daftar tema segera diumumkan. Anda tetap dapat mendaftar dengan mengusulkan tema.</div>
    @else
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($featuredTopics as $topic)<x-topic-card :topic="$topic"/>@endforeach
        </div>
    @endif
</section>

{{-- YEAR SCHEDULE OVERVIEW --}}
@if($yearDates->isNotEmpty())
<section class="bg-slate-50/70 py-20">
    <div class="container-x">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="eyebrow">Jadwal {{ $year }}</p>
                <h2 class="section-title mt-2">Januari – Desember {{ $year }}</h2>
                <p class="mt-2 text-slate-600">Kegiatan berlangsung 2 hari. Klik tanggal untuk mendaftar.</p>
            </div>
            <a href="{{ route('schedule.index') }}" class="btn btn-outline">Jadwal lengkap <x-heroicon-m-arrow-right class="size-4"/></a>
        </div>
        <div class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach(range(1, 12) as $m)
                @php $items = $yearMonths->get($m, collect()); @endphp
                <div class="card p-4">
                    <p class="text-xs font-bold tracking-wide text-slate-900 uppercase">{{ \Carbon\Carbon::create($year, $m, 1)->translatedFormat('F') }}</p>
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @forelse($items as $d)
                            @if($d->is_open)
                                <a href="{{ route('register.create', ['tanggal' => $d->start_date->toDateString()]) }}" title="{{ $d->label }} ({{ $d->days_label }})"
                                   class="rounded-lg bg-brand-50 px-2 py-1 text-xs font-semibold text-brand-800 ring-1 ring-brand-100 hover:bg-brand-600 hover:text-white">{{ $d->start_date->format('d') }}–{{ $d->end_date->format('d') }}</a>
                            @else
                                <span class="rounded-lg bg-slate-100 px-2 py-1 text-xs text-slate-400 line-through">{{ $d->start_date->format('d') }}–{{ $d->end_date->format('d') }}</span>
                            @endif
                        @empty
                            <span class="text-xs text-slate-400">—</span>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- CITIES --}}
@if($cities->isNotEmpty())
<section class="container-x py-20">
    <div class="max-w-2xl">
        <p class="eyebrow">Lokasi Kegiatan</p>
        <h2 class="section-title mt-2">{{ $cities->count() }} Kota, {{ $venues->count() }} Hotel Pilihan</h2>
        <p class="mt-2 text-slate-600">Pilih kota terdekat atau yang paling sesuai dengan rencana perjalanan dinas Anda.</p>
    </div>
    <div class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($cities as $city => $hotels)
            <a href="{{ route('register.create', ['hotel' => $hotels->first()->id]) }}" class="group card flex items-start gap-3 p-4 transition hover:border-brand-200 hover:shadow-lg hover:shadow-slate-200/50">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 transition group-hover:bg-brand-600 group-hover:text-white"><x-heroicon-o-map-pin class="size-5"/></span>
                <span class="min-w-0">
                    <span class="block font-semibold text-slate-900">{{ $city }}</span>
                    <span class="block truncate text-xs text-slate-500">{{ $hotels->pluck('name')->join(', ') }}</span>
                </span>
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- HOW TO REGISTER --}}
<section class="container-x grid gap-14 pb-20 lg:grid-cols-2">
    <div>
        <p class="eyebrow">Mengapa Prima Diklat</p>
        <h2 class="section-title mt-2">Fleksibel, Tertib Administrasi, dan Tepat Regulasi</h2>
        <div class="mt-8 grid gap-6 sm:grid-cols-2">
            @foreach([
                ['heroicon-o-adjustments-horizontal', 'Jadwal Fleksibel', 'Tema, tanggal, dan kota bisa dipilih bebas mengikuti agenda instansi.'],
                ['heroicon-o-user-group', 'Narasumber Praktisi', 'Akademisi, praktisi, dan pejabat kementerian yang berpengalaman.'],
                ['heroicon-o-document-text', 'Administrasi Lengkap', 'Undangan resmi, invoice, kuitansi, daftar hadir, dan sertifikat ber-QR.'],
                ['heroicon-o-building-office', 'Hotel Nyaman', 'Diselenggarakan di hotel pilihan di '.$cities->count().' kota.'],
            ] as [$icon, $title, $desc])
                <div>
                    <span class="grid size-10 place-items-center rounded-xl bg-amber-50 text-amber-600"><x-dynamic-component :component="$icon" class="size-5"/></span>
                    <h3 class="mt-3 font-semibold">{{ $title }}</h3>
                    <p class="mt-1 text-sm text-slate-600">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
    <div class="card bg-slate-950 p-8 text-slate-300 sm:p-10">
        <p class="text-xs font-semibold tracking-[0.14em] text-brand-300 uppercase">Alur Pendaftaran</p>
        <h3 class="mt-2 text-2xl font-bold text-white">5 langkah mudah</h3>
        <ol class="mt-8 space-y-6">
            @foreach([
                ['Pilih tema, tanggal & kota', 'Tentukan tema bimtek, salah satu jadwal, dan hotel pelaksanaan. Tema yang belum ada bisa diusulkan.'],
                ['Isi data peserta', 'Satu formulir untuk satu atau beberapa peserta dari instansi yang sama. Kode pendaftaran langsung terbit.'],
                ['Konfirmasi biaya via WhatsApp', 'Admin menghubungi kontak Anda untuk menyampaikan biaya kontribusi, lalu tagihan dikirim ke email.'],
                ['Bayar & unggah bukti transfer', 'Unggah bukti di halaman status pendaftaran. Kuitansi resmi terbit setelah diverifikasi.'],
                ['Ikuti kegiatan & unduh sertifikat', 'Hadir sesuai jadwal. Sertifikat ber-QR dapat diunduh di menu Cek Sertifikat.'],
            ] as $i => [$title, $desc])
                <li class="flex gap-4">
                    <span class="grid size-8 shrink-0 place-items-center rounded-full bg-brand-600 text-sm font-bold text-white">{{ $i + 1 }}</span>
                    <div><p class="font-semibold text-white">{{ $title }}</p><p class="mt-0.5 text-sm text-slate-400">{{ $desc }}</p></div>
                </li>
            @endforeach
        </ol>
        <a href="{{ route('register.create') }}" class="btn btn-primary mt-8 w-full">Daftar Sekarang</a>
    </div>
</section>

{{-- TESTIMONIALS --}}
@if($testimonials->isNotEmpty())
<section class="bg-slate-50/70 py-20">
    <div class="container-x">
        <div class="max-w-2xl">
            <p class="eyebrow">Testimoni</p>
            <h2 class="section-title mt-2">Apa Kata Alumni Peserta</h2>
        </div>
        <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach($testimonials->take(3) as $t)
                <figure class="card flex flex-col p-6">
                    <div class="flex gap-0.5 text-amber-400">@for($s = 0; $s < $t->rating; $s++)<x-heroicon-s-star class="size-4"/>@endfor</div>
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
</section>
@endif

{{-- PARTNERS --}}
@if($partners->isNotEmpty())
<section class="container-x py-16">
    <p class="text-center text-sm font-medium text-slate-500">Dipercaya oleh instansi pemerintah dan lembaga di seluruh Indonesia</p>
    <div class="mt-8 flex flex-wrap items-center justify-center gap-x-10 gap-y-6">
        @foreach($partners as $p)
            @if($p->logo_url)
                <img src="{{ $p->logo_url }}" alt="{{ $p->name }}" class="h-10 w-auto opacity-70 grayscale transition hover:opacity-100 hover:grayscale-0">
            @else
                <span class="text-sm font-bold tracking-tight text-slate-400">{{ $p->name }}</span>
            @endif
        @endforeach
    </div>
</section>
@endif

{{-- NEWS --}}
@if($posts->isNotEmpty())
<section class="container-x py-16">
    <div class="flex items-end justify-between gap-4">
        <div>
            <p class="eyebrow">Publikasi</p>
            <h2 class="section-title mt-2">Berita & Artikel Terbaru</h2>
        </div>
        <a href="{{ route('posts.index') }}" class="hidden text-sm font-semibold text-brand-700 hover:underline sm:inline">Lihat semua →</a>
    </div>
    <div class="mt-10 grid gap-8 md:grid-cols-3">
        @foreach($posts as $post)<x-post-card :post="$post"/>@endforeach
    </div>
</section>
@endif

{{-- CTA IN-HOUSE --}}
<section class="container-x">
    <div class="relative overflow-hidden rounded-3xl bg-brand-600 px-6 py-12 text-white sm:px-12 sm:py-16">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(30rem_20rem_at_100%_0%,rgba(255,255,255,0.18),transparent)]"></div>
        <div class="relative grid items-center gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <h2 class="text-2xl font-bold text-white sm:text-3xl">Butuh pelatihan khusus di instansi Anda?</h2>
                <p class="mt-3 max-w-2xl text-brand-100">Kami menyelenggarakan in-house training dengan materi, jadwal, dan lokasi yang disesuaikan dengan kebutuhan instansi.</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row lg:justify-end">
                <a href="{{ route('inhouse.create') }}" class="btn bg-white text-brand-700 hover:bg-brand-50">Ajukan In-House</a>
                <a href="{{ wa_link('Halo Admin, saya ingin konsultasi in-house training.') }}" target="_blank" rel="noopener" class="btn border border-white/30 text-white hover:bg-white/10">Konsultasi via WA</a>
            </div>
        </div>
    </div>
</section>
@endsection
