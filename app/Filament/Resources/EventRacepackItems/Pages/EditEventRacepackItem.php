<?php

namespace App\Filament\Resources\EventRacepackItems\Pages;

use App\Filament\Resources\EventRacepackItems\EventRacepackItemResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEventRacepackItem extends EditRecord
{
    protected static string $resource = EventRacepackItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
