@extends('layouts.app')

@section('title', 'Pendaftaran: '.$training->title)

@section('content')
@php $t = $training; @endphp
<x-page-header title="Formulir Pendaftaran" eyebrow="Daftar Online" :subtitle="$t->title"
    :crumbs="['Jadwal' => route('trainings.index'), \Illuminate\Support\Str::limit($t->title, 40) => route('trainings.show', $t), 'Daftar' => null]"/>

<div class="container-x grid gap-10 py-10 lg:grid-cols-3">
    <div class="lg:col-span-2">
        @if(! $t->can_register)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
                Pendaftaran online untuk kegiatan ini sudah ditutup ({{ $t->availability_label }}). Silakan hubungi admin melalui WhatsApp.
            </div>
        @else
        <form method="POST" action="{{ route('registrations.store', $t) }}" class="space-y-8" x-data="registrationForm()" @submit="submitting = true">
            @csrf
            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

            @if($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                    <p class="font-semibold">Mohon periksa kembali isian Anda.</p>
                    @error('training')<p class="mt-1">{{ $message }}</p>@enderror
                </div>
            @endif

            <fieldset class="card p-6">
                <h2 class="text-lg font-bold">Instansi</h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="institution" class="label">Nama instansi / OPD <span class="text-rose-500">*</span></label>
                        <input id="institution" name="institution" value="{{ old('institution') }}" required class="input" placeholder="BPKAD Kabupaten …">
                        @error('institution')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="institution_address" class="label">Alamat instansi</label>
                        <input id="institution_address" name="institution_address" value="{{ old('institution_address') }}" class="input">
                    </div>
                    <div>
                        <label for="city" class="label">Kota / Kabupaten</label>
                        <input id="city" name="city" value="{{ old('city') }}" class="input">
                    </div>
                </div>
            </fieldset>

            <fieldset class="card p-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold">Data Peserta</h2>
                        <p class="mt-1 text-sm text-slate-500">Nama dicetak pada sertifikat, jadi pastikan penulisan dan gelar sudah benar. Mendaftarkan beberapa orang dari instansi yang sama? Tambahkan di sini, tagihannya dijadikan satu.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600" x-text="people.length + ' peserta'"></span>
                </div>
                @error('participants')<p class="error mt-3">{{ $message }}</p>@enderror

                <template x-for="(p, i) in people" :key="p.key">
                    <div class="mt-6 rounded-xl border border-slate-200 p-5">
                        <div class="mb-4 flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-900">
                                Peserta <span x-text="i + 1"></span>
                                <span x-show="i === 0" class="ml-1 rounded bg-brand-50 px-1.5 py-0.5 text-xs font-medium text-brand-700">Kontak utama</span>
                            </p>
                            <button type="button" x-show="people.length > 1" @click="people.splice(i, 1)" class="inline-flex items-center gap-1 text-xs font-medium text-rose-600 hover:underline">
                                <x-heroicon-o-trash class="size-4"/> Hapus
                            </button>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="label" :for="'p'+i+'name'">Nama lengkap & gelar <span class="text-rose-500">*</span></label>
                                <input :id="'p'+i+'name'" :name="'participants['+i+'][name]'" x-model="p.name" required class="input" autocomplete="name">
                                <p class="error" x-show="err(i, 'name')" x-text="err(i, 'name')"></p>
                            </div>
                            <div>
                                <label class="label" :for="'p'+i+'nip'">NIP / NIK</label>
                                <input :id="'p'+i+'nip'" :name="'participants['+i+'][nip]'" x-model="p.nip" class="input" inputmode="numeric">
                                <p class="error" x-show="err(i, 'nip')" x-text="err(i, 'nip')"></p>
                            </div>
                            <div>
                                <label class="label" :for="'p'+i+'position'">Jabatan</label>
                                <input :id="'p'+i+'position'" :name="'participants['+i+'][position]'" x-model="p.position" class="input" placeholder="Kasubbag Keuangan">
                            </div>
                            <div>
                                <label class="label" :for="'p'+i+'email'">Email <span x-show="i === 0" class="text-rose-500">*</span></label>
                                <input :id="'p'+i+'email'" type="email" :name="'participants['+i+'][email]'" x-model="p.email" :required="i === 0" class="input" :placeholder="i === 0 ? '' : 'Kosongkan jika sama dengan kontak utama'">
                                <p class="error" x-show="err(i, 'email')" x-text="err(i, 'email')"></p>
                            </div>
                            <div>
                                <label class="label" :for="'p'+i+'phone'">No. HP / WhatsApp <span x-show="i === 0" class="text-rose-500">*</span></label>
                                <input :id="'p'+i+'phone'" type="tel" :name="'participants['+i+'][phone]'" x-model="p.phone" :required="i === 0" class="input" placeholder="08xxxxxxxxxx">
                                <p class="error" x-show="err(i, 'phone')" x-text="err(i, 'phone')"></p>
                            </div>
                        </div>
                    </div>
                </template>

                <button type="button" x-show="people.length < max" @click="add()" class="btn btn-outline mt-5 w-full border-dashed">
                    <x-heroicon-o-user-plus class="size-4"/> Tambah peserta
                </button>
                <p x-show="people.length >= max" x-cloak class="mt-4 text-xs text-slate-500">Maksimal <span x-text="max"></span> peserta per pendaftaran. Untuk lebih banyak, silakan hubungi admin.</p>
            </fieldset>

            @if($t->prices)
            <fieldset class="card p-6">
                <h2 class="text-lg font-bold">Pilih Paket</h2>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    @foreach($t->prices as $i => $p)
                        <label class="relative flex cursor-pointer gap-3 rounded-xl border border-slate-200 p-4 transition has-checked:border-brand-500 has-checked:bg-brand-50/50 has-checked:ring-4 has-checked:ring-brand-500/10">
                            <input type="radio" name="package" value="{{ $p['name'] }}" @change="package = $el.value" class="mt-1 accent-brand-600" @checked(old('package', $t->prices[0]['name']) === $p['name']) required>
                            <span>
                                <span class="block text-sm font-semibold text-slate-900">{{ $p['name'] }}</span>
                                <span class="block text-base font-bold text-brand-700">{{ rupiah($p['price']) }}</span>
                                @if(! empty($p['note']))<span class="mt-1 block text-xs text-slate-500">{{ $p['note'] }}</span>@endif
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('package')<p class="error">{{ $message }}</p>@enderror
            </fieldset>
            @endif

            <div class="card p-6">
                <label for="notes" class="label">Catatan (opsional)</label>
                <textarea id="notes" name="notes" rows="3" class="input" placeholder="Contoh: kebutuhan khusus, permintaan kamar, dll.">{{ old('notes') }}</textarea>
                <label class="mt-5 flex items-start gap-3 text-sm text-slate-600">
                    <input type="checkbox" name="agree" value="1" class="mt-0.5 size-4 accent-brand-600" @checked(old('agree')) required>
                    <span>Saya menyetujui <a href="{{ url('/halaman/syarat-dan-ketentuan') }}" target="_blank" class="font-medium text-brand-700 hover:underline">syarat & ketentuan</a> serta <a href="{{ url('/halaman/kebijakan-privasi') }}" target="_blank" class="font-medium text-brand-700 hover:underline">kebijakan privasi</a>.</span>
                </label>
                @error('agree')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div x-show="price > 0" class="flex items-center justify-between rounded-2xl bg-slate-900 px-6 py-4 text-white">
                <span class="text-sm text-slate-300">Total biaya (<span x-text="people.length"></span> &times; <span x-text="rp(price)"></span>)</span>
                <span class="text-xl font-bold" x-text="rp(price * people.length)"></span>
            </div>

            <button class="btn btn-primary w-full !py-3.5 text-base sm:w-auto" :disabled="submitting">
                <span x-show="!submitting">Kirim Pendaftaran</span>
                <span x-show="submitting" x-cloak>Mengirim…</span>
            </button>
        </form>
        @endif
    </div>

    <aside>
        <div class="card sticky top-24 overflow-hidden">
            <div class="aspect-[16/9] bg-slate-100">
                @if($t->thumbnail_url)<img src="{{ $t->thumbnail_url }}" alt="" class="size-full object-cover">@else<x-placeholder-cover :seed="$t->id" :label="$t->type_label"/>@endif
            </div>
            <div class="p-5">
                <h3 class="font-bold leading-snug">{{ $t->title }}</h3>
                <dl class="mt-4 space-y-2 text-sm text-slate-600">
                    <div class="flex gap-2"><x-heroicon-o-calendar-days class="size-4 shrink-0 text-slate-400"/>{{ $t->date_range }}</div>
                    <div class="flex gap-2"><x-heroicon-o-map-pin class="size-4 shrink-0 text-slate-400"/>{{ $t->location }}</div>
                    @if($t->quota)<div class="flex gap-2"><x-heroicon-o-users class="size-4 shrink-0 text-slate-400"/>Sisa {{ $t->remaining_quota }} kursi</div>@endif
                </dl>
                <a href="{{ route('trainings.invitation', $t) }}" target="_blank" class="btn btn-outline mt-5 w-full"><x-heroicon-o-arrow-down-tray class="size-4"/> Surat Undangan</a>
            </div>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
    function registrationForm() {
        const blank = () => ({ key: Math.random().toString(36).slice(2), name: '', nip: '', position: '', email: '', phone: '' });
        const old = @js(array_values(old('participants', [['name' => '', 'nip' => '', 'position' => '', 'email' => '', 'phone' => '']])));
        return {
            submitting: false,
            max: {{ \App\Http\Controllers\RegistrationController::MAX_PARTICIPANTS }},
            people: old.map(p => ({ ...blank(), ...p })),
            errors: @js(collect($errors->getMessages())->filter(fn ($m, $k) => str_starts_with($k, 'participants.'))->map(fn ($m) => $m[0])),
            prices: @js(collect($t->prices ?? [])->pluck('price', 'name')),
            package: @js(old('package', $t->prices[0]['name'] ?? null)),
            get price() { return Number(this.prices[this.package] || 0); },
            add() { this.people.push(blank()); this.$nextTick(() => document.getElementById('p' + (this.people.length - 1) + 'name')?.focus()); },
            err(i, f) { return this.errors['participants.' + i + '.' + f] || ''; },
            rp(v) { return 'Rp ' + Number(v).toLocaleString('id-ID'); },
        };
    }
</script>
@endpush
