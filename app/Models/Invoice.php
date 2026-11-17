<?php

namespace App\Models;

use App\Mail\InvoiceReady;
use App\Mail\PaymentVerified;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * One bill per registration batch (a single participant or a whole institution's group).
 * Payment status lives here; registrations are kept in sync when it changes.
 */
class Invoice extends Model
{
    public const STATUSES = [
        'unpaid' => 'Belum Bayar',
        'waiting' => 'Menunggu Verifikasi',
        'paid' => 'Lunas',
        'cancelled' => 'Dibatalkan',
    ];

    public const STATUS_COLORS = ['unpaid' => 'gray', 'waiting' => 'warning', 'paid' => 'success', 'cancelled' => 'danger'];

    protected $fillable = [
        'training_id', 'number', 'institution', 'contact_name', 'contact_email', 'contact_phone', 'amount',
        'status', 'payment_proof', 'proof_uploaded_at', 'paid_at', 'receipt_number', 'verified_by', 'admin_note',
    ];

    protected $attributes = ['status' => 'unpaid', 'amount' => 0];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'proof_uploaded_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            $invoice->number ??= static::nextNumber('INV', 'number', $invoice->created_at ?? now());
        });

        // However an invoice becomes paid (verify button or manual edit), it gets a receipt and syncs participants.
        static::saving(function (Invoice $invoice) {
            if ($invoice->status === 'paid' && $invoice->exists && $invoice->isDirty('status')) {
                $invoice->paid_at ??= now();
                $invoice->receipt_number ??= static::nextNumber('KWT', 'receipt_number', $invoice->paid_at);
            }
        });

        static::updated(function (Invoice $invoice) {
            if ($invoice->status === 'paid' && $invoice->wasChanged('status')) {
                $invoice->registrations()->where('status', '!=', 'cancelled')->update(['payment_status' => 'paid']);
                $invoice->registrations()->where('status', 'pending')->update(['status' => 'confirmed']);
            }
        });
    }

    /** INV-2611-0001: prefix, year+month, running number within that month. */
    public static function nextNumber(string $prefix, string $column, \DateTimeInterface $date): string
    {
        $base = $prefix.'-'.$date->format('ym').'-';
        $last = static::where($column, 'like', $base.'%')->orderByDesc($column)->value($column);
        $seq = $last ? ((int) substr($last, strlen($base))) + 1 : 1;

        return $base.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /** Build an invoice for registrations that were saved before one existed (old data, admin-created rows). */
    public static function createFor(Collection $registrations): self
    {
        $first = $registrations->first();
        $paid = $registrations->every(fn (Registration $r) => $r->payment_status === 'paid');

        $invoice = (new static)->forceFill([
            'training_id' => $first->training_id,
            'institution' => $first->institution,
            'contact_name' => $first->name,
            'contact_email' => $first->email,
            'contact_phone' => $first->phone,
            'status' => $paid ? 'paid' : 'unpaid',
            'paid_at' => $paid ? $first->updated_at : null,
            'created_at' => $first->created_at,
        ]);
        $invoice->save();
        if ($paid) {
            $invoice->update(['receipt_number' => static::nextNumber('KWT', 'receipt_number', $first->updated_at ?? now())]);
        }

        Registration::whereKey($registrations->pluck("id"))->update(['invoice_id' => $invoice->id]);
        $invoice->recalculate();

        return $invoice;
    }

    public static function createForOrphans(): void
    {
        Registration::whereNull('invoice_id')->orderBy('id')->each(fn (Registration $r) => static::createFor(collect([$r])));
    }

    public function recalculate(): void
    {
        $this->updateQuietly([
            'amount' => (int) $this->registrations()->where('status', '!=', 'cancelled')->sum('price'),
        ]);
    }

    public function markPaid(?User $by = null, bool $notify = true): void
    {
        $this->update(['status' => 'paid', 'verified_by' => $by?->id, 'admin_note' => null]);

        if ($notify) {
            try {
                Mail::to($this->contact_email)->send(new PaymentVerified($this));
            } catch (\Throwable $e) {
                Log::warning('Payment mail failed: '.$e->getMessage());
            }
        }
    }

    /** Apply one per-participant price to the whole batch (used after the WhatsApp price follow-up). */
    public function setPrice(int $price, ?string $package = null, bool $notify = true): void
    {
        $this->registrations()->where('status', '!=', 'cancelled')
            ->update(array_filter(['price' => $price, 'package' => $package], fn ($v) => $v !== null));
        $this->recalculate();

        if ($notify && $this->amount > 0) {
            try {
                Mail::to($this->contact_email)->send(new InvoiceReady($this->fresh()));
            } catch (\Throwable $e) {
                Log::warning('Invoice mail failed: '.$e->getMessage());
            }
        }
    }

    public function getContactPhoneWaAttribute(): string
    {
        $n = preg_replace('/\D/', '', (string) $this->contact_phone);

        return str_starts_with($n, '0') ? '62'.substr($n, 1) : $n;
    }

    public function rejectProof(string $reason): void
    {
        $this->update(['status' => 'unpaid', 'admin_note' => $reason]);
    }

    /** Any registration code in the batch opens the public payment page. */
    public function getPublicCodeAttribute(): ?string
    {
        return $this->registrations()->orderBy('id')->value('code');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getProofUrlAttribute(): ?string
    {
        return storage_url($this->payment_proof);
    }

    public function getCanUploadProofAttribute(): bool
    {
        return in_array($this->status, ['unpaid', 'waiting'], true) && $this->amount > 0;
    }
}
