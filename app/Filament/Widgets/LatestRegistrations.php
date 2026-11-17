<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Registrations\RegistrationResource;
use App\Models\Registration;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestRegistrations extends TableWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Pendaftar terbaru';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Registration::query()->with('training')->latest()->limit(8))
            ->paginated(false)
            ->recordUrl(fn (Registration $r) => RegistrationResource::getUrl('edit', ['record' => $r]))
            ->columns([
                TextColumn::make('name')->label('Peserta')->weight('semibold')->description(fn (Registration $r) => $r->institution),
                TextColumn::make('training.title')->label('Kegiatan')->limit(45),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => Registration::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => ['pending' => 'warning', 'confirmed' => 'info', 'attended' => 'success', 'cancelled' => 'danger'][$state] ?? 'gray'),
                TextColumn::make('created_at')->label('Waktu')->since(),
            ]);
    }
}
