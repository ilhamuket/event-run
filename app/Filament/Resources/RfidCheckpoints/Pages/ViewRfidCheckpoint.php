<?php

namespace App\Filament\Resources\RfidCheckpoints\Pages;

use App\Filament\Resources\RfidCheckpoints\RfidCheckpointResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRfidCheckpoint extends ViewRecord
{
    protected static string $resource = RfidCheckpointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
