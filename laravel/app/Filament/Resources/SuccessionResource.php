<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuccessionResource\Pages;
use App\Models\Succession;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;


class SuccessionResource extends Resource
{
    protected static ?string $model = Succession::class;
    protected static ?string $navigationGroup = 'People';
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('from_id')
                    ->label('Original User')
                    ->relationship('fromUser', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('to_id')
                    ->label('Successor')
                    ->relationship('toUser', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Leave blank if no successor is assigned.'),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'meninggal' => 'Meninggal',
                        'cerai' => 'Cerai',
                        'pindah' => 'Pindah',
                        'lainnya' => 'Lainnya',
                    ])
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fromUser.name')
                    ->label('Original User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('toUser.name')
                    ->label('Successor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fromUser.status')
                    ->label('Status')
                    ->sortable()
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
            'index' => Pages\ListSuccessions::route('/'),
            'create' => Pages\CreateSuccession::route('/create'),
            'edit' => Pages\EditSuccession::route('/{record}/edit'),
        ];
    }
}
