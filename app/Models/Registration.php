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
        'training_id', 'invoice_id', 'code', 'name', 'nip', 'position', 'institution', 'institution_address', 'city',
        'email', 'phone', 'package', 'price', 'notes', 'status', 'payment_status', 'certificate_number',
        'certificate_issued_at', 'certificate_file',
    ];

    protected function casts(): array
    {
        return ['certificate_issued_at' => 'datetime', 'price' => 'integer'];
    }

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

        // Rows created outside the public form (admin, seeder) still need a bill.
        static::created(function (Registration $r) {
            if (! $r->invoice_id) {
                Invoice::createFor(collect([$r]));
                $r->refresh();
            }
        });

        static::saved(function (Registration $r) {
            if ($r->invoice_id && $r->wasChanged(['price', 'status', 'invoice_id'])) {
                $r->invoice?->recalculate();
            }
        });

        static::deleted(fn (Registration $r) => $r->invoice?->recalculate());
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /** Give this participant a certificate number (idempotent). Format comes from site settings. */
    public function issueCertificate(): bool
    {
        if ($this->certificate_number || $this->status !== 'attended') {
            return false;
        }

        $date = $this->training?->end_date ?? now();
        $format = setting('certificate_number_format', '{no}/PD-SRT/{bulan}/{tahun}');
        $seq = static::whereYear('certificate_issued_at', $date->year)->count() + 1;

        do {
            $number = strtr($format, [
                '{no}' => str_pad((string) $seq++, 4, '0', STR_PAD_LEFT),
                '{bulan}' => roman_month($date->month),
                '{tahun}' => $date->format('Y'),
                '{kegiatan}' => (string) $this->training_id,
            ]);
        } while (static::where('certificate_number', $number)->exists());

        return $this->update(['certificate_number' => $number, 'certificate_issued_at' => now()]);
    }

    /** Certificate can be shown/downloaded publicly. */
    public function getHasCertificateAttribute(): bool
    {
        return $this->status === 'attended'
            && filled($this->certificate_number)
            && (! setting('certificate_require_paid', '1') || $this->payment_status === 'paid' || $this->certificate_file);
    }

    public function getCertificateDownloadUrlAttribute(): ?string
    {
        return $this->has_certificate ? route('certificates.download', $this->code) : null;
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
