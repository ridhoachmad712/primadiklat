<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public const DEFAULTS = [
        'site_name' => 'Prima Diklat',
        'site_tagline' => 'Pusat Informasi Bimtek & Diklat Aparatur',
        'site_description' => 'Prima Diklat menyelenggarakan bimbingan teknis, diklat, dan pelatihan untuk aparatur pemerintah, BUMN/BUMD, perguruan tinggi, dan swasta di seluruh Indonesia.',
        'hero_title' => 'Jadwal Bimtek 2027 Sudah Dibuka',
        'hero_subtitle' => 'Temukan tema sesuai kebutuhan instansi Anda, lalu pilih tanggal dan kota pelaksanaan. Kegiatan berlangsung setiap pekan dengan narasumber praktisi dan sertifikat resmi.',
        'phone' => '0812-0000-0000',
        'whatsapp' => '6281200000000',
        'whatsapp_message' => 'Halo Admin Prima Diklat, saya ingin bertanya mengenai jadwal bimtek.',
        'email' => 'info@primadiklat.test',
        'address' => 'Jl. Contoh No. 1, Jakarta',
        'office_hours' => 'Senin – Jumat, 08.00 – 17.00 WIB',
        'maps_embed' => '',
        'facebook' => '',
        'instagram' => '',
        'youtube' => '',
        'linkedin' => '',
        'stat_alumni' => '',
        'stat_years' => '',
        'hero_image' => '',
        'brand_color' => 'blue',
        'home_sections' => '[]',
        'bank_info' => "Bank Mandiri\nNo. Rek: 000-000-0000\na.n. Prima Diklat",
        'invitation_signatory' => 'Direktur Prima Diklat',
        'invitation_signatory_name' => 'Nama Direktur',
        'schedule_close_days' => '3',
        'price_notice' => 'Informasi biaya kontribusi akan disampaikan oleh admin melalui WhatsApp setelah pendaftaran diterima.',
        'payment_instructions' => 'Setelah transfer, unggah bukti pembayaran melalui halaman status pendaftaran. Kuitansi resmi dapat diunduh setelah pembayaran diverifikasi.',
        'certificate_number_format' => '{no}/PD-SRT/{bulan}/{tahun}',
        'certificate_require_paid' => '1',
        'certificate_text' => 'Atas partisipasinya sebagai PESERTA dalam kegiatan',
        'certificate_signatory' => 'Direktur Prima Diklat',
        'certificate_signatory_name' => 'Nama Direktur',
        'certificate_background' => '',
        'legal_name' => '',
        'legal_deed' => '',
        'legal_nib' => '',
        'legal_npwp' => '',
        'legal_other' => '',
        'legal_document' => '',
        'contacts' => '[]',
        'og_image' => '',
        'logo' => '',
        'favicon' => '',
        'notify_email' => '',
        'about_short' => 'Lembaga penyelenggara pelatihan dan bimbingan teknis yang berfokus pada peningkatan kompetensi SDM aparatur dan profesional.',
    ];

    public static function all_cached(): array
    {
        // Only stored values are cached, so changed defaults apply without clearing the cache.
        $stored = Cache::rememberForever('settings.stored', function () {
            return Schema::hasTable('settings') ? static::query()->pluck('value', 'key')->all() : [];
        });

        return array_merge(self::DEFAULTS, $stored);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::all_cached()[$key] ?? null;

        return filled($value) ? $value : $default;
    }

    /** Decode a setting stored as JSON (e.g. repeater data). */
    public static function json(string $key): array
    {
        $value = static::all_cached()[$key] ?? null;
        if (is_array($value)) {
            return $value;
        }
        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /** Legal identity rows that have a value, as [label => value]. */
    public static function legalItems(): array
    {
        $items = array_filter([
            'Badan hukum' => static::get('legal_name'),
            'Akta / SK Kemenkumham' => static::get('legal_deed'),
            'NIB' => static::get('legal_nib'),
            'NPWP' => static::get('legal_npwp'),
        ]);
        foreach (preg_split('/\R/', (string) static::get('legal_other')) as $line) {
            if (str_contains($line, ':')) {
                [$label, $value] = array_map('trim', explode(':', $line, 2));
                if ($label !== '' && $value !== '') {
                    $items[$label] = $value;
                }
            } elseif (trim($line) !== '') {
                $items[] = trim($line);
            }
        }

        return $items;
    }

    /**
     * Homepage sections below the hero. Order, visibility and texts are editable
     * in Settings; `limit` is null for sections that show a fixed set.
     */
    public const HOME_SECTIONS = [
        'topics' => ['label' => 'Katalog Tema', 'eyebrow' => 'Tema Bimtek', 'title' => 'Pilih Tema Sesuai Kebutuhan Instansi',
            'intro' => 'Setiap tema dapat diikuti pada seluruh tanggal dan kota pelaksanaan. Tema belum tersedia? Usulkan saat mendaftar.', 'limit' => 6, 'max' => 12],
        'trust' => ['label' => 'Legalitas & Kepercayaan', 'eyebrow' => 'Legalitas', 'title' => 'Lembaga Resmi, Dipercaya Instansi',
            'intro' => 'Legalitas lembaga dan rekam jejak kami dalam peningkatan kompetensi aparatur.', 'limit' => null, 'max' => null],
        'testimonials' => ['label' => 'Testimoni', 'eyebrow' => 'Testimoni', 'title' => 'Apa Kata Alumni Peserta',
            'intro' => '', 'limit' => 3, 'max' => 6],
        'contacts' => ['label' => 'Narahubung', 'eyebrow' => 'Narahubung', 'title' => 'Ada Pertanyaan? Hubungi Kami Langsung',
            'intro' => 'Konsultasi tema, jadwal, biaya kontribusi, maupun in-house training.', 'limit' => null, 'max' => null],
        'posts' => ['label' => 'Berita Terbaru', 'eyebrow' => 'Publikasi', 'title' => 'Berita & Artikel Terbaru',
            'intro' => '', 'limit' => 3, 'max' => 6],
    ];

    /** Stored section config merged with defaults: known keys only, new sections appended, all fields present. */
    public static function homeSections(): array
    {
        $stored = collect(static::json('home_sections'))->filter(fn ($s) => isset(self::HOME_SECTIONS[$s['key'] ?? null]))->keyBy('key');
        $keys = $stored->keys()->merge(array_keys(self::HOME_SECTIONS))->unique();

        return $keys->map(function ($key) use ($stored) {
            $default = self::HOME_SECTIONS[$key];
            $s = $stored->get($key, []);
            $limit = $default['limit'] === null ? null : max(1, min($default['max'], (int) ($s['limit'] ?? $default['limit'])));

            return [
                'key' => $key,
                'label' => $default['label'],
                'enabled' => (bool) ($s['enabled'] ?? true),
                'eyebrow' => array_key_exists('eyebrow', $s) ? (string) $s['eyebrow'] : $default['eyebrow'],
                'title' => filled($s['title'] ?? null) ? $s['title'] : $default['title'],
                'intro' => array_key_exists('intro', $s) ? (string) $s['intro'] : $default['intro'],
                'limit' => $limit,
            ];
        })->values()->all();
    }

    /** Named admin contacts with a valid WhatsApp number. */
    public static function contacts(): array
    {
        return array_values(array_filter(static::json('contacts'), fn ($c) => filled($c['name'] ?? null) && filled($c['whatsapp'] ?? null)));
    }

    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => is_array($value) ? json_encode($value) : $value]);
        }
        Cache::forget('settings.stored');
    }
}
