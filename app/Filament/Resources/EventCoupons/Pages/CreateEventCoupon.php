<?php

namespace App\Filament\Resources\EventCoupons\Pages;

use App\Filament\Resources\EventCoupons\EventCouponResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEventCoupon extends CreateRecord
{
    protected static string $resource = EventCouponResource::class;
}
