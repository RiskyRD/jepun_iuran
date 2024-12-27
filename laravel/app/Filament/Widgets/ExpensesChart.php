<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use App\Models\Expense;

class ExpensesChart extends ChartWidget
{
    protected static ?string $heading = 'Expenses';
    protected static string $color = 'danger';
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'pengurus']);
    }

    protected function getData(): array
    {
        $data = Trend::model(Expense::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->sum('nominal');
    
        return [
            'datasets' => [
                [
                    'label' => 'Pengeluaran',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
