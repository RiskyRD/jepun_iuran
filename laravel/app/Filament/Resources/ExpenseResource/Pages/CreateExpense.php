<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use App\Models\Expense;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function handleRecordCreation(array $data): Expense
    {
        $amount = $data['amount'] ?? 1;

        for ($i = 0; $i < $amount; $i++) {
            Expense::create([
                'category' => $data['category'],
                'nominal' => $data['nominal'],
                'expense_date' => $data['expense_date'],
                'description' => $data['description'],
            ]);
        }

        return new Expense();
    }

    protected function getRedirectUrl(): string
    {
        return '/admin/expenses';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $year = now()->year;

        $sampahOptions = ExpenseResource::generatePaymentOptionsExpense('sampah', $year);

        $data['sampahPayments'] = array_keys(array_filter($sampahOptions, fn ($option) => $option['checked']));

        return $data;
    }
}
