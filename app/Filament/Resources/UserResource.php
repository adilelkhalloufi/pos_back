<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Enums\EnumAccountStatue;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make(User::COL_EMAIL)
                    ->label('Email'),
                TextInput::make(User::COL_NAME)
                    ->label('Nom'),
                TextInput::make(User::COL_PHONE)
                    ->label('Téléphone'),
                TextInput::make(User::COL_ROLE)
                    ->label('Rôle'),
                Select::make(User::COL_STATUE)
                    ->label('Statut')
                    ->options(EnumAccountStatue::class),
                DateTimePicker::make(User::COL_CREATED_AT)
                    ->label('Créé le')
                    ->displayFormat('d/m/Y H:i')
                    ->disabled(),
                DateTimePicker::make(User::COL_TRIAL_ENDS_AT)
                    ->label('Essai se termine le')
                    ->displayFormat('d/m/Y H:i'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
             
                TextColumn::make(User::COL_EMAIL)
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make(User::COL_NAME)
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make(User::COL_PHONE)
                    ->label('Téléphone')
                    ->searchable()
                    ->sortable(),
                TextColumn::make(User::COL_ROLE)
                    ->label('Rôle')
                    ->searchable()
                    ->sortable(),
                TextColumn::make(User::COL_STATUE)
                    ->label('Statut')
                    ->formatStateUsing(fn ($state) => EnumAccountStatue::tryFrom($state)?->getLabel() ?? $state)
                    ->searchable()
                    ->sortable(),
                TextColumn::make(User::COL_CREATED_AT)
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make(User::COL_TRIAL_ENDS_AT)
                    ->label('Essai se termine le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                 
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
