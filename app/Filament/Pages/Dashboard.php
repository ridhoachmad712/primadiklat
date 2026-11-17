<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dasbor';

    public function getSubheading(): ?string
    {
        return 'Selamat datang, '.auth()->user()?->name.'. Ringkasan aktivitas Prima Diklat hari ini.';
    }

    public function getColumns(): int|array
    {
        return ['default' => 1, 'lg' => 3];
    }
}
