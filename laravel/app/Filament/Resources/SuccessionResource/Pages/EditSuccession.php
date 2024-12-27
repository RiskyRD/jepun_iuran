<?php

namespace App\Filament\Resources\SuccessionResource\Pages;

use App\Filament\Resources\SuccessionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSuccession extends EditRecord
{
    protected static string $resource = SuccessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
