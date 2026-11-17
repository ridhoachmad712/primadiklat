<?php

namespace App\Filament\Resources\Trainings\Pages;

use App\Filament\Resources\Trainings\TrainingResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditTraining extends EditRecord
{
    protected static string $resource = TrainingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_site')->label('Lihat')->color('gray')->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(fn () => route('trainings.show', $this->record), shouldOpenInNewTab: true),
            Action::make('invitation')->label('Undangan PDF')->color('gray')->icon(Heroicon::OutlinedDocumentText)
                ->url(fn () => route('trainings.invitation', $this->record), shouldOpenInNewTab: true),
            DeleteAction::make(),
        ];
    }
}
