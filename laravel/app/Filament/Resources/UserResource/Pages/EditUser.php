<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\IncomeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['wajibPayments'] = $data['wajibPayments'] ?? [];
        $data['sampahPayments'] = $data['sampahPayments'] ?? [];

        $data['year'] = $data['year'] ?? now()->year;
        $userId = $data['id'];

        $wajibOptions = UserResource::generatePaymentOptionsUser($userId, 'wajib', $data['year']);
        $sampahOptions = UserResource::generatePaymentOptionsUser($userId, 'sampah', $data['year']);

        $data['wajibPayments'] = array_keys(array_filter($wajibOptions, fn($option) => $option['checked']));
        $data['sampahPayments'] = array_keys(array_filter($sampahOptions, fn($option) => $option['checked']));

        $data['arrear_wajib'] = IncomeResource::calculateArrears($userId, 'wajib');
        $data['arrear_sampah'] = IncomeResource::calculateArrears($userId, 'sampah');

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }
}
