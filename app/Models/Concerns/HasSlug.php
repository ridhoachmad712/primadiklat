<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::saving(function ($model) {
            if (blank($model->slug)) {
                $model->slug = static::uniqueSlug($model->{$model->slugSource ?? 'title'}, $model->id);
            }
        });

        // Changing the slug of a public page keeps the old URL alive with a 301.
        static::updated(function ($model) {
            $route = $model->publicRoute ?? null;
            $old = $model->getOriginal('slug');
            if ($route && $model->wasChanged('slug') && filled($old)) {
                \App\Models\Redirect::record(route($route, $old, false), route($route, $model->slug, false));
            }
        });
    }

    public static function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::random(8);
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
