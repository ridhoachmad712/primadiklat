<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Trainings\TrainingResource;
use App\Models\Training;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingTrainings extends TableWidget
{
    protected static ?int $sort = 3;

    /** Operational figures are not shown to writers (Penulis). */
    public static function canView(): bool
    {
        return ! auth()->user()?->isWriter();
    }

    protected static ?string $heading = 'Kegiatan terdekat';

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Training::upcoming()->withCount(['registrations' => fn ($q) => $q->where('status', '!=', 'cancelled')])->limit(6))
            ->paginated(false)
            ->defaultSort('start_date')
            ->recordUrl(fn (Training $r) => TrainingResource::getUrl('edit', ['record' => $r]))
            ->columns([
                TextColumn::make('title')->label('Kegiatan')->weight('semibold')->limit(38)
                    ->description(fn (Training $r) => $r->date_range.' · '.($r->city ?: $r->method_label)),
                TextColumn::make('registrations_count')->label('Peserta')->badge()->color('primary')->alignEnd(),
            ]);
    }
}
