<?php

namespace App\Filament\Resources\EventHeroImages\Pages;

use App\Filament\Resources\EventHeroImages\EventHeroImageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEventHeroImage extends EditRecord
{
    protected static string $resource = EventHeroImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
