@extends('layouts.app')

@section('content')
{{-- HERO --}}
<section class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(50rem_30rem_at_90%_-20%,rgba(29,78,216,0.14),transparent),radial-gradient(30rem_20rem_at_0%_110%,rgba(245,158,11,0.10),transparent)]"></div>
    <div class="container-x relative grid items-center gap-12 py-14 lg:grid-cols-12 lg:py-24">
        <div class="lg:col-span-7">
            <span class="inline-flex items-center gap-2 rounded-full border border-brand-100 bg-white px-3 py-1 text-xs font-semibold text-brand-700">
                <span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span></span>
                {{ \App\Models\Training::upcoming()->count() }} jadwal kegiatan tersedia
            </span>
            <h1 class="mt-5 text-4xl leading-[1.1] font-extrabold sm:text-5xl lg:text-[3.4rem]">{{ setting('hero_title') }}</h1>
            <p class="mt-5 max-w-xl text-lg text-slate-600">{{ setting('hero_subtitle') }}</p>

            <form action="{{ route('trainings.index') }}" class="mt-8 flex max-w-xl flex-col gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-200/50 sm:flex-row">
                <label class="flex flex-1 items-center gap-2 px-3">
                    <x-heroicon-o-magnifying-glass class="size-5 text-slate-400"/>
                    <span class="sr-only">Cari kegiatan</span>
                    <input type="search" name="q" placeholder="Cari topik bimtek atau diklat…" class="w-full border-0 bg-transparent py-2.5 text-sm outline-none placeholder:text-slate-400">
                </label>
                <select name="kategori" class="rounded-xl border-0 bg-slate-50 px-3 py-2.5 text-sm text-slate-600 outline-none sm:w-48" aria-label="Kategori">
                    <option value="">Semua kategori</option>
                    @foreach($categories as $c)<option value="{{ $c->slug }}">{{ $c->name }}</option>@endforeach
                </select>
                <button class="btn btn-primary">Cari Jadwal</button>
            </form>

            <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-slate-500">
                <span class="inline-flex items-center gap-1.5"><x-heroicon-s-check-badge class="size-4 text-brand-600"/> Sertifikat resmi</span>
                <span class="inline-flex items-center gap-1.5"><x-heroicon-s-check-badge class="size-4 text-brand-600"/> Narasumber praktisi</span>
                <span class="inline-flex items-center gap-1.5"><x-heroicon-s-check-badge class="size-4 text-brand-600"/> Undangan resmi untuk SPPD</span>
            </div>
        </div>

        @if($featured)
        <div class="lg:col-span-5">
            <div class="relative">
                <div class="absolute -inset-4 -z-10 rotate-2 rounded-[2rem] bg-brand-100/50"></div>
                <a href="{{ route('trainings.show', $featured) }}" class="group block overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-300/40">
                    <div class="aspect-[16/9] overflow-hidden">
                        @if($featured->thumbnail_url)
                            <img src="{{ $featured->thumbnail_url }}" alt="" class="size-full object-cover">
                        @else
                            <x-placeholder-cover :seed="$featured->id" :label="'Unggulan · '.$featured->type_label"/>
                        @endif
                    </div>
                    <div class="p-6">
                        <p class="text-xs font-semibold text-brand-700">{{ $featured->category?->name }}</p>
                        <h2 class="mt-1.5 text-lg leading-snug font-bold group-hover:text-brand-700">{{ $featured->title }}</h2>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-xl bg-slate-50 p-3"><p class="text-[11px] text-slate-400">Tanggal</p><p class="font-semibold text-slate-800">{{ $featured->date_range }}</p></div>
                            <div class="rounded-xl bg-slate-50 p-3"><p class="text-[11px] text-slate-400">Lokasi</p><p class="truncate font-semibold text-slate-800">{{ $featured->method === 'online' ? 'Online' : $featured->city }}</p></div>
                        </div>
                        <div class="mt-5 flex items-center justify-between">
                            <span class="text-sm text-slate-500">@if($featured->lowest_price)Mulai <strong class="text-slate-900">{{ rupiah($featured->lowest_price) }}</strong>@endif</span>
                            <span class="btn btn-primary !py-2">Lihat Detail</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        @endif
    </div>
</section>

{{-- STATS --}}
<section class="border-y border-slate-200/70 bg-slate-50/60">
    <div class="container-x grid grid-cols-2 divide-slate-200 py-8 md:grid-cols-4 md:divide-x">
        @foreach([
            [number_format($stats['alumni'], 0, ',', '.').'+', 'Alumni peserta'],
            [number_format($stats['trainings'], 0, ',', '.').'+', 'Kegiatan terselenggara'],
            [number_format($stats['partners'], 0, ',', '.').'+', 'Instansi mitra'],
            [$stats['years'].'+', 'Tahun pengalaman'],
        ] as [$value, $label])
            <div class="px-4 py-3 text-center">
                <p class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $value }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $label }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- UPCOMING --}}
<section class="container-x py-20">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="eyebrow">Jadwal Terdekat</p>
            <h2 class="section-title mt-2">Bimtek & Diklat yang Akan Datang</h2>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('trainings.calendar') }}" class="btn btn-outline"><x-heroicon-o-calendar class="size-4"/> Kalender</a>
            <a href="{{ route('trainings.index') }}" class="btn btn-outline">Semua jadwal <x-heroicon-m-arrow-right class="size-4"/></a>
        </div>
    </div>
    @if($upcoming->isEmpty())
        <div class="card mt-10 p-10 text-center text-slate-500">Belum ada jadwal kegiatan. Hubungi kami untuk info terbaru.</div>
    @else
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($upcoming as $t)<x-training-card :training="$t"/>@endforeach
        </div>
    @endif
</section>

{{-- CATEGORIES --}}
@if($categories->isNotEmpty())
<section class="bg-slate-50/70 py-20">
    <div class="container-x">
        <div class="max-w-2xl">
            <p class="eyebrow">Bidang Program</p>
            <h2 class="section-title mt-2">Temukan Pelatihan Sesuai Kebutuhan Instansi</h2>
        </div>
        <div class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($categories as $c)
                <a href="{{ route('trainings.index', ['kategori' => $c->slug]) }}" class="group card flex items-start gap-4 p-5 transition hover:border-brand-200 hover:bg-white hover:shadow-lg hover:shadow-slate-200/50">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 transition group-hover:bg-brand-600 group-hover:text-white">
                        <x-heroicon-o-academic-cap class="size-5"/>
                    </span>
                    <span>
                        <span class="block font-semibold text-slate-900">{{ $c->name }}</span>
                        <span class="mt-0.5 block text-xs text-slate-500">{{ $c->trainings_count }} jadwal tersedia</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- WHY + HOW --}}
<section class="container-x grid gap-14 py-20 lg:grid-cols-2">
    <div>
        <p class="eyebrow">Mengapa Prima Diklat</p>
        <h2 class="section-title mt-2">Pelatihan yang Praktis, Tertib Administrasi, dan Tepat Regulasi</h2>
        <div class="mt-8 grid gap-6 sm:grid-cols-2">
            @foreach([
                ['heroicon-o-user-group', 'Narasumber Praktisi', 'Akademisi, praktisi, dan pejabat kementerian yang berpengalaman.'],
                ['heroicon-o-document-check', 'Materi Sesuai Regulasi', 'Kurikulum diperbarui mengikuti peraturan terbaru.'],
                ['heroicon-o-document-text', 'Administrasi Lengkap', 'Undangan resmi, daftar hadir, kuitansi, dan sertifikat.'],
                ['heroicon-o-building-office', 'Hotel Nyaman', 'Diselenggarakan di hotel berbintang dengan fasilitas lengkap.'],
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
        <h3 class="mt-2 text-2xl font-bold text-white">4 langkah mudah</h3>
        <ol class="mt-8 space-y-6">
            @foreach([
                ['Pilih kegiatan', 'Temukan jadwal dan topik yang sesuai melalui menu Jadwal atau Kalender.'],
                ['Unduh undangan', 'Gunakan surat undangan PDF resmi untuk proses SPPD di instansi Anda.'],
                ['Daftar online', 'Isi formulir singkat dan dapatkan kode pendaftaran secara instan.'],
                ['Ikuti & dapatkan sertifikat', 'Hadir sesuai jadwal, sertifikat dapat dicek dan diunduh online.'],
            ] as $i => [$title, $desc])
                <li class="flex gap-4">
                    <span class="grid size-8 shrink-0 place-items-center rounded-full bg-brand-600 text-sm font-bold text-white">{{ $i + 1 }}</span>
                    <div><p class="font-semibold text-white">{{ $title }}</p><p class="mt-0.5 text-sm text-slate-400">{{ $desc }}</p></div>
                </li>
            @endforeach
        </ol>
        <a href="{{ route('trainings.index') }}" class="btn btn-primary mt-8 w-full">Lihat Jadwal & Daftar</a>
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
