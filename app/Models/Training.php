<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Training extends Model
{
    use HasSlug;

    public const TYPES = [
        'bimtek' => 'Bimtek',
        'diklat' => 'Diklat',
        'pelatihan' => 'Pelatihan',
        'workshop' => 'Workshop',
        'sertifikasi' => 'Sertifikasi',
    ];

    public const METHODS = [
        'offline' => 'Tatap Muka',
        'online' => 'Daring (Online)',
        'hybrid' => 'Hybrid',
    ];

    public const STATUSES = [
        'draft' => 'Draf',
        'published' => 'Terbit',
        'cancelled' => 'Dibatalkan',
    ];

    protected $fillable = [
        'category_id', 'type', 'title', 'slug', 'excerpt', 'description', 'materials', 'target_participants',
        'thumbnail', 'brochure', 'start_date', 'end_date', 'registration_deadline', 'method', 'city', 'venue',
        'venue_address', 'quota', 'prices', 'facilities', 'status', 'is_featured', 'registration_open',
        'meta_title', 'meta_description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_deadline' => 'date',
        'prices' => 'array',
        'facilities' => 'array',
        'is_featured' => 'boolean',
        'registration_open' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->published()->whereDate('end_date', '>=', today())->orderBy('start_date');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHODS[$this->method] ?? ucfirst($this->method);
    }

    public function getDateRangeAttribute(): string
    {
        $s = $this->start_date;
        $e = $this->end_date;
        if (! $s) {
            return '-';
        }
        if (! $e || $s->isSameDay($e)) {
            return $s->translatedFormat('j F Y');
        }
        if ($s->isSameMonth($e)) {
            return $s->format('j').' – '.$e->translatedFormat('j F Y');
        }
        if ($s->isSameYear($e)) {
            return $s->translatedFormat('j M').' – '.$e->translatedFormat('j M Y');
        }

        return $s->translatedFormat('j M Y').' – '.$e->translatedFormat('j M Y');
    }

    public function getDurationDaysAttribute(): int
    {
        return $this->start_date && $this->end_date ? $this->start_date->diffInDays($this->end_date) + 1 : 1;
    }

    public function getLowestPriceAttribute(): ?int
    {
        $prices = collect($this->prices ?? [])->pluck('price')->filter()->map(fn ($p) => (int) $p);

        return $prices->isEmpty() ? null : $prices->min();
    }

    public function getRegisteredCountAttribute(): int
    {
        return $this->registrations_count ?? $this->registrations()->where('status', '!=', 'cancelled')->count();
    }

    public function getRemainingQuotaAttribute(): ?int
    {
        return $this->quota ? max(0, $this->quota - $this->registered_count) : null;
    }

    public function getIsFinishedAttribute(): bool
    {
        return $this->end_date?->lt(today()) ?? false;
    }

    public function getCanRegisterAttribute(): bool
    {
        if ($this->status !== 'published' || ! $this->registration_open || $this->is_finished) {
            return false;
        }
        if ($this->registration_deadline && $this->registration_deadline->lt(today())) {
            return false;
        }

        return $this->remaining_quota === null || $this->remaining_quota > 0;
    }

    public function getAvailabilityLabelAttribute(): string
    {
        return match (true) {
            $this->status === 'cancelled' => 'Dibatalkan',
            $this->is_finished => 'Selesai',
            $this->start_date->lte(today()) => 'Berlangsung',
            $this->remaining_quota === 0 => 'Kuota Penuh',
            ! $this->can_register => 'Pendaftaran Ditutup',
            default => 'Pendaftaran Dibuka',
        };
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail ? Storage::disk('public')->url($this->thumbnail) : null;
    }

    public function getBrochureUrlAttribute(): ?string
    {
        return $this->brochure ? Storage::disk('public')->url($this->brochure) : null;
    }

    public function getLocationAttribute(): string
    {
        if ($this->method === 'online') {
            return 'Online (Zoom Meeting)';
        }

        return collect([$this->venue, $this->city])->filter()->implode(', ') ?: '-';
    }
}
