<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = '使用者';

    protected static ?string $modelLabel = '使用者';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('名字')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('名字')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\CheckboxColumn::make('is_admin')
                    ->label('管理員')
                    ->visible(auth()->user()->is_admin)
                    ->disabled(fn($record) => $record->is(auth()->user()))
                    ->afterStateUpdated(function ($record, $state) {
                        $record->is_admin = $state;
                        $record->save();
                        Notification::make()
                            ->title('Success')
                            ->success()
                            ->send();
                    }),
                Tables\Columns\CheckboxColumn::make('is_blocked')
                    ->label('停權')
                    ->visible(auth()->user()->is_admin)
                    ->disabled(fn($record) => $record->is_admin)
                    ->afterStateUpdated(function ($record, $state) {
                        $record->is_blocked = $state;
                        $record->save();
                        Notification::make()
                            ->title('Success')
                            ->success()
                            ->send();
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('建立時間')
                    ->dateTime('Y-m-d h:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新時間')
                    ->dateTime('Y-m-d h:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
//                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
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
            'index' => Pages\ListUsers::route('/'),
            //            'create' => Pages\CreateUser::route('/create'),
            //            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->is_admin;
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
