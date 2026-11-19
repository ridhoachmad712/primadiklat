<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('preview')->color('gray')->icon(\Filament\Support\Icons\Heroicon::OutlinedEye)
                ->label(fn () => $this->record->is_published ? 'Lihat di website' : 'Pratinjau')
                ->url(fn () => route('posts.show', $this->record), shouldOpenInNewTab: true),
            DeleteAction::make(),
        ];
    }
}
