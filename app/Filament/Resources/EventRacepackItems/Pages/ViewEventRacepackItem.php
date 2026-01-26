<?php

namespace App\Filament\Resources\EventRacepackItems\Pages;

use App\Filament\Resources\EventRacepackItems\EventRacepackItemResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEventRacepackItem extends ViewRecord
{
    protected static string $resource = EventRacepackItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
