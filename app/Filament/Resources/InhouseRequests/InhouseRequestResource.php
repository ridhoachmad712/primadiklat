<?php

namespace App\Filament\Resources\InhouseRequests;

use App\Filament\Concerns\RestrictedByRole;
use App\Models\InhouseRequest;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class InhouseRequestResource extends Resource
{
    use RestrictedByRole;

    protected static ?string $model = InhouseRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Pesan Masuk';

    protected static ?string $navigationLabel = 'Permintaan In-House';

    protected static ?string $modelLabel = 'permintaan in-house';

    protected static ?string $pluralModelLabel = 'Permintaan In-House';

    protected static ?int $navigationSort = 1;

    public static function allowedRoles(): array
    {
        return [User::ROLE_REGISTRAR];
    }

    public static function getNavigationBadge(): ?string
    {
        $c = InhouseRequest::where('status', 'new')->count();

        return $c ? (string) $c : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('institution')->label('Instansi')->required()->columnSpanFull(),
            TextInput::make('contact_name')->label('Nama kontak')->required(),
            TextInput::make('position')->label('Jabatan'),
            TextInput::make('email')->email()->required(),
            TextInput::make('phone')->label('No. HP / WA')->required(),
            TextInput::make('topic')->label('Topik pelatihan')->required()->columnSpanFull(),
            TextInput::make('participants')->label('Jumlah peserta')->numeric(),
            DatePicker::make('preferred_date')->label('Rencana tanggal')->native(false),
            TextInput::make('location')->label('Lokasi')->columnSpanFull(),
            Textarea::make('message')->label('Pesan')->rows(3)->columnSpanFull(),
            Select::make('status')->options(InhouseRequest::STATUSES)->default('new')->required()->native(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('institution')->label('Instansi')->searchable()->weight('semibold')->description(fn (InhouseRequest $r) => $r->contact_name.' · '.$r->phone),
                TextColumn::make('topic')->label('Topik')->limit(40)->searchable(),
                TextColumn::make('participants')->label('Peserta')->alignCenter(),
                TextColumn::make('preferred_date')->label('Rencana')->date('d M Y'),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => InhouseRequest::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => ['new' => 'warning', 'contacted' => 'info', 'deal' => 'success', 'closed' => 'gray'][$state] ?? 'gray'),
                TextColumn::make('created_at')->label('Masuk')->since(),
            ])
            ->filters([SelectFilter::make('status')->options(InhouseRequest::STATUSES)])
            ->recordActions([
                Action::make('wa')->label('WA')->icon(Heroicon::OutlinedChatBubbleLeftRight)->color('success')
                    ->url(fn (InhouseRequest $r) => 'https://wa.me/'.preg_replace(['/\D/', '/^0/'], ['', '62'], $r->phone), shouldOpenInNewTab: true),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageInhouseRequests::route('/')];
    }
}
