<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\IncomeResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestUserIncomes extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['warga', 'pengurus']);
    }
    protected function getTableHeading(): string
    {
        return 'Latest User Payments';
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(
                IncomeResource::getEloquentQuery()
                ->where('user_id', auth()->user()->id)
            )
            ->defaultPaginationPageOption(5)
            ->defaultSort('income_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\TextColumn::make('category'),
                Tables\Columns\TextColumn::make('nominal'),
                Tables\Columns\TextColumn::make('income_date'),
                Tables\Columns\TextColumn::make('description'),

            ]);
    }
}
