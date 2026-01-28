<?php

namespace App\Filament\Resources\RfidRawLogs\Pages;

use App\Filament\Resources\RfidRawLogs\RfidRawLogResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRfidRawLog extends EditRecord
{
    protected static string $resource = RfidRawLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
