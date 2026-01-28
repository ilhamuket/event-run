<?php

namespace App\Filament\Resources\RfidCheckpoints\Pages;

use App\Filament\Resources\RfidCheckpoints\RfidCheckpointResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRfidCheckpoints extends ListRecords
{
    protected static string $resource = RfidCheckpointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
