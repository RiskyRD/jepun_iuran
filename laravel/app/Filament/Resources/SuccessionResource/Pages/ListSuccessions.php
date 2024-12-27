<?php

namespace App\Filament\Resources\SuccessionResource\Pages;

use App\Filament\Resources\SuccessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSuccessions extends ListRecords
{
    protected static string $resource = SuccessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
