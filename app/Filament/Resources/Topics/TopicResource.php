<?php

namespace App\Filament\Resources\Topics;

use App\Filament\Concerns\RestrictedByRole;
use App\Models\Topic;
use App\Models\Training;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class TopicResource extends Resource
{
    use RestrictedByRole;

    protected static ?string $model = Topic::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Jadwal & Tema';

    protected static ?string $navigationLabel = 'Tema Bimtek';

    protected static ?string $modelLabel = 'tema';

    protected static ?string $pluralModelLabel = 'Tema Bimtek';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('title')->label('Judul tema')->required()->maxLength(255)->columnSpanFull(),
            Select::make('category_id')->label('Kategori')->relationship('category', 'name')->searchable()->preload(),
            Select::make('type')->label('Jenis')->options(Training::TYPES)->default('bimtek')->required()->native(false),
            Textarea::make('excerpt')->label('Ringkasan')->rows(2)->columnSpanFull(),
            RichEditor::make('description')->label('Deskripsi')->columnSpanFull(),
            RichEditor::make('materials')->label('Materi pokok')->columnSpanFull(),
            Textarea::make('target_participants')->label('Sasaran peserta')->rows(2)->columnSpanFull(),
            FileUpload::make('thumbnail')->label('Gambar')->image()->disk('public')->directory('topics')->maxSize(2048)->columnSpanFull(),
            Grid::make(3)->columnSpanFull()->schema([
                Toggle::make('is_active')->label('Tampil di website')->default(true),
                Toggle::make('is_featured')->label('Tema unggulan'),
                TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('category')->withCount('trainings'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('thumbnail')->label('')->disk('public')->square()->size(40),
                TextColumn::make('title')->label('Tema')->searchable()->weight('semibold')->wrap()
                    ->description(fn (Topic $record) => $record->category?->name),
                TextColumn::make('type')->label('Jenis')->badge()->formatStateUsing(fn ($state) => Training::TYPES[$state] ?? $state),
                TextColumn::make('trainings_count')->label('Kelas terbentuk')->alignCenter(),
                ToggleColumn::make('is_featured')->label('Unggulan'),
                ToggleColumn::make('is_active')->label('Aktif'),
            ])
            ->filters([
                SelectFilter::make('category_id')->label('Kategori')->relationship('category', 'name'),
            ])
            ->recordActions([
                ActionGroup::make([EditAction::make(), DeleteAction::make()]),
            ])
            ->emptyStateHeading('Belum ada tema')
            ->emptyStateDescription('Tambahkan daftar tema bimtek yang dapat dipilih peserta.');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageTopics::route('/')];
    }
}
