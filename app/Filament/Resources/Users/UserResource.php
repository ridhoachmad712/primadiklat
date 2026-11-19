<?php

namespace App\Filament\Resources\Users;

use App\Filament\Concerns\RestrictedByRole;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    use RestrictedByRole;

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Pengguna Admin';

    protected static ?string $modelLabel = 'pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna Admin';

    protected static ?int $navigationSort = 2;

    public static function allowedRoles(): array
    {
        return [User::ROLE_SUPER_ADMIN];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            TextInput::make('name')->label('Nama')->required(),
            TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            Select::make('role')->label('Peran')->options(User::ROLES)->required()->native(false)
                // Nobody can change their own role, so a super admin cannot lock themselves out.
                ->disabled(fn (?User $record) => $record?->is(auth()->user()))
                ->helperText(fn (?User $record) => $record?->is(auth()->user())
                    ? 'Peran akun Anda sendiri tidak dapat diubah. Minta Super Admin lain bila perlu.'
                    : 'Editor Konten: kegiatan, jadwal & konten website. Admin Pendaftaran: pendaftar, tagihan, pesan masuk, serta melihat & mengonfirmasi kelas. Penulis: menulis berita/artikel sebagai draf, diterbitkan oleh Editor.'),
            TextInput::make('password')->password()->revealable()
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn (?string $state) => filled($state))
                ->minLength(8)
                ->helperText(fn (string $operation) => $operation === 'edit' ? 'Kosongkan bila tidak diubah.' : null),
            Toggle::make('is_active')->label('Aktif (boleh login)')->default(true)
                ->disabled(fn (?User $record) => $record?->is(auth()->user())),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->weight('semibold')->description(fn (User $r) => $r->email),
                TextColumn::make('role')->label('Peran')->badge()->formatStateUsing(fn ($state) => User::ROLES[$state] ?? $state)
                    ->color(fn ($state) => $state === User::ROLE_SUPER_ADMIN ? 'primary' : 'gray'),
                ToggleColumn::make('is_active')->label('Aktif')->disabled(fn (User $r) => $r->is(auth()->user())),
                TextColumn::make('created_at')->label('Dibuat')->date('d M Y'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->hidden(fn (User $r) => $r->is(auth()->user())),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageUsers::route('/')];
    }
}
