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
