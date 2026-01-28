<?php

namespace App\Filament\Resources\ParticipantRfidMappings\Pages;

use App\Filament\Resources\ParticipantRfidMappings\ParticipantRfidMappingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListParticipantRfidMappings extends ListRecords
{
    protected static string $resource = ParticipantRfidMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
