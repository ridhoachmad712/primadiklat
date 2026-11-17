<?php

namespace App\Filament\Resources\Venues;

use App\Filament\Concerns\RestrictedByRole;
use App\Models\Venue;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class VenueResource extends Resource
{
    use RestrictedByRole;

    protected static ?string $model = Venue::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static string|UnitEnum|null $navigationGroup = 'Jadwal & Tema';

    protected static ?string $navigationLabel = 'Kota & Hotel';

    protected static ?string $modelLabel = 'hotel';

    protected static ?string $pluralModelLabel = 'Kota & Hotel';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('city')->label('Kota')->required()
                ->datalist(fn () => Venue::query()->distinct()->orderBy('city')->pluck('city')->all()),
            TextInput::make('name')->label('Nama hotel')->required(),
            TextInput::make('address')->label('Alamat')->columnSpanFull(),
            TextInput::make('maps_url')->label('Tautan Google Maps')->url()->columnSpanFull(),
            Toggle::make('is_active')->label('Tampil & bisa dipilih')->default(true),
            TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('trainings'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('city')->label('Kota')->searchable()->weight('semibold'),
                TextColumn::make('name')->label('Hotel')->searchable()->description(fn (Venue $record) => $record->address)->wrap(),
                TextColumn::make('trainings_count')->label('Kelas terbentuk')->alignCenter(),
                ToggleColumn::make('is_active')->label('Aktif'),
            ])
            ->recordActions([
                ActionGroup::make([EditAction::make(), DeleteAction::make()]),
            ])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageVenues::route('/')];
    }
}
