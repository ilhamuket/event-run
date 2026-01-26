<?php

namespace App\Filament\Resources\EventHeroImages\Pages;

use App\Filament\Resources\EventHeroImages\EventHeroImageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventHeroImages extends ListRecords
{
    protected static string $resource = EventHeroImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
