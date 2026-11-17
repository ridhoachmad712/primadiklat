<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Resources\Registrations\RegistrationResource;
use App\Models\Registration;
use App\Support\RegistrationExporter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Ekspor Excel')
                ->color('gray')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(fn () => RegistrationExporter::download($this->getFilteredTableQuery()->get(), 'pendaftar')),
            CreateAction::make()->label('Tambah Pendaftar'),
        ];
    }

    public function getTabs(): array
    {
        $tabs = ['all' => Tab::make('Semua')];
        foreach (Registration::STATUSES as $key => $label) {
            $tabs[$key] = Tab::make($label)
                ->badge(fn () => Registration::where('status', $key)->count() ?: null)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $key));
        }

        return $tabs;
    }
}
