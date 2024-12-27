<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GangResource\Pages;
use App\Models\Gang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class GangResource extends Resource
{
    protected static ?string $model = Gang::class;
    protected static ?string $navigationGroup = 'People';
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationLabel = 'Gang';
    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('gang_name')
                ->required(),
                Forms\Components\TextInput::make('coordinator'),
            ]);
    }

    public static function table(Table $table): Table
    {
        // dd(auth()->user()->can('viewAny', static::getModel()));

        return $table
            ->columns([
                TextColumn::make('gang_name')
                    ->label('Gang Name'),
                TextColumn::make('coordinator')
                    ->label('Coordinator'),
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
            'index' => Pages\ListGangs::route('/'),
            'create' => Pages\CreateGang::route('/create'),
            'edit' => Pages\EditGang::route('/{record}/edit'),
        ];
    }
}
