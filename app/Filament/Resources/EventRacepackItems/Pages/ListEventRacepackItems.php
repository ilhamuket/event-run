<?php

namespace App\Filament\Resources\EventRacepackItems\Pages;

use App\Filament\Resources\EventRacepackItems\EventRacepackItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventRacepackItems extends ListRecords
{
    protected static string $resource = EventRacepackItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
