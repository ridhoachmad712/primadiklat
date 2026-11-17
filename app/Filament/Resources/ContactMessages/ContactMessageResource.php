<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Concerns\RestrictedByRole;
use App\Models\ContactMessage;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ContactMessageResource extends Resource
{
    use RestrictedByRole;

    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'Pesan Masuk';

    protected static ?string $navigationLabel = 'Pesan Kontak';

    protected static ?string $modelLabel = 'pesan';

    protected static ?string $pluralModelLabel = 'Pesan Kontak';

    protected static ?int $navigationSort = 2;

    public static function allowedRoles(): array
    {
        return [User::ROLE_REGISTRAR];
    }

    public static function getNavigationBadge(): ?string
    {
        $c = ContactMessage::where('is_read', false)->count();

        return $c ? (string) $c : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextEntry::make('name')->label('Nama'),
            TextEntry::make('created_at')->label('Dikirim')->dateTime('d M Y H:i'),
            TextEntry::make('email')->copyable(),
            TextEntry::make('phone')->label('Telepon')->placeholder('-'),
            TextEntry::make('subject')->label('Subjek')->columnSpanFull(),
            TextEntry::make('message')->label('Pesan')->columnSpanFull()->prose(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('is_read')->label('')->boolean()->trueIcon(Heroicon::OutlinedEnvelopeOpen)->falseIcon(Heroicon::Envelope)->trueColor('gray')->falseColor('primary'),
                TextColumn::make('name')->label('Pengirim')->searchable()->weight(fn (ContactMessage $r) => $r->is_read ? 'normal' : 'bold')->description(fn (ContactMessage $r) => $r->email),
                TextColumn::make('subject')->label('Subjek')->limit(50)->searchable(),
                TextColumn::make('created_at')->label('Masuk')->since(),
            ])
            ->recordActions([
                ViewAction::make()->after(fn (ContactMessage $r) => $r->update(['is_read' => true]))
                    ->mountUsing(fn (ContactMessage $r) => $r->update(['is_read' => true])),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageContactMessages::route('/')];
    }
}
