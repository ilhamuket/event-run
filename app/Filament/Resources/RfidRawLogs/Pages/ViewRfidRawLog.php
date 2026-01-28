<?php

namespace App\Filament\Resources\RfidRawLogs\Pages;

use App\Filament\Resources\RfidRawLogs\RfidRawLogResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRfidRawLog extends ViewRecord
{
    protected static string $resource = RfidRawLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
