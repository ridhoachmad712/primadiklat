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
        'hero_title' => 'Tingkatkan Kompetensi Aparatur Bersama Prima Diklat',
        'hero_subtitle' => 'Jadwal bimtek dan diklat terbaru, narasumber praktisi, materi sesuai regulasi terkini, dan sertifikat resmi.',
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
        'logo' => '',
        'favicon' => '',
        'notify_email' => '',
        'about_short' => 'Lembaga penyelenggara pelatihan dan bimbingan teknis yang berfokus pada peningkatan kompetensi SDM aparatur dan profesional.',
    ];

    public static function all_cached(): array
    {
        return Cache::rememberForever('settings.all', function () {
            if (! Schema::hasTable('settings')) {
                return self::DEFAULTS;
            }

            return array_merge(self::DEFAULTS, static::query()->pluck('value', 'key')->all());
        });
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
        Cache::forget('settings.all');
    }
}
