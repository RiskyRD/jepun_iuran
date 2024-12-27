<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BalanceResource\Pages;
use App\Filament\Resources\BalanceResource\RelationManagers;
use App\Models\Balance;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms;

class BalanceResource extends Resource
{
    protected static ?string $model = Balance::class;
    protected static ?string $navigationGroup = 'Payments';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nominal')->numeric(),
                Forms\Components\DatePicker::make('balance_date')
                    ->default(now()->toDateString()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nominal')
                    ->formatStateUsing(fn (string $state): string => number_format($state, 0, ',', '.')),
                TextColumn::make('balance_date')
                    ->date('F Y')
                    ->label('Date')
                    ->sortable(),
            ])
            ->defaultSort('balance_date', 'desc')
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
            'index' => Pages\ListBalances::route('/'),
            'create' => Pages\CreateBalance::route('/create'),
            'edit' => Pages\EditBalance::route('/{record}/edit'),
        ];
    }
}
