<?php

namespace App\Filament\Resources\RfidValidatedTimes\Pages;

use App\Filament\Resources\RfidValidatedTimes\RfidValidatedTimeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRfidValidatedTimes extends ListRecords
{
    protected static string $resource = RfidValidatedTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
