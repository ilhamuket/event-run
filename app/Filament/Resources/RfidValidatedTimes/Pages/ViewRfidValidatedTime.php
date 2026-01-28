<?php

namespace App\Filament\Resources\RfidValidatedTimes\Pages;

use App\Filament\Resources\RfidValidatedTimes\RfidValidatedTimeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRfidValidatedTime extends ViewRecord
{
    protected static string $resource = RfidValidatedTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
