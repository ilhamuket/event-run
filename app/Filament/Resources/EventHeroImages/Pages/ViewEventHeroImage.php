<?php

namespace App\Filament\Resources\EventHeroImages\Pages;

use App\Filament\Resources\EventHeroImages\EventHeroImageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEventHeroImage extends ViewRecord
{
    protected static string $resource = EventHeroImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
