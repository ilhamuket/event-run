<?php

namespace App\Filament\Resources\EventCoupons\Pages;

use App\Filament\Resources\EventCoupons\EventCouponResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEventCoupon extends EditRecord
{
    protected static string $resource = EventCouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
