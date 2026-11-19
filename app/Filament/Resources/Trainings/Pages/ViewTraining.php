<?php

namespace App\Filament\Resources\Trainings\Pages;

use App\Filament\Resources\Trainings\TrainingResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

/** Read-only class page (used by registration staff), with the participant list below. */
class ViewTraining extends ViewRecord
{
    protected static string $resource = TrainingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            TrainingResource::confirmProposalAction()->record($this->record)
                ->after(fn () => $this->refreshFormData(['status'])),
            Action::make('view_site')->label('Lihat')->color('gray')->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(fn () => route('trainings.show', $this->record), shouldOpenInNewTab: true),
            Action::make('invitation')->label('Undangan PDF')->color('gray')->icon(Heroicon::OutlinedDocumentText)
                ->url(fn () => route('trainings.invitation', $this->record), shouldOpenInNewTab: true),
            EditAction::make(),
        ];
    }
}
