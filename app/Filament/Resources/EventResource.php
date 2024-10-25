<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                ->disabled(auth()->user()->is_blocked)
                ->relationship('room', 'name')
                ->required()
                ->native(false)
                ->searchable(),
            Forms\Components\TextInput::make('name')
                ->disabled(auth()->user()->is_blocked),
            Forms\Components\Select::make('book_by')
                ->disabled(auth()->user()->is_blocked)
                ->relationship('bookBy', 'name')
                ->default(auth()->id())
                ->required()
                ->native(false)
                ->searchable(),
            Forms\Components\DateTimePicker::make('from')
                ->disabled(auth()->user()->is_blocked)
                ->required()
                ->default(now()),
            Forms\Components\DateTimePicker::make('to')
                ->disabled(auth()->user()->is_blocked)
                ->required()
                ->default(now()),
            Forms\Components\TextInput::make('expected_participants')
                ->disabled(auth()->user()->is_blocked)
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
                Tables\Columns\TextColumn::make('room.name'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('bookBy.name'),
                Tables\Columns\TextColumn::make('from')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('to')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expected_participants')
                    ->numeric(),
            ])
            ->filters([
                //
            ])
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
}
