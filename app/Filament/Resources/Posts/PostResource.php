<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Forms\SeoFields;
use App\Filament\Concerns\RestrictedByRole;
use App\Models\Post;
use App\Support\SeoAnalyzer;
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
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use UnitEnum;

class PostResource extends Resource
{
    use RestrictedByRole {
        getAuthorizationResponse as roleAuthorizationResponse;
    }

    protected static ?string $model = Post::class;

    /**
     * Writers (Penulis) work only on their own unpublished drafts and cannot publish;
     * Editors and Super Admins review and publish everything.
     */
    public static function getAuthorizationResponse(string $action, ?Model $record = null): Response
    {
        if (! static::isWriter()) {
            return static::roleAuthorizationResponse($action, $record);
        }

        $ownDraft = $record instanceof Post && $record->user_id === auth()->id() && ! $record->is_published;

        return match (true) {
            in_array($action, ['viewAny', 'create'], true) => Response::allow(),
            in_array($action, ['view', 'update', 'delete'], true) && $ownDraft => Response::allow(),
            default => Response::deny('Penulis hanya dapat mengubah draf artikel miliknya yang belum terbit.'),
        };
    }

    public static function isWriter(): bool
    {
        return (bool) auth()->user()?->isWriter();
    }

    /** Writers only ever see their own articles. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->when(static::isWriter(), fn (Builder $q) => $q->where('user_id', auth()->id()));
    }

    /** Editors see how many articles are waiting to be reviewed and published. */
    public static function getNavigationBadge(): ?string
    {
        if (static::isWriter()) {
            return null;
        }
        $pending = Post::where('is_published', false)->whereHas('author', fn ($q) => $q->where('role', User::ROLE_WRITER))->count();

        return $pending ? (string) $pending : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Artikel dari Penulis yang menunggu ditinjau';
    }

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
                Group::make()->columnSpan(2)->schema([
                Section::make()->schema([
                    TextInput::make('title')->label('Judul')->required()->maxLength(255)
                        ->live(debounce: 800)
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state, string $operation) {
                            if ($operation === 'create' || blank($get('slug'))) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),
                    TextInput::make('slug')->prefix(url('/berita').'/')->required()->unique(ignoreRecord: true)->live(debounce: 800),
                    Textarea::make('excerpt')->label('Ringkasan')->rows(2)->maxLength(300)->live(debounce: 800),
                    RichEditor::make('content')->label('Isi')
                        ->live(debounce: 1200)
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory('posts/content'),
                    // Always-visible score in the screen corner, updated as the article is written.
                    SeoFields::badge('content', 'excerpt'),
                ]),
                ]),
                Group::make()->schema([
                    Section::make('Publikasi')->schema([
                        Toggle::make('is_published')->label('Terbitkan')
                            ->default(fn () => ! static::isWriter())
                            ->disabled(fn () => static::isWriter())
                            ->helperText(fn () => static::isWriter() ? 'Artikel Anda disimpan sebagai draf dan ditinjau Editor sebelum terbit.' : null),
                        DateTimePicker::make('published_at')->label('Tanggal terbit')->default(now())->native(false),
                        Select::make('category')->label('Kategori')->options(Post::CATEGORIES)->default('berita')->native(false),
                    ]),
                    Section::make('Gambar')->schema([
                        FileUpload::make('thumbnail')->hiddenLabel()->image()->imageEditor()->disk('public')->directory('posts')->maxSize(2048)->live(),
                    ]),
                ]),
            ]),
            // Full width below the editor and sidebar so it uses the whole page.
            Section::make('SEO')->id('seo-panel')->description('Kata kunci, skor SEO, dan cara artikel tampil di Google.')->collapsible()->columnSpanFull()
                ->schema(SeoFields::make('posts.show', 'content', 'excerpt', imageAlt: true, analyzer: true)),
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
                TextColumn::make('seo_score')->label('Skor SEO')->alignCenter()->badge()
                    ->state(fn (Post $r) => SeoAnalyzer::forPost($r)['score'])
                    ->color(fn (int $state) => match (SeoAnalyzer::level($state)) { 'good' => 'success', 'ok' => 'warning', default => 'danger' })
                    ->tooltip(fn (Post $r) => $r->focus_keyword ? 'Kata kunci: '.$r->focus_keyword : 'Kata kunci utama belum diisi'),
                TextColumn::make('author.name')->label('Penulis')->placeholder('-')->toggleable()
                    ->hidden(fn () => static::isWriter()),
                TextColumn::make('published_at')->label('Terbit')->date('d M Y')->sortable(),
                TextColumn::make('views')->label('Dilihat')->numeric()->sortable(),
                ToggleColumn::make('is_published')->label('Terbit')->disabled(fn () => static::isWriter()),
            ])
            ->filters([
                SelectFilter::make('category')->label('Kategori')->options(Post::CATEGORIES),
                SelectFilter::make('is_published')->label('Status')->options(['0' => 'Draf / menunggu terbit', '1' => 'Terbit']),
                SelectFilter::make('user_id')->label('Penulis')->relationship('author', 'name')->hidden(fn () => static::isWriter()),
            ])
            ->recordActions([ActionGroup::make([EditAction::make(), DeleteAction::make()])])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])->hidden(fn () => static::isWriter())]);
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
