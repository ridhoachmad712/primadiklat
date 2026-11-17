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
        'hero_subtitle' => 'Pilih tema, tanggal, dan kota pelaksanaan sesuai kebutuhan instansi Anda. Kegiatan berlangsung setiap pekan di 13 kota, dengan narasumber praktisi dan sertifikat resmi.',
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
        'stat_alumni' => '5000',
        'stat_trainings' => '',
        'stat_partners' => '',
        'stat_years' => '10',
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

    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => is_array($value) ? json_encode($value) : $value]);
        }
        Cache::forget('settings.stored');
    }
}
