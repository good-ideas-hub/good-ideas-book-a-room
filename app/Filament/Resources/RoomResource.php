<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Table;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationLabel = '會議室';

    protected static ?string $modelLabel = '會議室';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('名稱')
                    ->required(),
                ColorPicker::make('color')
                    ->label('顏色')
                    ->hsl()
                    ->regex('/^hsl\(\s*(\d+)\s*,\s*(\d*(?:\.\d+)?%)\s*,\s*(\d*(?:\.\d+)?%)\)$/'),
                Forms\Components\Checkbox::make('is_available')
                    ->label('是否開放預約'),
                Forms\Components\Textarea::make('description')
                    ->label('備注'),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('名稱'),
                ColorColumn::make('color')
                    ->label('顏色'),
                Tables\Columns\IconColumn::make('is_available')
                    ->label('是否開放預約')
                    ->boolean(),
                Tables\Columns\TextColumn::make('description')
                    ->label('備注'),
            ])
            ->recordUrl(fn ($record) => auth()->user()->is_admin ? RoomResource::getUrl('edit', ['record' => $record]) : null)
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(auth()->user()->is_admin),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])
                ->visible(auth()->user()->is_admin),
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
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->is_admin;
    }

    public static function canView($record): bool
    {
        return auth()->user()->is_admin;
    }
}
