<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use App\Models\Room;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationLabel = '預約';

    protected static ?string $modelLabel = '預約';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('room_id')
                ->label('會議室')
                ->relationship('room', 'name')
                ->options(Room::where('is_available', 1)->pluck('name', 'id'))
                ->required()
                ->default(request()->get('room_id'))
                ->native(false)
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('name')
                ->label('名稱'),
            Forms\Components\Select::make('book_by')
                ->label('預訂人')
                ->default(auth()->id())
                ->disabled(!auth()->user()->is_admin)
                ->relationship('bookBy', 'name')
                ->default(auth()->id())
                ->required()
                ->native(false)
                ->searchable()
                ->preload(),
            Forms\Components\DateTimePicker::make('from')
                ->label('開始時間')
                ->required()
                ->default(now()->format('Y-m-d H:i:00')),
            Forms\Components\DateTimePicker::make('to')
                ->label('結束時間')
                ->required()
                ->default(now()->format('Y-m-d H:i:00')),
            Forms\Components\TextInput::make('expected_participants')
                ->label('預計參與人數')
                ->numeric()
                ->minValue(0)
                ->required(),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::getFormSchema())
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('room.name')
                    ->label('會議室'),
                Tables\Columns\TextColumn::make('name')
                    ->label('名稱')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bookBy.name')
                    ->label('預訂人'),
                Tables\Columns\TextColumn::make('from')
                    ->label('開始時間')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('to')
                    ->label('結束時間')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expected_participants')
                    ->label('預計參與人數')
                    ->numeric(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('建立時間')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新時間')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('room_id')
                    ->label('會議室')
                    ->relationship('room', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                SelectFilter::make('book_by')
                    ->label('預訂人')
                    ->relationship('bookBy', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Filter::make('from')
                    ->form([
                        DateTimePicker::make('from')
                            ->label('開始時間')
                            ->default(today()->format("Y-m-d 00:00:00"))
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['from'],
                        fn (Builder $query, $date): Builder => $query->whereDate('from', '>=', $date),
                    )),
                Filter::make('to')
                    ->form([
                        DateTimePicker::make('to')
                            ->label('結束時間')
                            ->default(today()->format("Y-m-d 23:59:59"))
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['to'],
                        fn (Builder $query, $date): Builder => $query->whereDate('to', '<=', $date),
                    ))
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(auth()->user()->is_blocked),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])
                    ->hidden(auth()->user()->is_blocked),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }

    public static function canEdit($record): bool
    {
        if (auth()->user()->is_admin) {
            return true;
        }
        return !auth()->user()->is_blocked && $record->book_by === auth()->id();
    }
}
