<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Balance;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;
    protected static ?int $sort = 1;
    
    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'pengurus']);
    }

    protected function getStats(): array
    {
        $totalIncomes = Income::sum('nominal');
        $totalExpenses = Expense::sum('nominal');
    
        $totalIncomesFormatted = number_format($totalIncomes, 0, ',', '.');
        $totalExpensesFormatted = number_format($totalExpenses, 0, ',', '.');

        $latestBalanceRecord = Balance::orderBy('balance_date', 'desc')->first();

        if ($latestBalanceRecord) {
            $previousBalance = $latestBalanceRecord->nominal;
            $balanceDate = $latestBalanceRecord->balance_date;
        } else {
            $previousBalance = 0;
            $balanceDate = Carbon::now()->startOfYear(); // Default start date if no balance exists
        }

        $balanceIncomes = Income::where('income_date', '>=', $balanceDate)->sum('nominal');
        $balanceExpenses = Expense::where('expense_date', '>=', $balanceDate)->sum('nominal');

        $latestBalance = $previousBalance + $balanceIncomes - $balanceExpenses;
        $latestBalanceFormatted = number_format($latestBalance, 0, ',', '.');

        return [
            Stat::make('Incomes Total', $totalIncomesFormatted),
            Stat::make('Expenses Total', $totalExpensesFormatted),
            Stat::make('Balance', $latestBalanceFormatted),
        ];
    }
}
