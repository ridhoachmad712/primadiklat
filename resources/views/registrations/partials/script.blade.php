@push('scripts')
<script>
    function registrationForm(extra = {}) {
        const blank = () => ({ key: Math.random().toString(36).slice(2), name: '', nip: '', position: '', email: '', phone: '' });
        const old = @js(array_values(old('participants', [['name' => '', 'nip' => '', 'position' => '', 'email' => '', 'phone' => '']])));
        return Object.assign({
            submitting: false,
            max: {{ \App\Http\Controllers\RegistrationController::MAX_PARTICIPANTS }},
            people: old.map(p => ({ ...blank(), ...p })),
            errors: @js(collect($errors->getMessages())->filter(fn ($m, $k) => str_starts_with($k, 'participants.'))->map(fn ($m) => $m[0])),
            prices: @js(collect($prices ?? [])->pluck('price', 'name')),
            package: @js(old('package', ($prices ?? [])[0]['name'] ?? null)),
            get price() { return Number(this.prices[this.package] || 0); },
            // Open-schedule form state (overridden through `extra`).
            topic: '', customTopic: '', date: '', venue: '', topicLabels: {}, dateLabels: {}, venueLabels: {}, topicSlugs: {},
            get topicText() { return this.topic === 'other' ? (this.customTopic || 'Tema usulan') : (this.topicLabels[this.topic] || ''); },
            add() { this.people.push(blank()); this.$nextTick(() => document.getElementById('p' + (this.people.length - 1) + 'name')?.focus()); },
            err(i, f) { return this.errors['participants.' + i + '.' + f] || ''; },
            rp(v) { return 'Rp ' + Number(v).toLocaleString('id-ID'); },
        }, extra);
    }
</script>
@endpush
