<?php

namespace App\Filament\Resources\Trainings;

use App\Filament\Concerns\RestrictedByRole;
use App\Filament\Resources\Trainings\Pages;
use App\Filament\Resources\Trainings\RelationManagers\RegistrationsRelationManager;
use App\Models\Training;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use UnitEnum;

class TrainingResource extends Resource
{
    use RestrictedByRole;

    protected static ?string $model = Training::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|UnitEnum|null $navigationGroup = 'Kegiatan';

    protected static ?string $navigationLabel = 'Kelas Kegiatan';

    protected static ?string $modelLabel = 'kegiatan';

    protected static ?string $pluralModelLabel = 'Bimtek & Diklat';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 1;

    public static function allowedRoles(): array
    {
        return [User::ROLE_EDITOR];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Training::upcoming()->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Kegiatan akan datang';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'city', 'venue'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->columnSpanFull()->schema([
                Group::make()->columnSpan(2)->schema([
                    Tabs::make()->tabs([
                        Tab::make('Informasi Utama')->icon(Heroicon::OutlinedInformationCircle)->schema([
                            TextInput::make('title')
                                ->label('Judul Kegiatan')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Set $set, Get $get, ?string $state, string $operation) {
                                    if ($operation === 'create' || blank($get('slug'))) {
                                        $set('slug', Str::slug((string) $state));
                                    }
                                }),
                            TextInput::make('slug')
                                ->label('URL (slug)')
                                ->prefix(url('/kegiatan').'/')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            Textarea::make('excerpt')
                                ->label('Ringkasan singkat')
                                ->helperText('Tampil di kartu kegiatan dan hasil pencarian Google.')
                                ->rows(2)
                                ->maxLength(300),
                            RichEditor::make('description')
                                ->label('Deskripsi & Latar Belakang')
                                ->fileAttachmentsDisk('public')
                                ->fileAttachmentsDirectory('trainings/content'),
                        ]),
                        Tab::make('Materi & Peserta')->icon(Heroicon::OutlinedBookOpen)->schema([
                            RichEditor::make('materials')
                                ->label('Materi / Pokok Bahasan')
                                ->toolbarButtons([['bold', 'italic', 'link'], ['h3', 'bulletList', 'orderedList'], ['undo', 'redo']]),
                            Textarea::make('target_participants')
                                ->label('Sasaran Peserta')
                                ->rows(3),
                        ]),
                        Tab::make('Biaya & Fasilitas')->icon(Heroicon::OutlinedBanknotes)->schema([
                            Repeater::make('prices')
                                ->label('Paket Biaya')
                                ->addActionLabel('Tambah paket')
                                ->schema([
                                    TextInput::make('name')->label('Nama paket')->placeholder('Tanpa Penginapan')->required(),
                                    TextInput::make('price')->label('Biaya')->numeric()->prefix('Rp')->required(),
                                    TextInput::make('note')->label('Keterangan')->placeholder('2 hari kelas, tanpa hotel')->columnSpanFull(),
                                ])
                                ->columns(2)
                                ->itemLabel(fn (array $state) => $state['name'] ?? null)
                                ->collapsible()
                                ->defaultItems(0),
                            TagsInput::make('facilities')
                                ->label('Fasilitas')
                                ->placeholder('Ketik lalu Enter')
                                ->suggestions(['Modul / bahan ajar', 'Training kit', 'Sertifikat', 'Makan siang', 'Coffee break 2x', 'Penginapan hotel', 'Penjemputan bandara', 'Konsultasi pasca pelatihan']),
                        ]),
                        Tab::make('SEO')->icon(Heroicon::OutlinedMagnifyingGlass)->schema([
                            TextInput::make('meta_title')->label('Judul SEO')->maxLength(70)->helperText('Kosongkan untuk memakai judul kegiatan.'),
                            Textarea::make('meta_description')->label('Deskripsi SEO')->rows(2)->maxLength(160),
                        ]),
                    ])->persistTabInQueryString(),
                ]),

                Group::make()->schema([
                    Section::make('Publikasi')->schema([
                        ToggleButtons::make('status')
                            ->options(Training::STATUSES)
                            ->colors(['draft' => 'gray', 'published' => 'success', 'cancelled' => 'danger'])
                            ->default('draft')
                            ->inline()
                            ->required(),
                        Toggle::make('is_featured')->label('Tampilkan di beranda (unggulan)'),
                        Toggle::make('registration_open')->label('Pendaftaran online dibuka')->default(true),
                    ]),
                    Section::make('Jadwal & Tempat')->schema([
                        Select::make('type')->label('Jenis')->options(Training::TYPES)->default('bimtek')->required()->native(false),
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([TextInput::make('name')->label('Nama kategori')->required()]),
                        Grid::make(2)->schema([
                            DatePicker::make('start_date')->label('Mulai')->required()->native(false)->displayFormat('d M Y')->live(),
                            DatePicker::make('end_date')->label('Selesai')->required()->native(false)->displayFormat('d M Y')
                                ->afterOrEqual('start_date'),
                        ]),
                        DatePicker::make('registration_deadline')->label('Batas pendaftaran')->native(false)->displayFormat('d M Y'),
                        Select::make('method')->label('Metode')->options(Training::METHODS)->default('offline')->required()->native(false)->live(),
                        TextInput::make('city')->label('Kota')->hidden(fn (Get $get) => $get('method') === 'online'),
                        TextInput::make('venue')->label('Hotel / Tempat')->hidden(fn (Get $get) => $get('method') === 'online'),
                        TextInput::make('venue_address')->label('Alamat tempat')->hidden(fn (Get $get) => $get('method') === 'online'),
                        TextInput::make('quota')->label('Kuota peserta')->numeric()->minValue(1)->helperText('Kosongkan bila tidak dibatasi.'),
                    ]),
                    Section::make('Media')->schema([
                        FileUpload::make('thumbnail')
                            ->label('Gambar sampul')
                            ->image()
                            ->imageEditor()
                            ->imageAspectRatio('16:9')
                            ->disk('public')
                            ->directory('trainings')
                            ->maxSize(2048),
                        FileUpload::make('brochure')
                            ->label('Brosur / undangan (PDF)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->disk('public')
                            ->directory('brochures')
                            ->maxSize(5120)
                            ->helperText('Opsional. Bila kosong, website membuat surat undangan PDF otomatis.'),
                    ]),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('category')->withCount(['registrations' => fn ($r) => $r->where('status', '!=', 'cancelled')]))
            ->defaultSort('start_date', 'desc')
            ->columns([
                ImageColumn::make('thumbnail')->label('')->disk('public')->imageWidth(64)->imageHeight(40)->extraImgAttributes(['class' => 'rounded-md object-cover']),
                TextColumn::make('title')
                    ->label('Kegiatan')
                    ->searchable()
                    ->weight('semibold')
                    ->wrap()
                    ->description(fn (Training $r) => $r->type_label.' · '.($r->category?->name ?? 'Tanpa kategori')),
                TextColumn::make('start_date')
                    ->label('Jadwal')
                    ->sortable()
                    ->formatStateUsing(fn (Training $r) => $r->date_range)
                    ->description(fn (Training $r) => $r->location),
                TextColumn::make('registrations_count')
                    ->label('Peserta')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state, Training $r) => $r->quota ? "{$state} / {$r->quota}" : (string) $state)
                    ->badge()
                    ->color(fn ($state, Training $r) => $r->quota && $state >= $r->quota ? 'danger' : 'gray'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Training::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => ['draft' => 'gray', 'published' => 'success', 'cancelled' => 'danger'][$state] ?? 'gray'),
                IconColumn::make('is_featured')->label('Unggulan')->boolean()->trueIcon(Heroicon::Star)->falseIcon(Heroicon::OutlinedStar)->trueColor('warning')->toggleable(),
                TextColumn::make('views')->label('Dilihat')->numeric()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(Training::STATUSES),
                SelectFilter::make('type')->label('Jenis')->options(Training::TYPES),
                SelectFilter::make('category_id')->label('Kategori')->relationship('category', 'name')->searchable()->preload(),
                Filter::make('upcoming')->label('Hanya yang akan datang')->query(fn (Builder $query) => $query->whereDate('end_date', '>=', today()))->toggle(),
                TernaryFilter::make('is_featured')->label('Unggulan'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('view_site')->label('Lihat di website')->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                        ->url(fn (Training $r) => route('trainings.show', $r), shouldOpenInNewTab: true),
                    Action::make('invitation')->label('Surat undangan PDF')->icon(Heroicon::OutlinedDocumentText)
                        ->url(fn (Training $r) => route('trainings.invitation', $r), shouldOpenInNewTab: true),
                    ReplicateAction::make()->label('Duplikat')
                        ->excludeAttributes(['slug', 'views', 'registrations_count'])
                        ->beforeReplicaSaved(function (Training $replica) {
                            $replica->title .= ' (Salinan)';
                            $replica->slug = Training::uniqueSlug($replica->title);
                            $replica->status = 'draft';
                        }),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->emptyStateHeading('Belum ada kegiatan')
            ->emptyStateDescription('Tambahkan jadwal bimtek atau diklat pertama Anda.');
    }

    public static function getRelations(): array
    {
        return [RegistrationsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrainings::route('/'),
            'create' => Pages\CreateTraining::route('/create'),
            'edit' => Pages\EditTraining::route('/{record}/edit'),
        ];
    }
}
