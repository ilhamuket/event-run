<?php

namespace App\Filament\Resources\RfidCheckpoints\Pages;

use App\Filament\Resources\RfidCheckpoints\RfidCheckpointResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRfidCheckpoint extends EditRecord
{
    protected static string $resource = RfidCheckpointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
