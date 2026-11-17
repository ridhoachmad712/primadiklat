<?php

namespace App\Filament\Resources\Registrations;

use App\Filament\Concerns\RestrictedByRole;
use App\Models\Registration;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class RegistrationResource extends Resource
{
    use RestrictedByRole;

    protected static ?string $model = Registration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Kegiatan';

    protected static ?string $navigationLabel = 'Pendaftar';

    protected static ?string $modelLabel = 'pendaftar';

    protected static ?string $pluralModelLabel = 'Pendaftar';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    public static function allowedRoles(): array
    {
        return [User::ROLE_REGISTRAR];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Registration::where('status', 'pending')->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Menunggu verifikasi';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'code', 'institution', 'email', 'phone'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(RegistrationSchema::form());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->with('training', 'invoice'))
            ->defaultSort('created_at', 'desc')
            ->columns(RegistrationSchema::columns())
            ->filters([
                SelectFilter::make('training_id')->label('Kegiatan')->relationship('training', 'title')->searchable()->preload(),
                ...RegistrationSchema::filters(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    RegistrationSchema::whatsappAction(),
                    RegistrationSchema::certificateAction(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions(RegistrationSchema::bulkActions())
            ->emptyStateHeading('Belum ada pendaftar');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrations::route('/'),
            'create' => Pages\CreateRegistration::route('/create'),
            'edit' => Pages\EditRegistration::route('/{record}/edit'),
        ];
    }
}
