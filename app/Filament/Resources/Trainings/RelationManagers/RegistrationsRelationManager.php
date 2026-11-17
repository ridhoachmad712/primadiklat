<?php

namespace App\Filament\Resources\Trainings\RelationManagers;

use App\Filament\Resources\Registrations\RegistrationSchema;
use App\Support\RegistrationExporter;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $title = 'Peserta Terdaftar';

    protected static ?string $modelLabel = 'peserta';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->registrations()->count();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components(RegistrationSchema::form(withTraining: false));
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('created_at', 'desc')
            ->columns(RegistrationSchema::columns(withTraining: false))
            ->filters(RegistrationSchema::filters())
            ->headerActions([
                Action::make('export')
                    ->label('Ekspor Excel')
                    ->color('gray')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->action(fn () => RegistrationExporter::download(
                        $this->getOwnerRecord()->registrations()->get(),
                        'peserta-'.$this->getOwnerRecord()->slug
                    )),
                CreateAction::make()->label('Tambah peserta'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    RegistrationSchema::whatsappAction(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions(RegistrationSchema::bulkActions());
    }
}
