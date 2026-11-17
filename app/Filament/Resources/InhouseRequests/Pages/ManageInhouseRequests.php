<?php

namespace App\Filament\Resources\InhouseRequests\Pages;

use App\Filament\Resources\InhouseRequests\InhouseRequestResource;
use Filament\Resources\Pages\ManageRecords;

class ManageInhouseRequests extends ManageRecords
{
    protected static string $resource = InhouseRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
