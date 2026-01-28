<?php

namespace App\Filament\Resources\ParticipantRfidMappings\Pages;

use App\Filament\Resources\ParticipantRfidMappings\ParticipantRfidMappingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditParticipantRfidMapping extends EditRecord
{
    protected static string $resource = ParticipantRfidMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
