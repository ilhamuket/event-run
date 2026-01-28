<?php

namespace App\Filament\Resources\ParticipantRfidMappings\Pages;

use App\Filament\Resources\ParticipantRfidMappings\ParticipantRfidMappingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewParticipantRfidMapping extends ViewRecord
{
    protected static string $resource = ParticipantRfidMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
