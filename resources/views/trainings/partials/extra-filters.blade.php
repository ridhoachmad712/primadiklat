<select name="jenis" class="input lg:w-36" aria-label="Jenis">
    <option value="">Semua jenis</option>
    @foreach(\App\Models\Training::TYPES as $k => $v)<option value="{{ $k }}" @selected(($filters['jenis'] ?? '') === $k)>{{ $v }}</option>@endforeach
</select>
<select name="kota" class="input lg:w-36" aria-label="Kota">
    <option value="">Semua kota</option>
    @foreach($cities as $city)<option value="{{ $city }}" @selected(($filters['kota'] ?? '') === $city)>{{ $city }}</option>@endforeach
</select>
<select name="metode" class="input lg:w-36" aria-label="Metode">
    <option value="">Semua metode</option>
    @foreach(\App\Models\Training::METHODS as $k => $v)<option value="{{ $k }}" @selected(($filters['metode'] ?? '') === $k)>{{ $v }}</option>@endforeach
</select>
