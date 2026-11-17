<?php

namespace App\Filament\Resources\Galleries;

use App\Filament\Concerns\RestrictedByRole;
use App\Models\Gallery;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class GalleryResource extends Resource
{
    use RestrictedByRole;

    protected static ?string $model = Gallery::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Konten Website';

    protected static ?string $navigationLabel = 'Galeri';

    protected static ?string $modelLabel = 'album';

    protected static ?string $pluralModelLabel = 'Galeri';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('title')->label('Judul album')->required()->columnSpanFull(),
            Select::make('training_id')->label('Terkait kegiatan')->relationship('training', 'title')->searchable()->preload(),
            DatePicker::make('event_date')->label('Tanggal')->native(false),
            Textarea::make('description')->label('Keterangan')->rows(2)->columnSpanFull(),
            FileUpload::make('images')->label('Foto')->multiple()->reorderable()->image()->panelLayout('grid')
                ->disk('public')->directory('galleries')->maxSize(3072)->maxFiles(40)->columnSpanFull(),
            Toggle::make('is_published')->label('Tampilkan')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('event_date', 'desc')
            ->columns([
                ImageColumn::make('images')->label('Foto')->disk('public')->imageHeight(40)->circular()->stacked()->limit(4)->limitedRemainingText(),
                TextColumn::make('title')->label('Album')->searchable()->weight('semibold')->description(fn (Gallery $r) => $r->training?->title),
                TextColumn::make('event_date')->label('Tanggal')->date('d M Y')->sortable(),
                ToggleColumn::make('is_published')->label('Tampil'),
            ])
            ->recordActions([EditAction::make()->modalWidth('3xl'), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageGalleries::route('/')];
    }
}
