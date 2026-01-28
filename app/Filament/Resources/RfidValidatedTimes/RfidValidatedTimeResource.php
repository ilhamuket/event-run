<?php

namespace App\Filament\Resources\RfidValidatedTimes;

use App\Filament\Resources\RfidValidatedTimes\Pages\CreateRfidValidatedTime;
use App\Filament\Resources\RfidValidatedTimes\Pages\EditRfidValidatedTime;
use App\Filament\Resources\RfidValidatedTimes\Pages\ListRfidValidatedTimes;
use App\Filament\Resources\RfidValidatedTimes\Pages\ViewRfidValidatedTime;
use App\Filament\Resources\RfidValidatedTimes\Schemas\RfidValidatedTimeForm;
use App\Filament\Resources\RfidValidatedTimes\Schemas\RfidValidatedTimeInfolist;
use App\Filament\Resources\RfidValidatedTimes\Tables\RfidValidatedTimesTable;
use App\Models\RfidValidatedTime;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RfidValidatedTimeResource extends Resource
{
    protected static ?string $model = RfidValidatedTime::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'RfidValidatedTime';

    public static function form(Schema $schema): Schema
    {
        return RfidValidatedTimeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RfidValidatedTimeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RfidValidatedTimesTable::configure($table);
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
            'index' => ListRfidValidatedTimes::route('/'),
            'create' => CreateRfidValidatedTime::route('/create'),
            'view' => ViewRfidValidatedTime::route('/{record}'),
            'edit' => EditRfidValidatedTime::route('/{record}/edit'),
        ];
    }
}
