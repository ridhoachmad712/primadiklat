<?php

namespace App\Filament\Resources\Partners;

use App\Filament\Concerns\RestrictedByRole;
use App\Models\Partner;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class PartnerResource extends Resource
{
    use RestrictedByRole;

    protected static ?string $model = Partner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|UnitEnum|null $navigationGroup = 'Konten Website';

    protected static ?string $navigationLabel = 'Mitra & Klien';

    protected static ?string $modelLabel = 'mitra';

    protected static ?string $pluralModelLabel = 'Mitra & Klien';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            TextInput::make('name')->label('Nama instansi')->required(),
            TextInput::make('url')->label('Website')->url(),
            FileUpload::make('logo')->image()->disk('public')->directory('partners')->maxSize(1024),
            Toggle::make('is_active')->label('Tampilkan')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                ImageColumn::make('logo')->label('')->disk('public')->imageHeight(36),
                TextColumn::make('name')->label('Nama')->searchable()->weight('semibold'),
                ToggleColumn::make('is_active')->label('Tampil'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePartners::route('/')];
    }
}
