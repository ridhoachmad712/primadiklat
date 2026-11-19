<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Models\User;
use App\Support\BrandPalette;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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
        $this->form->fill(array_merge(Setting::all_cached(), [
            'contacts' => Setting::json('contacts'),
            'home_sections' => collect(Setting::homeSections())->map(fn ($s) => collect($s)->except('label')->all())->all(),
        ]));
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
                        FileUpload::make('og_image')->label('Gambar saat dibagikan (opsional)')->image()->disk('public')->directory('settings')
                            ->maxSize(2048)->imageEditor()->imageEditorAspectRatios(['1.91:1'])
                            ->helperText('Tampil saat tautan dibagikan ke WhatsApp/Facebook untuk halaman tanpa gambar sendiri. Ukuran ideal 1200×630 px. Jika kosong, dibuat otomatis dari nama situs dan warna utama.'),
                    ]),
                    Tab::make('Beranda')->icon(Heroicon::OutlinedHome)->schema([
                        Section::make('Bagian atas (hero)')->description('Selalu tampil paling atas, berisi pencarian tema dan jadwal terdekat.')->schema([
                            TextInput::make('hero_title')->label('Judul'),
                            Textarea::make('hero_subtitle')->label('Sub judul')->rows(2),
                            FileUpload::make('hero_image')->label('Foto (opsional)')->image()->disk('public')->directory('settings')
                                ->maxSize(3072)->imageEditor()
                                ->helperText('Sebaiknya foto kegiatan asli, mendatar (mis. 1600×1000 px). Tampil di atas kartu jadwal terdekat.'),
                        ]),
                        Section::make('Warna')->schema([
                            Radio::make('brand_color')->label('Warna utama situs')->options(BrandPalette::options())->inline()->default(BrandPalette::DEFAULT)
                                ->helperText('Semua pilihan sudah memenuhi standar kontras teks (WCAG AA).'),
                        ]),
                        Section::make('Urutan & isi bagian')->description('Seret untuk mengubah urutan. Matikan bagian yang tidak ingin ditampilkan.')->schema([
                            Repeater::make('home_sections')->hiddenLabel()
                                ->schema([
                                    Hidden::make('key'),
                                    Grid::make(4)->schema([
                                        Toggle::make('enabled')->label('Tampilkan')->inline(false),
                                        TextInput::make('eyebrow')->label('Label kecil')->placeholder('Kosongkan untuk menyembunyikan'),
                                        TextInput::make('title')->label('Judul')->required()->columnSpan(2),
                                    ]),
                                    Grid::make(4)->schema([
                                        Textarea::make('intro')->label('Teks pengantar')->rows(2)->columnSpan(3),
                                        TextInput::make('limit')->label('Jumlah ditampilkan')->numeric()->minValue(1)
                                            ->maxValue(fn (Get $get) => Setting::HOME_SECTIONS[$get('key')]['max'] ?? null)
                                            ->visible(fn (Get $get) => (Setting::HOME_SECTIONS[$get('key')]['limit'] ?? null) !== null),
                                    ]),
                                ])
                                ->addable(false)->deletable(false)->reorderable()->reorderableWithButtons()->collapsible()
                                ->itemLabel(fn (array $state) => (Setting::HOME_SECTIONS[$state['key'] ?? '']['label'] ?? 'Bagian').(($state['enabled'] ?? true) ? '' : ' (disembunyikan)')),
                        ]),
                        Section::make('Angka di bagian Legalitas & Kepercayaan')->description('Isi hanya dengan data yang benar. Angka yang dikosongkan tidak ditampilkan.')->schema([
                            Grid::make(2)->schema([
                                TextInput::make('stat_alumni')->label('Jumlah alumni')->numeric()->minValue(0),
                                TextInput::make('stat_years')->label('Tahun pengalaman')->numeric()->minValue(0),
                            ]),
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
                        Repeater::make('contacts')->label('Narahubung (tampil di beranda)')
                            ->helperText('Admin yang bisa dihubungi langsung. Kosongkan jika tidak ingin ditampilkan.')
                            ->schema([
                                TextInput::make('name')->label('Nama')->required(),
                                TextInput::make('role')->label('Peran')->placeholder('Admin Pendaftaran'),
                                TextInput::make('whatsapp')->label('WhatsApp')->required()->placeholder('6281234567890')->regex('/^62\d{8,13}$/'),
                            ])
                            ->columns(3)->maxItems(4)->defaultItems(0)->reorderable()
                            ->addActionLabel('Tambah narahubung')
                            ->itemLabel(fn (array $state) => $state['name'] ?? null),
                        Textarea::make('maps_embed')->label('URL embed Google Maps')->rows(2)->helperText('Ambil dari Google Maps → Bagikan → Sematkan peta → salin isi src.'),
                    ]),
                    Tab::make('Legalitas')->icon(Heroicon::OutlinedShieldCheck)->schema([
                        Grid::make(2)->schema([
                            TextInput::make('legal_name')->label('Nama badan hukum')->placeholder('PT Prima Diklat Indonesia'),
                            TextInput::make('legal_deed')->label('Akta pendirian / SK Kemenkumham')->placeholder('AHU-0000000.AH.01.01 Tahun 2020'),
                            TextInput::make('legal_nib')->label('NIB'),
                            TextInput::make('legal_npwp')->label('NPWP'),
                        ]),
                        Textarea::make('legal_other')->label('Izin / akreditasi lain')->rows(3)
                            ->helperText('Satu per baris, format "Label: nilai". Contoh: Akreditasi LKPP: No. 123/2025'),
                        FileUpload::make('legal_document')->label('Dokumen legalitas (PDF, opsional)')
                            ->disk('public')->directory('settings')->acceptedFileTypes(['application/pdf'])->maxSize(5120)
                            ->helperText('Bisa diunduh calon peserta untuk kelengkapan SPJ.'),
                        \Filament\Forms\Components\Placeholder::make('legal_hint')->hiddenLabel()
                            ->content('Bagian legalitas di beranda hanya tampil jika minimal satu isian diisi.'),
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
                        Grid::make(2)->schema([
                            TextInput::make('schedule_close_days')->label('Pendaftaran ditutup H-berapa')->numeric()->minValue(0)->suffix('hari sebelum kegiatan')
                                ->helperText('Berlaku untuk jadwal tanggal (Jadwal & Tema).'),
                            Textarea::make('price_notice')->label('Keterangan biaya di form pendaftaran')->rows(2),
                        ]),
                        Textarea::make('payment_instructions')->label('Petunjuk pembayaran')->rows(2)->helperText('Tampil di halaman status pendaftaran, di bawah info rekening.'),
                    ]),
                    Tab::make('Sertifikat')->icon(Heroicon::OutlinedAcademicCap)->schema([
                        Grid::make(2)->schema([
                            TextInput::make('certificate_number_format')->label('Format nomor sertifikat')->required()
                                ->helperText('Kode: {no} nomor urut per tahun, {bulan} bulan romawi, {tahun}, {kegiatan} ID kegiatan.'),
                            Toggle::make('certificate_require_paid')->label('Wajib lunas untuk unduh sertifikat')->inline(false)
                                ->formatStateUsing(fn ($state) => (bool) (int) $state)
                                ->dehydrateStateUsing(fn ($state) => $state ? '1' : '0'),
                            TextInput::make('certificate_signatory')->label('Jabatan penanda tangan'),
                            TextInput::make('certificate_signatory_name')->label('Nama penanda tangan'),
                        ]),
                        TextInput::make('certificate_text')->label('Kalimat pengantar')->helperText('Tampil di atas nama kegiatan.'),
                        FileUpload::make('certificate_background')->label('Latar sertifikat (opsional)')->image()->disk('public')->directory('settings')
                            ->maxSize(4096)
                            ->helperText('Gambar A4 landscape (3508×2480 px). Jika kosong, dipakai bingkai bawaan. Biarkan area tengah kosong untuk teks.'),
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
        $state = $this->form->getState();
        // Repeaters key items by UUID; store plain lists.
        foreach (['contacts', 'home_sections'] as $key) {
            if (isset($state[$key]) && is_array($state[$key])) {
                $state[$key] = array_values($state[$key]);
            }
        }
        Setting::setMany($state);

        Notification::make()->title('Pengaturan disimpan')->success()->send();
    }
}
