<?php

namespace App\Filament\Resources\IncomeResource\Pages;

use App\Filament\Resources\IncomeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIncome extends EditRecord
{
    protected static string $resource = IncomeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $year = now()->year;
        $userId = $data['user_id'];

        if (empty($data['year'])) {
            $data['year'] = $year;
        }

        $wajibOptions = IncomeResource::generatePaymentOptionsIncome($userId, 'wajib', $year);
        $sampahOptions = IncomeResource::generatePaymentOptionsIncome($userId, 'sampah', $year);

        $data['wajibPayments'] = array_keys(array_filter($wajibOptions, fn ($option) => $option['checked']));
        $data['sampahPayments'] = array_keys(array_filter($sampahOptions, fn ($option) => $option['checked']));

        $data['arrear_wajib'] = IncomeResource::calculateArrears($userId, 'wajib');
        $data['arrear_sampah'] = IncomeResource::calculateArrears($userId, 'sampah');

        return $data;
    }
}
