<?php

namespace App\Filament\Resources\EventCoupons;

use App\Filament\Resources\EventCoupons\Pages\CreateEventCoupon;
use App\Filament\Resources\EventCoupons\Pages\EditEventCoupon;
use App\Filament\Resources\EventCoupons\Pages\ListEventCoupons;
use App\Filament\Resources\EventCoupons\Schemas\EventCouponForm;
use App\Filament\Resources\EventCoupons\Tables\EventCouponsTable;
use App\Models\EventCoupon;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventCouponResource extends Resource
{
    protected static ?string $model = EventCoupon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Schema $schema): Schema
    {
        return EventCouponForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventCouponsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventCoupons::route('/'),
            'create' => CreateEventCoupon::route('/create'),
            'edit' => EditEventCoupon::route('/{record}/edit'),
        ];
    }
}
