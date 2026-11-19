<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Forms\SeoFields;
use Filament\Schemas\Components\Section;
use App\Filament\Concerns\RestrictedByRole;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class PageResource extends Resource
{
    use RestrictedByRole;

    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static string|UnitEnum|null $navigationGroup = 'Konten Website';

    protected static ?string $navigationLabel = 'Halaman Statis';

    protected static ?string $modelLabel = 'halaman';

    protected static ?string $pluralModelLabel = 'Halaman Statis';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('title')->label('Judul')->required(),
            TextInput::make('slug')->prefix(url('/halaman').'/')->helperText('Kosongkan untuk otomatis.')->unique(ignoreRecord: true),
            RichEditor::make('content')->label('Isi')->columnSpanFull()
                ->fileAttachmentsDisk('public')->fileAttachmentsDirectory('pages'),
            Toggle::make('is_published')->label('Terbit')->default(true),
            Section::make('SEO')->description('Cara halaman tampil di Google dan saat dibagikan.')->collapsible()->columnSpanFull()
                ->schema(SeoFields::make('pages.show', 'content', null)),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Judul')->searchable()->weight('semibold')->description(fn (Page $r) => '/halaman/'.$r->slug),
                TextColumn::make('updated_at')->label('Diperbarui')->since(),
                ToggleColumn::make('is_published')->label('Terbit'),
            ])
            ->recordActions([
                Action::make('open')->label('Lihat')->icon(Heroicon::OutlinedArrowTopRightOnSquare)->color('gray')
                    ->url(fn (Page $r) => route('pages.show', $r), shouldOpenInNewTab: true),
                EditAction::make()->slideOver()->modalWidth('4xl'),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePages::route('/')];
    }
}
