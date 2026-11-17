<?php

namespace App\Filament\Resources\Trainings\Pages;

use App\Filament\Resources\Trainings\TrainingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTrainings extends ListRecords
{
    protected static string $resource = TrainingResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Tambah Kegiatan')];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua'),
            'upcoming' => Tab::make('Akan Datang')->modifyQueryUsing(fn (Builder $query) => $query->whereDate('end_date', '>=', today())),
            'past' => Tab::make('Selesai')->modifyQueryUsing(fn (Builder $query) => $query->whereDate('end_date', '<', today())),
            'draft' => Tab::make('Draf')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft')),
        ];
    }
}
