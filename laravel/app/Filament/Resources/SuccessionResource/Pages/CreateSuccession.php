<?php

namespace App\Filament\Resources\SuccessionResource\Pages;

use App\Filament\Resources\SuccessionResource;
use App\Models\Succession;
use App\Models\User;
use App\Models\Income;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class CreateSuccession extends CreateRecord
{
    protected static string $resource = SuccessionResource::class;

    protected function handleRecordCreation(array $data): Succession
    {
        return DB::transaction(function () use ($data) {
            // Check if `to_id` is provided and has no existing income data
            if (!empty($data['to_id'])) {
                $toUserIncomes = Income::where('user_id', $data['to_id'])->exists();

                if ($toUserIncomes) {
                    // Show an alert if the successor has existing income data
                    Notification::make()
                        ->title('Error')
                        ->body('Successor must be empty.')
                        ->danger()
                        ->send();
                }
            }

            // Create the succession record
            $succession = Succession::create([
                'from_id' => $data['from_id'],
                'to_id' => $data['to_id'],
            ]);

            // Update the status of the original user (fromUser)
            if (isset($data['status'])) {
                $fromUser = User::find($data['from_id']);
                $fromUser->update(['status' => $data['status']]);
            }

            // Duplicate income records from `fromUser` to `toUser` if `to_id` is provided
            if (!empty($data['to_id'])) {
                $fromUserIncomes = Income::where('user_id', $data['from_id'])->get();

                foreach ($fromUserIncomes as $income) {
                    Income::create([
                        'user_id' => $data['to_id'],
                        'category' => $income->category,
                        'nominal' => $income->nominal,
                        'income_date' => $income->income_date,
                        'description' => $income->description,
                    ]);
                }
            }

            return $succession;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
