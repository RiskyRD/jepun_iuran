<?php

namespace App\Filament\Resources\IncomeResource\Pages;

use App\Filament\Resources\IncomeResource;
use App\Models\Income;
use Filament\Resources\Pages\CreateRecord;

class CreateIncome extends CreateRecord
{
    protected static string $resource = IncomeResource::class;

    protected function handleRecordCreation(array $data): Income
    {
        $amount = $data['amount'] ?? 1; 
        $incomeDate = \Carbon\Carbon::parse($data['income_date']); 
        $createdCount = 0; 
        $currentDate = $incomeDate->copy(); 
    
        while ($createdCount < $amount) {
            $exists = Income::where('user_id', $data['user_id'])
                ->where('category', $data['category'] === 'wajib_sampah' ? 'wajib' : $data['category'])
                ->whereMonth('income_date', $currentDate->month)
                ->whereYear('income_date', $currentDate->year)
                ->exists();
            if (!$exists) {
                if ($data['category'] === 'wajib_sampah') {
                    Income::create([
                        'user_id' => $data['user_id'],
                        'category' => 'wajib',
                        'nominal' => 5000,
                        'income_date' => $currentDate,
                        'description' => $data['description'],
                    ]);
                    Income::create([
                        'user_id' => $data['user_id'],
                        'category' => 'sampah',
                        'nominal' => 20000,
                        'income_date' => $currentDate,
                        'description' => $data['description'],
                    ]);
                } else {
                    Income::create([
                        'user_id' => $data['user_id'],
                        'category' => $data['category'],
                        'nominal' => $data['nominal'],
                        'income_date' => $currentDate,
                        'description' => $data['description'],
                    ]);
                }
    
                $createdCount++;
            }
            
            $currentDate->addMonth();
        }
    
        return new Income(); // Return an empty instance or modify as needed
    }


    protected function getRedirectUrl(): string
    {
        return '/admin/incomes';
    }

}
