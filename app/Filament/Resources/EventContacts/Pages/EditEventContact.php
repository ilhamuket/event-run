<?php

namespace App\Filament\Resources\EventContacts\Pages;

use App\Filament\Resources\EventContacts\EventContactResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEventContact extends EditRecord
{
    protected static string $resource = EventContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
