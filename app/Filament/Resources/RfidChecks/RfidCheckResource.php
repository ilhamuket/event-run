<?php

namespace App\Filament\Resources\RfidChecks;

use App\Filament\Resources\RfidChecks\Pages\CheckRfid;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use UnitEnum;
use BackedEnum;

class RfidCheckResource extends Resource
{
    protected static ?string $model = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static string|UnitEnum|null $navigationGroup = 'RFID Timing';

    protected static ?string $navigationLabel = 'RFID Check';

    protected static ?int $navigationSort = 3;

    public static function getPages(): array
    {
        return [
            'index' => CheckRfid::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
