<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Concerns\RestrictedByRole;
use App\Models\Post;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class PostResource extends Resource
{
    use RestrictedByRole;

    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'Konten Website';

    protected static ?string $navigationLabel = 'Berita & Artikel';

    protected static ?string $modelLabel = 'artikel';

    protected static ?string $pluralModelLabel = 'Berita & Artikel';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->columnSpanFull()->schema([
                Section::make()->columnSpan(2)->schema([
                    TextInput::make('title')->label('Judul')->required()->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state, string $operation) {
                            if ($operation === 'create' || blank($get('slug'))) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),
                    TextInput::make('slug')->prefix(url('/berita').'/')->required()->unique(ignoreRecord: true),
                    Textarea::make('excerpt')->label('Ringkasan')->rows(2)->maxLength(300),
                    RichEditor::make('content')->label('Isi')
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory('posts/content'),
                ]),
                Group::make()->schema([
                    Section::make('Publikasi')->schema([
                        Toggle::make('is_published')->label('Terbitkan')->default(true),
                        DateTimePicker::make('published_at')->label('Tanggal terbit')->default(now())->native(false),
                        Select::make('category')->label('Kategori')->options(Post::CATEGORIES)->default('berita')->native(false),
                    ]),
                    Section::make('Gambar')->schema([
                        FileUpload::make('thumbnail')->hiddenLabel()->image()->imageEditor()->disk('public')->directory('posts')->maxSize(2048),
                    ]),
                    Section::make('SEO')->collapsed()->schema([
                        TextInput::make('meta_title')->label('Judul SEO')->maxLength(70),
                        Textarea::make('meta_description')->label('Deskripsi SEO')->rows(2)->maxLength(160),
                    ]),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('thumbnail')->label('')->disk('public')->imageWidth(64)->imageHeight(40)->extraImgAttributes(['class' => 'rounded-md object-cover']),
                TextColumn::make('title')->label('Judul')->searchable()->weight('semibold')->wrap()
                    ->description(fn (Post $r) => $r->category_label),
                TextColumn::make('published_at')->label('Terbit')->date('d M Y')->sortable(),
                TextColumn::make('views')->label('Dilihat')->numeric()->sortable(),
                ToggleColumn::make('is_published')->label('Terbit'),
            ])
            ->filters([SelectFilter::make('category')->label('Kategori')->options(Post::CATEGORIES)])
            ->recordActions([ActionGroup::make([EditAction::make(), DeleteAction::make()])])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
