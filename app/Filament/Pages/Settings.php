<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class Settings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Pengaturan Situs';

    protected static ?string $title = 'Pengaturan Situs';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill(Setting::all_cached());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make()->persistTabInQueryString()->tabs([
                    Tab::make('Identitas')->icon(Heroicon::OutlinedIdentification)->schema([
                        Grid::make(2)->schema([
                            TextInput::make('site_name')->label('Nama situs')->required(),
                            TextInput::make('site_tagline')->label('Tagline'),
                        ]),
                        Textarea::make('site_description')->label('Deskripsi situs (SEO)')->rows(2)->maxLength(200),
                        Textarea::make('about_short')->label('Profil singkat (footer)')->rows(2),
                        Grid::make(2)->schema([
                            FileUpload::make('logo')->label('Logo')->image()->disk('public')->directory('settings')->maxSize(1024),
                            FileUpload::make('favicon')->label('Favicon')->image()->disk('public')->directory('settings')->maxSize(256),
                        ]),
                    ]),
                    Tab::make('Beranda')->icon(Heroicon::OutlinedHome)->schema([
                        TextInput::make('hero_title')->label('Judul hero'),
                        Textarea::make('hero_subtitle')->label('Sub judul hero')->rows(2),
                        Grid::make(4)->schema([
                            TextInput::make('stat_alumni')->label('Jumlah alumni')->numeric(),
                            TextInput::make('stat_trainings')->label('Jumlah kegiatan')->numeric()->helperText('Kosong = hitung otomatis'),
                            TextInput::make('stat_partners')->label('Instansi mitra')->numeric()->helperText('Kosong = hitung otomatis'),
                            TextInput::make('stat_years')->label('Tahun pengalaman')->numeric(),
                        ]),
                    ]),
                    Tab::make('Kontak')->icon(Heroicon::OutlinedPhone)->schema([
                        Grid::make(2)->schema([
                            TextInput::make('phone')->label('Telepon'),
                            TextInput::make('email')->email(),
                            TextInput::make('whatsapp')->label('Nomor WhatsApp')->helperText('Format internasional, contoh 6281234567890')->regex('/^62\d{8,13}$/'),
                            TextInput::make('office_hours')->label('Jam layanan'),
                        ]),
                        Textarea::make('whatsapp_message')->label('Pesan awal WhatsApp')->rows(2),
                        Textarea::make('address')->label('Alamat kantor')->rows(2),
                        Textarea::make('maps_embed')->label('URL embed Google Maps')->rows(2)->helperText('Ambil dari Google Maps → Bagikan → Sematkan peta → salin isi src.'),
                    ]),
                    Tab::make('Media Sosial')->icon(Heroicon::OutlinedShare)->schema([
                        Grid::make(2)->schema([
                            TextInput::make('instagram')->url()->prefixIcon(Heroicon::OutlinedCamera),
                            TextInput::make('facebook')->url(),
                            TextInput::make('youtube')->url()->prefixIcon(Heroicon::OutlinedPlayCircle),
                            TextInput::make('linkedin')->url(),
                        ]),
                    ]),
                    Tab::make('Pendaftaran & Undangan')->icon(Heroicon::OutlinedEnvelopeOpen)->schema([
                        TextInput::make('notify_email')->label('Email notifikasi pendaftaran')->email()->helperText('Setiap pendaftaran baru dikirim ke alamat ini.'),
                        Textarea::make('bank_info')->label('Info rekening pembayaran')->rows(3)->helperText('Tampil di email konfirmasi dan surat undangan.'),
                        Grid::make(2)->schema([
                            TextInput::make('invitation_signatory')->label('Jabatan penanda tangan undangan'),
                            TextInput::make('invitation_signatory_name')->label('Nama penanda tangan'),
                        ]),
                    ]),
                ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Simpan pengaturan')->submit('save')->keyBindings(['mod+s']),
                    ])->sticky(),
                ]),
        ]);
    }

    public function save(): void
    {
        Setting::setMany($this->form->getState());

        Notification::make()->title('Pengaturan disimpan')->success()->send();
    }
}
