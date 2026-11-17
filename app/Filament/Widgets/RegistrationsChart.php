<?php

namespace App\Filament\Widgets;

use App\Models\Registration;
use Filament\Widgets\ChartWidget;

class RegistrationsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Pendaftar per bulan';

    protected ?string $description = '12 bulan terakhir';

    protected int|string|array $columnSpan = 2;

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $months = collect(range(11, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));

        return [
            'datasets' => [[
                'label' => 'Pendaftar',
                'data' => $months->map(fn ($m) => Registration::whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->count())->all(),
                'backgroundColor' => '#1d4ed8',
                'borderRadius' => 6,
                'maxBarThickness' => 28,
            ]],
            'labels' => $months->map(fn ($m) => $m->translatedFormat('M y'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0], 'grid' => ['color' => 'rgba(148,163,184,0.15)']],
                'x' => ['grid' => ['display' => false]],
            ],
        ];
    }
}
