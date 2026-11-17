<?php

namespace App\Filament\Widgets;

use App\Models\InhouseRequest;
use App\Models\Registration;
use App\Models\Training;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $trend = collect(range(6, 0))->map(fn ($d) => Registration::whereDate('created_at', today()->subDays($d))->count())->all();
        $thisMonth = Registration::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $lastMonth = Registration::whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->count();
        $diff = $thisMonth - $lastMonth;

        $revenue = Registration::where('payment_status', 'paid')->whereYear('created_at', now()->year)->sum('price');

        return [
            Stat::make('Kegiatan akan datang', Training::upcoming()->count())
                ->description(Training::published()->whereBetween('start_date', [today(), today()->addDays(30)])->count().' dalam 30 hari ke depan')
                ->icon(Heroicon::OutlinedCalendarDays),
            Stat::make('Pendaftar bulan ini', $thisMonth)
                ->description(($diff >= 0 ? '+' : '').$diff.' dibanding bulan lalu')
                ->descriptionIcon($diff >= 0 ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown)
                ->color($diff >= 0 ? 'success' : 'danger')
                ->chart($trend)
                ->icon(Heroicon::OutlinedUserGroup),
            Stat::make('Menunggu verifikasi', Registration::where('status', 'pending')->count())
                ->description(InhouseRequest::where('status', 'new')->count().' permintaan in-house baru')
                ->color('warning')
                ->icon(Heroicon::OutlinedClock),
            Stat::make('Pemasukan '.now()->year, 'Rp '.number_format($revenue / 1_000_000, 1, ',', '.').' jt')
                ->description('Dari pendaftar berstatus lunas')
                ->icon(Heroicon::OutlinedBanknotes),
        ];
    }
}
