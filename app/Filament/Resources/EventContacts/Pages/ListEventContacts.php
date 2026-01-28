<?php

namespace App\Filament\Resources\EventContacts\Pages;

use App\Filament\Resources\EventContacts\EventContactResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventContacts extends ListRecords
{
    protected static string $resource = EventContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
