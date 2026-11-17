<?php

namespace App\Filament\Resources\ScheduleDates;

use App\Filament\Concerns\RestrictedByRole;
use App\Models\ScheduleDate;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ScheduleDateResource extends Resource
{
    use RestrictedByRole;

    protected static ?string $model = ScheduleDate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Jadwal & Tema';

    protected static ?string $navigationLabel = 'Jadwal Tanggal';

    protected static ?string $modelLabel = 'jadwal';

    protected static ?string $pluralModelLabel = 'Jadwal Tanggal';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            DatePicker::make('start_date')->label('Tanggal mulai')->required()->native(false)->displayFormat('d M Y')
                ->unique(ignoreRecord: true)
                ->live()
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    if ($state && ! $get('end_date')) {
                        $set('end_date', \Carbon\Carbon::parse($state)->addDay()->toDateString());
                    }
                }),
            DatePicker::make('end_date')->label('Tanggal selesai')->required()->native(false)->displayFormat('d M Y')->afterOrEqual('start_date'),
            TextInput::make('note')->label('Catatan (opsional)')->placeholder('mis. Libur nasional, kuota terbatas')->columnSpanFull(),
            Toggle::make('is_active')->label('Tampil & bisa dipilih')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('trainings'))
            ->defaultSort('start_date')
            ->columns([
                TextColumn::make('start_date')->label('Tanggal')->sortable()
                    ->formatStateUsing(fn (ScheduleDate $record) => $record->label)
                    ->description(fn (ScheduleDate $record) => $record->days_label),
                TextColumn::make('deadline')->label('Tutup pendaftaran')->date('d M Y')
                    ->color(fn (ScheduleDate $record) => $record->is_open ? null : 'gray'),
                TextColumn::make('trainings_count')->label('Kelas terbentuk')->alignCenter(),
                TextColumn::make('note')->label('Catatan')->toggleable()->placeholder('-'),
                ToggleColumn::make('is_active')->label('Aktif'),
            ])
            ->filters([
                SelectFilter::make('year')->label('Tahun')
                    ->options(fn () => ScheduleDate::query()->pluck('start_date')->map(fn ($d) => $d->year)->unique()->sort()->mapWithKeys(fn ($y) => [$y => $y])->all())
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'] ?? null, fn ($q, $y) => $q->whereYear('start_date', $y))),
            ])
            ->recordActions([
                ActionGroup::make([EditAction::make(), DeleteAction::make()]),
            ])
            ->paginated([25, 50, 100]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageScheduleDates::route('/')];
    }
}
