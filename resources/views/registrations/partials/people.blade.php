            <fieldset class="card p-4 sm:p-6">
                <h2 class="text-lg font-bold">Instansi</h2>
                <div class="mt-5 grid gap-4 sm:mt-6 sm:grid-cols-2 sm:gap-5">
                    <div class="sm:col-span-2">
                        <label for="institution" class="label">Nama instansi / OPD <span class="text-rose-600">*</span></label>
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

            <fieldset class="card p-4 sm:p-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold">Data Peserta</h2>
                        <p class="mt-1 text-sm text-slate-500">Nama dicetak pada sertifikat, jadi pastikan penulisan dan gelar sudah benar. Mendaftarkan beberapa orang dari instansi yang sama? Tambahkan di sini, tagihannya dijadikan satu.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600" x-text="people.length + ' peserta'"></span>
                </div>
                @error('participants')<p class="error mt-3">{{ $message }}</p>@enderror

                <template x-for="(p, i) in people" :key="p.key">
                    <div class="mt-5 rounded-xl border border-slate-200 p-3.5 sm:mt-6 sm:p-5">
                        <div class="mb-4 flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-900">
                                Peserta <span x-text="i + 1"></span>
                                <span x-show="i === 0" class="ml-1 rounded bg-brand-50 px-1.5 py-0.5 text-xs font-medium text-brand-700">Kontak utama</span>
                            </p>
                            <button type="button" x-show="people.length > 1" @click="people.splice(i, 1)" class="inline-flex items-center gap-1 text-xs font-medium text-rose-600 hover:underline">
                                <x-heroicon-o-trash class="size-4"/> Hapus
                            </button>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2 sm:gap-5">
                            <div class="sm:col-span-2">
                                <label class="label" :for="'p'+i+'name'">Nama lengkap & gelar <span class="text-rose-600">*</span></label>
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
                                <label class="label" :for="'p'+i+'email'">Email <span x-show="i === 0" class="text-rose-600">*</span></label>
                                <input :id="'p'+i+'email'" type="email" :name="'participants['+i+'][email]'" x-model="p.email" :required="i === 0" class="input" :placeholder="i === 0 ? '' : 'Kosongkan jika sama dengan kontak utama'">
                                <p class="error" x-show="err(i, 'email')" x-text="err(i, 'email')"></p>
                            </div>
                            <div>
                                <label class="label" :for="'p'+i+'phone'">No. HP / WhatsApp <span x-show="i === 0" class="text-rose-600">*</span></label>
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
