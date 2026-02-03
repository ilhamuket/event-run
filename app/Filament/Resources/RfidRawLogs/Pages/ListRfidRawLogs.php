<?php

namespace App\Filament\Resources\RfidRawLogs\Pages;

use App\Filament\Resources\RfidRawLogs\RfidRawLogResource;
use Filament\Resources\Pages\ListRecords;

class ListRfidRawLogs extends ListRecords
{
    protected static string $resource = RfidRawLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
