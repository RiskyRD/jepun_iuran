<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpense extends EditRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $year = now()->year;

        if (empty($data['year'])) {
            $data['year'] = $year;
        }

        $sampahOptions = ExpenseResource::generatePaymentOptionsExpense('sampah', $year);
        $data['sampahPayments'] = array_keys(array_filter($sampahOptions, fn ($option) => $option['checked']));

        return $data;
    }
}
