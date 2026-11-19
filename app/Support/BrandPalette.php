<?php

namespace App\Support;

/**
 * Brand colour scales selectable in Settings. Every scale keeps white text on
 * shade 600 and shade 600/700 text on white at WCAG AA (4.5:1) or better.
 */
class BrandPalette
{
    public const DEFAULT = 'blue';

    public const PALETTES = [
        'blue' => ['label' => 'Biru', 'shades' => [
            50 => '#eff5ff', 100 => '#dbe7fe', 200 => '#bfd5fe', 300 => '#93b8fd', 400 => '#6093fa', 500 => '#3b6ff6',
            600 => '#1d4ed8', 700 => '#1a3fb4', 800 => '#1b3591', 900 => '#1c3173', 950 => '#121e45',
        ]],
        'green' => ['label' => 'Hijau', 'shades' => [
            50 => '#f0fdf4', 100 => '#dcfce7', 200 => '#bbf7d0', 300 => '#86efac', 400 => '#4ade80', 500 => '#16a34a',
            600 => '#15803d', 700 => '#166534', 800 => '#14532d', 900 => '#0f3d22', 950 => '#052e16',
        ]],
        'teal' => ['label' => 'Toska', 'shades' => [
            50 => '#f0fdfa', 100 => '#ccfbf1', 200 => '#99f6e4', 300 => '#5eead4', 400 => '#2dd4bf', 500 => '#0d9488',
            600 => '#0f766e', 700 => '#115e59', 800 => '#134e4a', 900 => '#0f3b38', 950 => '#042f2e',
        ]],
        'red' => ['label' => 'Merah marun', 'shades' => [
            50 => '#fef2f2', 100 => '#fee2e2', 200 => '#fecaca', 300 => '#fca5a5', 400 => '#f87171', 500 => '#dc2626',
            600 => '#b91c1c', 700 => '#991b1b', 800 => '#7f1d1d', 900 => '#651515', 950 => '#450a0a',
        ]],
        'violet' => ['label' => 'Ungu', 'shades' => [
            50 => '#f5f3ff', 100 => '#ede9fe', 200 => '#ddd6fe', 300 => '#c4b5fd', 400 => '#a78bfa', 500 => '#8b5cf6',
            600 => '#7c3aed', 700 => '#6d28d9', 800 => '#5b21b6', 900 => '#4c1d95', 950 => '#2e1065',
        ]],
    ];

    public static function options(): array
    {
        return array_map(fn ($p) => $p['label'], self::PALETTES);
    }

    public static function shades(?string $key): array
    {
        return (self::PALETTES[$key] ?? self::PALETTES[self::DEFAULT])['shades'];
    }

    /** CSS overriding Tailwind's --color-brand-* theme variables. */
    public static function css(?string $key): string
    {
        $vars = collect(self::shades($key))->map(fn ($hex, $shade) => "--color-brand-{$shade}:{$hex}")->join(';');

        return ":root{{$vars}}";
    }
}
