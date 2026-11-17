<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Resources\Registrations\RegistrationResource;
use App\Filament\Resources\Registrations\RegistrationSchema;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRegistration extends EditRecord
{
    protected static string $resource = RegistrationResource::class;

    public function getSubheading(): ?string
    {
        return 'Kode '.$this->record->code.' · '.$this->record->training?->title;
    }

    protected function getHeaderActions(): array
    {
        return [
            RegistrationSchema::whatsappAction(),
            DeleteAction::make(),
        ];
    }
}
