<?php

namespace App\Filament\Resources\RfidValidatedTimes\Pages;

use App\Filament\Resources\RfidValidatedTimes\RfidValidatedTimeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRfidValidatedTime extends EditRecord
{
    protected static string $resource = RfidValidatedTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
