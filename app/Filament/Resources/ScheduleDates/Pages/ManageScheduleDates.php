<?php

namespace App\Filament\Resources\ScheduleDates\Pages;

use App\Filament\Resources\ScheduleDates\ScheduleDateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageScheduleDates extends ManageRecords
{
    protected static string $resource = ScheduleDateResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Tambah tanggal')];
    }
}
