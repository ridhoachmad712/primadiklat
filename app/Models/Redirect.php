<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A permanent (301) redirect from an old public URL path to its new path. */
class Redirect extends Model
{
    protected $fillable = ['from_path', 'to_path', 'hits', 'last_hit_at'];

    protected $casts = ['last_hit_at' => 'datetime'];

    public static function normalize(string $path): string
    {
        return '/'.trim(parse_url($path, PHP_URL_PATH) ?? $path, '/');
    }

    /** Record from -> to, keeping chains flat and never redirecting a live URL. */
    public static function record(string $from, string $to): void
    {
        $from = static::normalize($from);
        $to = static::normalize($to);
        if ($from === $to) {
            return;
        }

        static::where('to_path', $from)->update(['to_path' => $to]);
        static::where('from_path', $to)->delete();
        static::updateOrCreate(['from_path' => $from], ['to_path' => $to]);
    }

    public static function findFor(string $path): ?self
    {
        return static::where('from_path', static::normalize($path))->first();
    }
}
