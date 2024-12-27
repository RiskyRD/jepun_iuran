<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use App\Models\Income;

class IncomesChart extends ChartWidget
{
    protected static ?string $heading = 'Incomes';
    protected static string $color = 'success';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'pengurus']);
    }

    protected function getData(): array
    {
        $data = Trend::model(Income::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->sum('nominal');
    
        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
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
