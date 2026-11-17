<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Concerns\RestrictedByRole;
use App\Models\Testimonial;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
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

class TestimonialResource extends Resource
{
    use RestrictedByRole;

    protected static ?string $model = Testimonial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static string|UnitEnum|null $navigationGroup = 'Konten Website';

    protected static ?string $navigationLabel = 'Testimoni';

    protected static ?string $modelLabel = 'testimoni';

    protected static ?string $pluralModelLabel = 'Testimoni';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->label('Nama')->required(),
            TextInput::make('position')->label('Jabatan'),
            TextInput::make('institution')->label('Instansi')->columnSpanFull(),
            Textarea::make('content')->label('Testimoni')->required()->rows(4)->columnSpanFull(),
            Select::make('rating')->options([5 => '★★★★★', 4 => '★★★★', 3 => '★★★'])->default(5)->native(false),
            FileUpload::make('photo')->label('Foto')->image()->avatar()->disk('public')->directory('testimonials')->maxSize(1024),
            Toggle::make('is_active')->label('Tampilkan')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                ImageColumn::make('photo')->label('')->disk('public')->circular()->defaultImageUrl(fn (Testimonial $r) => 'https://ui-avatars.com/api/?background=e0e7ff&color=1d4ed8&name='.urlencode($r->name)),
                TextColumn::make('name')->label('Nama')->searchable()->weight('semibold')->description(fn (Testimonial $r) => trim($r->position.', '.$r->institution, ', ')),
                TextColumn::make('content')->label('Testimoni')->limit(70)->wrap(),
                ToggleColumn::make('is_active')->label('Tampil'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageTestimonials::route('/')];
    }
}
