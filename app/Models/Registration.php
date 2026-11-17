<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Registration extends Model
{
    public const STATUSES = [
        'pending' => 'Menunggu Verifikasi',
        'confirmed' => 'Terkonfirmasi',
        'attended' => 'Hadir / Lulus',
        'cancelled' => 'Dibatalkan',
    ];

    public const PAYMENT_STATUSES = [
        'unpaid' => 'Belum Bayar',
        'paid' => 'Lunas',
        'refunded' => 'Dikembalikan',
    ];

    protected $fillable = [
        'training_id', 'code', 'name', 'nip', 'position', 'institution', 'institution_address', 'city',
        'email', 'phone', 'package', 'price', 'notes', 'status', 'payment_status', 'certificate_number',
        'certificate_file',
    ];

    protected static function booted(): void
    {
        static::creating(function (Registration $r) {
            if (blank($r->code)) {
                do {
                    $code = 'PD-'.now()->format('ym').'-'.strtoupper(Str::random(5));
                } while (static::where('code', $code)->exists());
                $r->code = $code;
            }
        });
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getCertificateUrlAttribute(): ?string
    {
        return $this->certificate_file ? Storage::disk('public')->url($this->certificate_file) : null;
    }

    public function getWhatsappNumberAttribute(): string
    {
        $n = preg_replace('/\D/', '', $this->phone);

        return str_starts_with($n, '0') ? '62'.substr($n, 1) : $n;
    }
}
