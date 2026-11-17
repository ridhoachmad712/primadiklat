<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('rupiah')) {
    function rupiah(int|float|string|null $value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }
}

if (! function_exists('storage_url')) {
    function storage_url(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}

if (! function_exists('wa_link')) {
    function wa_link(?string $message = null, ?string $number = null): string
    {
        $number = preg_replace('/\D/', '', $number ?? (string) setting('whatsapp'));

        return 'https://wa.me/'.$number.'?text='.rawurlencode($message ?? (string) setting('whatsapp_message'));
    }
}

if (! function_exists('roman_month')) {
    function roman_month(int $month): string
    {
        return ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$month] ?? (string) $month;
    }
}

if (! function_exists('terbilang')) {
    /** Spell out a number in Indonesian, e.g. 1500000 → "satu juta lima ratus ribu". */
    function terbilang(int|float $n): string
    {
        $n = (int) abs($n);
        $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        $spell = function (int $n) use (&$spell, $words): string {
            return match (true) {
                $n < 12 => $words[$n],
                $n < 20 => $spell($n - 10).' belas',
                $n < 100 => $spell(intdiv($n, 10)).' puluh '.$spell($n % 10),
                $n < 200 => 'seratus '.$spell($n - 100),
                $n < 1000 => $spell(intdiv($n, 100)).' ratus '.$spell($n % 100),
                $n < 2000 => 'seribu '.$spell($n - 1000),
                $n < 1000000 => $spell(intdiv($n, 1000)).' ribu '.$spell($n % 1000),
                $n < 1000000000 => $spell(intdiv($n, 1000000)).' juta '.$spell($n % 1000000),
                $n < 1000000000000 => $spell(intdiv($n, 1000000000)).' miliar '.$spell($n % 1000000000),
                default => $spell(intdiv($n, 1000000000000)).' triliun '.$spell($n % 1000000000000),
            };
        };

        return $n === 0 ? 'nol' : trim(preg_replace('/\s+/', ' ', $spell($n)));
    }
}
