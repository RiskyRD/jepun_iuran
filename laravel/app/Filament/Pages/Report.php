<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Balance;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;

class Report extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.report';
    public $startDate; public $endDate; public $selectRange = 'custom';
    public $totalIncomes; public $totalExpense;

    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'pengurus']);
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('selectRange')
                ->label('Date Range')
                ->options([
                    'today' => 'Today',
                    'this_week' => 'This Week',
                    'this_month' => 'This Month',
                    'last_month' => 'Last Month',
                    'this_year' => 'This Year',
                    'custom' => 'Custom',
                ])
                ->default('custom')
                ->reactive()
                ->afterStateUpdated(fn ($state, callable $set) => $this->updateDateRange($state, $set)),

            Forms\Components\DatePicker::make('startDate')
                ->label('Start Date')
                ->required(),

            Forms\Components\DatePicker::make('endDate')
                ->label('End Date')
                ->required(),
        ];
    }

    public function updateDateRange($range, $set)
    {
        $today = Carbon::today();

        switch ($range) {
            case 'today':
                $set('startDate', $today->toDateString());
                $set('endDate', $today->toDateString());
                break;

            case 'this_week':
                $set('startDate', $today->startOfWeek()->toDateString());
                $set('endDate', $today->endOfWeek()->toDateString());
                break;

            case 'this_month':
                $set('startDate', $today->startOfMonth()->toDateString());
                $set('endDate', $today->endOfMonth()->toDateString());
                break;

            case 'this_year':
                $set('startDate', $today->startOfYear()->toDateString());
                $set('endDate', $today->endOfYear()->toDateString());
                break;

            case 'last_month':
                $set('startDate', $today->startOfMonth()->subMonth()->toDateString());
                $set('endDate', $today->endOfMonth()->toDateString());
                break;

            case 'custom':
                break;
        }
    }

    public $incomes = []; public $expenses = []; public $totalArrears = 0; public $previousBalance = 0;
    public $totalIncomesQuantity = 0; public $totalIncomesAmount = 0; public $totalExpensesQuantity = 0;
    public $totalExpensesAmount = 0; public $latestBalance = 0; public $arrearsData = []; public $isReportGenerated = false;

    public function generateReport()
    {
        // Check if report is generated (for showing the buttons)
        $this->isReportGenerated = false;
        if (!$this->startDate || !$this->endDate) {
            $this->totalIncomes = 0;
            $this->totalExpense = 0;
            $this->isReportGenerated = false;
            return;
        }
        $this->isReportGenerated = true;

        // Take the start date and end date
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        // Get the previous balance
        $previousBalanceRecord = Balance::where('balance_date', '<', $start)
            ->orderBy('balance_date', 'desc')
            ->first();

        // If there are no previous balance on the month before the start date, use the first balance
        // Also getting the date of the previous balance to calculate the latest balence
        if ($previousBalanceRecord) {
            $this->previousBalance = $previousBalanceRecord->nominal;
            $balanceDate = $previousBalanceRecord->balance_date;
        } else {
            $oldestBalanceRecord = Balance::orderBy('balance_date', 'asc')->first();
            $this->previousBalance = $oldestBalanceRecord ? $oldestBalanceRecord->nominal : 0;
            $balanceDate = $oldestBalanceRecord ? $oldestBalanceRecord->balance_date : $start;
        }

        // Get incomes data
        $this->incomes = Income::select('category', \DB::raw('COUNT(*) as quantity'), \DB::raw('SUM(nominal) as amount'))
            ->whereBetween('income_date', [$start, $end])
            ->groupBy('category')
            ->get()
            ->toArray();
        // Get expenses data
        $this->expenses = Expense::select('category', \DB::raw('COUNT(*) as quantity'), \DB::raw('SUM(nominal) as amount'))
            ->whereBetween('expense_date', [$start, $end])
            ->groupBy('category')
            ->get() 
            ->toArray();
        // Incomes and expenses quantity and amount
        $this->totalIncomesQuantity = array_sum(array_column($this->incomes, 'quantity'));
        $this->totalIncomesAmount = array_sum(array_column($this->incomes, 'amount'));
        $this->totalExpensesQuantity = array_sum(array_column($this->expenses, 'quantity'));
        $this->totalExpensesAmount = array_sum(array_column($this->expenses, 'amount'));

        // Incomes and expenses after the previous balance to calculate the latest balance 
        $balanceIncomes = Income::whereBetween('income_date', [$balanceDate, $end])->sum('nominal');
        $balanceExpenses = Expense::whereBetween('expense_date', [$balanceDate, $end])->sum('nominal');

        // Calculate latest balance
        $this->latestBalance = $this->previousBalance + $balanceIncomes - $balanceExpenses;

        // Get the arrears data
        $this->arrearsData = $this->calculateArrears();  
    }

    public function calculateArrears()
    {
        // Calculate the number of months passed since January 2024
        $startOf2024 = Carbon::create(2024, 1, 1);
        $endDate = Carbon::parse($this->endDate);
        $monthsPassed = ceil($startOf2024->diffInMonths($endDate));

        // Step 2: Get the total user count excluding the admin
        $totalUsers = \App\Models\User::where('name', '!=', 'admin')->count();

        // Step 3: Calculate the expected total number of payments (for each category) up to today
        $expectedPayments = $monthsPassed * $totalUsers;

        // Step 4: Calculate the actual payments made in each routine payment category
        $actualWajibPayments = Income::where('category',    'wajib')->count();
        $actualSampahPayments = Income::where('category', 'sampah')->count();

        // Step 5: Calculate arrears for each category
        $arrearsWajib = $expectedPayments - $actualWajibPayments;
        $arrearsSampah = $expectedPayments - $actualSampahPayments;
        $arrearsWajibNominal = $arrearsWajib * 5000;
        $arrearsSampahNominal = $arrearsSampah * 20000;

        $totalArrears = $arrearsWajib + $arrearsSampah;
        $totalArrearsNominal = $arrearsWajibNominal + $arrearsSampahNominal;

        return [
            'arrears_wajib' => $arrearsWajib ?? 0,
            'arrears_sampah' => $arrearsSampah ?? 0,
            'total_tunggakan' => $totalArrears ?? 0,
            'arrears_wajib_nominal' => $arrearsWajibNominal ?? 0,
            'arrears_sampah_nominal' => $arrearsSampahNominal ?? 0,
            'total_tunggakan_nominal' => $totalArrearsNominal ?? 0
        ];
    }

    public function storeBalance()
    {
        if (!$this->latestBalance || !$this->endDate) {
            Notification::make()
                ->title('Please generate report first')
                ->danger()
                ->send();
            return;
        }

        $lastDate = Carbon::parse($this->endDate);

        // Check if a balance record with the same date already exists
        $existingBalance = Balance::whereDate('balance_date', $lastDate)->exists();

        if ($existingBalance) {
            Notification::make()
                ->title('Balance for this date already exists')
                ->danger()
                ->send();
            return;
        }

        // Create the balance record if it does not already exist
        Balance::create([
            'nominal' => $this->latestBalance,
            'balance_date' => $lastDate,
        ]);

        Notification::make()
            ->title('Balance stored')
            ->success()
            ->send();
    }

    public function makeReport()
    {
        // Call the generateReport method to populate incomes, expenses, and other data
        $this->generateReport();

        $start = Carbon::parse($this->startDate)->format('dmY');
        $end = Carbon::parse($this->endDate)->format('dmY');
        $fileName = "Laporan_IGJ_{$start}{$end}.pdf";

        // Prepare the PDF using the data calculated in generateReport
        $pdf = Pdf::loadView('pdf.report', [
            'incomes' => $this->incomes,
            'expenses' => $this->expenses,
            'totalIncomes' => $this->totalIncomes,
            'totalExpense' => $this->totalExpense,
            'arrearWajib' => $this->arrearsData['arrears_wajib'],
            'arrearSampah' => $this->arrearsData['arrears_sampah'],
            'totalArrears' => $this->arrearsData['total_tunggakan_nominal'],
            'previousBalance' => $this->previousBalance,
            'latestBalance' => $this->latestBalance,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'totalIncomesQuantity' => $this->totalIncomesQuantity,
            'totalIncomesAmount' => $this->totalIncomesAmount,
            'totalExpensesQuantity' => $this->totalExpensesQuantity,
            'totalExpensesAmount' => $this->totalExpensesAmount,
        ]);

        Notification::make()
            ->title('Report created')
            ->icon('heroicon-o-document-text')
            ->iconColor('success')
            ->send();

        // Stream the PDF for download
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }
}
