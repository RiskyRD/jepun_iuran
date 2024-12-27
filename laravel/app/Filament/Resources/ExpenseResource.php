<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables\Columns\TextColumn;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;
    protected static ?string $navigationGroup = 'Payments';
    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static function getMonthOptions(): array
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = \Carbon\Carbon::create()->month($i)->format('M');
        }
        return $months;
    }

    public static function generatePaymentOptionsExpense($category, $year)
    {
        $options = [];
        
        // Fetch all expense dates for the given category and year
        $expenses = Expense::where('category', $category)
            ->whereYear('expense_date', $year)
            ->pluck('expense_date')
            ->map(function ($date) {
                return \Carbon\Carbon::parse($date)->format('Y-m');
            })
            ->toArray();

        // Iterate through each month of the year
        for ($i = 1; $i <= 12; $i++) {
            $monthName = \Carbon\Carbon::create()->month($i)->format('M');
            $monthKey = sprintf('%s-%02d', $year, $i); // e.g., "2024-01"

            $options[$i] = [
                'label' => $monthName,
                'disabled' => in_array($monthKey, $expenses),
                'checked' => in_array($monthKey, $expenses),
            ];
        }

        return $options;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category')
                    ->options([
                        'sampah' => 'Sampah',
                        'perbaikan_jalan' => 'Perbaikan Jalan',
                        'perbaikan_lampu' => 'Perbaikan Lampu',
                        'suka_duka' => 'Suka Duka',
                        'lainnya' => 'Lainnya',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('nominal')->numeric(),
                Forms\Components\DatePicker::make('expense_date')
                    ->default(now()->toDateString()),
                Forms\Components\Textarea::make('description'),
                Forms\Components\TextInput::make('amount')
                    ->label('Number of Records')
                    ->numeric()
                    ->default(1)
                    ->required()
                    ->visible(fn (string $context) => $context === 'create'),
                Forms\Components\Fieldset::make('Payments')
                        ->schema([
                            Forms\Components\Select::make('year')
                                ->options([
                                    2024 => "2024",
                                    2025 => "2025",
                                    2026 => "2026"
                                ])
                                ->default(now()->year)
                                ->reactive() 
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) { 
                                    $sampahOptions = static::generatePaymentOptionsExpense('sampah', $state);
                                    $set('sampahPayments', array_keys(array_filter($sampahOptions, fn ($option) => $option['checked'])));
                                }),
                            Forms\Components\CheckboxList::make('sampahPayments')
                                ->options(static::getMonthOptions())
                                ->columns(4)
                                ->disabled()
                                ->default(function (callable $get) {
                                    $year = $get('year') ?? now()->year;
                                    $sampahOptions = ExpenseResource::generatePaymentOptionsExpense('sampah', $year);
                                    return array_keys(array_filter($sampahOptions, fn ($option) => $option['checked']));
                                }),
                        ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category'),
                TextColumn::make('method'),
                TextColumn::make('nominal')
                    ->formatStateUsing(fn (string $state): string => number_format($state, 0, ',', '.')),
                TextColumn::make('expense_date')
                    ->label('Date')
                    ->sortable(),
                TextColumn::make('description'),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'sampah' => 'Sampah',
                        'perbaikan_jalan' => 'Perbaikan Jalan',
                        'perbaikan_lampu' => 'Perbaikan Lampu',
                        'suka_duka' => 'Suka Duka',
                        'lainnya' => 'Lainnya',
                    ]),
                Tables\Filters\Filter::make('expense_date')
                ->form([
                    Forms\Components\DatePicker::make('expense_date')->label('Date'),
                ])
                ->query(function ($query, $data) {
                    if ($data['expense_date']) {
                        return $query->whereDate('expense_date', $data['expense_date']);
                    }
                    return $query;
                })
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
