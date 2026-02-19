<?php

namespace App\Filament\Resources\EventCoupons\Pages;

use App\Filament\Resources\EventCoupons\EventCouponResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventCoupons extends ListRecords
{
    protected static string $resource = EventCouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
