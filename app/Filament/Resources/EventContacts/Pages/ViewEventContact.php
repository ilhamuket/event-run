<?php

namespace App\Filament\Resources\EventContacts\Pages;

use App\Filament\Resources\EventContacts\EventContactResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEventContact extends ViewRecord
{
    protected static string $resource = EventContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
