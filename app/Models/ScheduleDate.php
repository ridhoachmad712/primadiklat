<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One date slot of the yearly schedule (e.g. 07–08 Januari 2027). */
class ScheduleDate extends Model
{
    protected $fillable = ['start_date', 'end_date', 'note', 'is_active'];

    protected $attributes = ['is_active' => true];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'is_active' => 'boolean'];

    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class);
    }

    public static function closeDays(): int
    {
        return max(0, (int) setting('schedule_close_days', 3));
    }

    /** Last day registration is accepted for this slot. */
    public function getDeadlineAttribute()
    {
        return $this->start_date->copy()->subDays(static::closeDays());
    }

    public function getIsOpenAttribute(): bool
    {
        return $this->is_active && today()->lte($this->deadline);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('start_date');
    }

    /** Slots still accepting registrations. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->active()->whereDate('start_date', '>', today()->addDays(static::closeDays() - 1));
    }

    public function getLabelAttribute(): string
    {
        $s = $this->start_date;
        $e = $this->end_date;
        if ($s->isSameDay($e)) {
            return $s->translatedFormat('d F Y');
        }

        return $s->isSameMonth($e)
            ? $s->format('d').' – '.$e->translatedFormat('d F Y')
            : $s->translatedFormat('d F').' – '.$e->translatedFormat('d F Y');
    }

    public function getDaysLabelAttribute(): string
    {
        return $this->start_date->translatedFormat('l').($this->start_date->isSameDay($this->end_date) ? '' : ' – '.$this->end_date->translatedFormat('l'));
    }
}
