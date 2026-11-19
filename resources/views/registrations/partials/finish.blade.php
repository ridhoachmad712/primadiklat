            <div class="card p-4 sm:p-6">
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
