<?php

namespace App\Filament\Resources\Registrations;

use App\Models\Registration;
use App\Models\Training;
use App\Support\RegistrationExporter;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Collection;

/** Form, columns and actions shared by the Registration resource and the Training relation manager. */
class RegistrationSchema
{
    public static function form(bool $withTraining = true): array
    {
        return [
            Section::make('Data Peserta')->columns(2)->columnSpanFull()->schema(array_filter([
                $withTraining ? Select::make('training_id')
                    ->label('Kegiatan')
                    ->relationship('training', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->columnSpanFull() : null,
                TextInput::make('name')->label('Nama lengkap')->required(),
                TextInput::make('nip')->label('NIP / NIK'),
                TextInput::make('position')->label('Jabatan'),
                TextInput::make('institution')->label('Instansi')->required(),
                TextInput::make('institution_address')->label('Alamat instansi')->columnSpanFull(),
                TextInput::make('city')->label('Kota / Kabupaten'),
                TextInput::make('email')->email()->required(),
                TextInput::make('phone')->label('No. HP / WhatsApp')->tel()->required(),
            ])),
            Section::make('Paket & Status')->columns(2)->columnSpanFull()->schema([
                Select::make('package')
                    ->label('Paket biaya')
                    ->options(function (Get $get, $livewire) {
                        $trainingId = $get('training_id') ?? ($livewire->ownerRecord->id ?? null);
                        $prices = Training::find($trainingId)?->prices ?? [];

                        return collect($prices)->mapWithKeys(fn ($p) => [$p['name'] => $p['name'].' — Rp '.number_format((int) $p['price'], 0, ',', '.')])->all();
                    })
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state, $livewire) {
                        $trainingId = $get('training_id') ?? ($livewire->ownerRecord->id ?? null);
                        $price = collect(Training::find($trainingId)?->prices ?? [])->firstWhere('name', $state);
                        $set('price', $price['price'] ?? null);
                    }),
                TextInput::make('price')->label('Biaya')->numeric()->prefix('Rp'),
                Select::make('status')->options(Registration::STATUSES)->default('pending')->required()->native(false),
                Select::make('payment_status')->label('Pembayaran')->options(Registration::PAYMENT_STATUSES)->default('unpaid')->required()->native(false),
                Textarea::make('notes')->label('Catatan')->rows(2)->columnSpanFull(),
            ]),
            Section::make('Sertifikat')->columns(2)->columnSpanFull()->collapsible()->schema([
                TextInput::make('certificate_number')->label('Nomor sertifikat')->unique(ignoreRecord: true),
                FileUpload::make('certificate_file')
                    ->label('File sertifikat (PDF/JPG)')
                    ->disk('public')
                    ->directory('certificates')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                    ->maxSize(4096),
            ]),
        ];
    }

    public static function columns(bool $withTraining = true): array
    {
        return array_values(array_filter([
            TextColumn::make('code')->label('Kode')->searchable()->copyable()->fontFamily('mono')->size('sm')->color('gray'),
            TextColumn::make('name')->label('Peserta')->searchable()->weight('semibold')
                ->description(fn (Registration $r) => trim($r->position.' · '.$r->institution, ' ·')),
            $withTraining ? TextColumn::make('training.title')->label('Kegiatan')->limit(40)->tooltip(fn (Registration $r) => $r->training?->title)->searchable() : null,
            TextColumn::make('phone')->label('Kontak')->description(fn (Registration $r) => $r->email)->searchable(['phone', 'email']),
            TextColumn::make('package')->label('Paket')->toggleable()
                ->description(fn (Registration $r) => $r->price ? 'Rp '.number_format($r->price, 0, ',', '.') : null),
            TextColumn::make('status')->badge()
                ->formatStateUsing(fn ($state) => Registration::STATUSES[$state] ?? $state)
                ->color(fn ($state) => ['pending' => 'warning', 'confirmed' => 'info', 'attended' => 'success', 'cancelled' => 'danger'][$state] ?? 'gray'),
            TextColumn::make('payment_status')->label('Bayar')->badge()
                ->formatStateUsing(fn ($state) => Registration::PAYMENT_STATUSES[$state] ?? $state)
                ->color(fn ($state) => ['unpaid' => 'gray', 'paid' => 'success', 'refunded' => 'danger'][$state] ?? 'gray'),
            TextColumn::make('created_at')->label('Daftar')->since()->sortable()->dateTimeTooltip('d M Y H:i'),
        ]));
    }

    public static function filters(): array
    {
        return [
            SelectFilter::make('status')->options(Registration::STATUSES),
            SelectFilter::make('payment_status')->label('Pembayaran')->options(Registration::PAYMENT_STATUSES),
        ];
    }

    public static function whatsappAction(): Action
    {
        return Action::make('whatsapp')
            ->label('WhatsApp')
            ->icon(Heroicon::OutlinedChatBubbleLeftRight)
            ->color('success')
            ->url(fn (Registration $r) => 'https://wa.me/'.$r->whatsapp_number.'?text='.rawurlencode(
                "Halo Bapak/Ibu {$r->name},\n\nTerima kasih telah mendaftar \"{$r->training?->title}\" ({$r->training?->date_range}).\nKode pendaftaran: {$r->code}\n\nSalam,\nPrima Diklat"
            ), shouldOpenInNewTab: true);
    }

    public static function bulkActions(): array
    {
        return [
            BulkActionGroup::make([
                BulkAction::make('set_status')
                    ->label('Ubah status')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->schema([
                        Select::make('status')->options(Registration::STATUSES)->required(),
                    ])
                    ->action(fn (Collection $records, array $data) => $records->each->update(['status' => $data['status']]))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('mark_paid')
                    ->label('Tandai lunas')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->action(fn (Collection $records) => $records->each->update(['payment_status' => 'paid']))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('export')
                    ->label('Ekspor terpilih (Excel)')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->action(fn (Collection $records) => RegistrationExporter::download($records)),
                DeleteBulkAction::make(),
            ]),
        ];
    }

    public static function grid(): Grid
    {
        return Grid::make(1);
    }
}
