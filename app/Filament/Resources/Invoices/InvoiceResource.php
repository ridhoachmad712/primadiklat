<?php

namespace App\Filament\Resources\Invoices;

use App\Filament\Concerns\RestrictedByRole;
use App\Models\Invoice;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class InvoiceResource extends Resource
{
    use RestrictedByRole;

    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Kegiatan';

    protected static ?string $navigationLabel = 'Tagihan & Pembayaran';

    protected static ?string $modelLabel = 'tagihan';

    protected static ?string $pluralModelLabel = 'Tagihan';

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?int $navigationSort = 3;

    public static function allowedRoles(): array
    {
        return [User::ROLE_REGISTRAR];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Invoice::where('status', 'waiting')->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Bukti bayar menunggu verifikasi';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'receipt_number', 'institution', 'contact_name'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('institution')->label('Instansi')->required()->columnSpanFull(),
            TextInput::make('contact_name')->label('Nama kontak')->required(),
            TextInput::make('contact_phone')->label('HP / WA')->required(),
            TextInput::make('contact_email')->label('Email')->email()->required(),
            Select::make('status')->options(Invoice::STATUSES)->required()->native(false)
                ->helperText('Gunakan tombol "Verifikasi lunas" agar kuitansi dan email terbit otomatis.'),
            Textarea::make('admin_note')->label('Catatan untuk pendaftar')->rows(2)->columnSpanFull()
                ->helperText('Tampil di halaman status pendaftaran.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('training')->withCount('registrations'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')->label('No. tagihan')->searchable()->copyable()->fontFamily('mono')->size('sm')
                    ->description(fn (Invoice $record) => $record->receipt_number),
                TextColumn::make('institution')->label('Instansi')->searchable(['institution', 'contact_name', 'contact_phone'])->weight('semibold')->wrap()
                    ->description(fn (Invoice $record) => $record->contact_name.' · '.$record->contact_phone),
                TextColumn::make('training.title')->label('Kegiatan')->limit(36)->tooltip(fn (Invoice $record) => $record->training?->title),
                TextColumn::make('registrations_count')->label('Peserta')->alignCenter(),
                TextColumn::make('amount')->label('Total')->money('IDR', locale: 'id')->sortable(),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => Invoice::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => Invoice::STATUS_COLORS[$state] ?? 'gray')
                    ->description(fn (Invoice $record) => $record->status === 'waiting' ? 'Bukti '.$record->proof_uploaded_at?->diffForHumans() : null),
                TextColumn::make('created_at')->label('Dibuat')->since()->sortable()->dateTimeTooltip('d M Y H:i'),
            ])
            ->filters([
                SelectFilter::make('training_id')->label('Kegiatan')->relationship('training', 'title')->searchable()->preload(),
            ])
            ->recordActions([
                Action::make('proof')
                    ->label('Bukti')
                    ->icon(Heroicon::OutlinedPhoto)
                    ->color('gray')
                    ->visible(fn (Invoice $record) => filled($record->payment_proof))
                    ->url(fn (Invoice $record) => $record->proof_url, shouldOpenInNewTab: true),
                static::verifyAction(),
                ActionGroup::make([
                    static::rejectAction(),
                    Action::make('invoice_pdf')
                        ->label('Invoice PDF')
                        ->icon(Heroicon::OutlinedDocumentText)
                        ->url(fn (Invoice $record) => route('registrations.invoice', $record->public_code), shouldOpenInNewTab: true),
                    Action::make('receipt_pdf')
                        ->label('Kuitansi PDF')
                        ->icon(Heroicon::OutlinedReceiptPercent)
                        ->visible(fn (Invoice $record) => $record->status === 'paid')
                        ->url(fn (Invoice $record) => route('registrations.receipt', $record->public_code), shouldOpenInNewTab: true),
                    Action::make('status_page')
                        ->label('Halaman peserta')
                        ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                        ->url(fn (Invoice $record) => route('registrations.show', $record->public_code), shouldOpenInNewTab: true),
                    EditAction::make(),
                    DeleteAction::make()
                        ->modalDescription('Data peserta tetap tersimpan, hanya tagihannya yang dihapus.'),
                ]),
            ])
            ->emptyStateHeading('Belum ada tagihan');
    }

    public static function verifyAction(): Action
    {
        return Action::make('verify')
            ->label('Verifikasi lunas')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->color('success')
            ->visible(fn (Invoice $record) => in_array($record->status, ['unpaid', 'waiting'], true))
            ->requiresConfirmation()
            ->modalHeading(fn (Invoice $record) => 'Verifikasi '.$record->number.'?')
            ->modalDescription(fn (Invoice $record) => 'Total '.rupiah($record->amount).' untuk '.$record->registrations()->count().' peserta. Kuitansi akan diterbitkan dan email konfirmasi dikirim ke '.$record->contact_email.'.')
            ->action(function (Invoice $record) {
                $record->markPaid(auth()->user());
            })
            ->successNotificationTitle('Pembayaran diverifikasi');
    }

    public static function rejectAction(): Action
    {
        return Action::make('reject')
            ->label('Tolak bukti bayar')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (Invoice $record) => $record->status === 'waiting')
            ->schema([
                Textarea::make('reason')->label('Alasan (tampil ke pendaftar)')->required()
                    ->default('Bukti transfer tidak terbaca atau nominal tidak sesuai. Mohon unggah ulang.'),
            ])
            ->action(fn (Invoice $record, array $data) => $record->rejectProof($data['reason']))
            ->successNotificationTitle('Bukti pembayaran ditolak');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageInvoices::route('/'),
        ];
    }
}
