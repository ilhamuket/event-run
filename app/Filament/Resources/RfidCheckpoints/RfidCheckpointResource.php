<?php

namespace App\Filament\Resources\RfidCheckpoints;

use App\Filament\Resources\RfidCheckpoints\Pages\CreateRfidCheckpoint;
use App\Filament\Resources\RfidCheckpoints\Pages\EditRfidCheckpoint;
use App\Filament\Resources\RfidCheckpoints\Pages\ListRfidCheckpoints;
use App\Filament\Resources\RfidCheckpoints\Pages\ViewRfidCheckpoint;
use App\Filament\Resources\RfidCheckpoints\Schemas\RfidCheckpointForm;
use App\Filament\Resources\RfidCheckpoints\Schemas\RfidCheckpointInfolist;
use App\Filament\Resources\RfidCheckpoints\Tables\RfidCheckpointsTable;
use App\Models\RfidCheckpoint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RfidCheckpointResource extends Resource
{
    protected static ?string $model = RfidCheckpoint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static string|UnitEnum|null $navigationGroup = 'RFID Timing';

    protected static ?string $navigationLabel = 'Checkpoints';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'checkpoint_name';

    public static function form(Schema $schema): Schema
    {
        return RfidCheckpointForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RfidCheckpointInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RfidCheckpointsTable::configure($table);
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
            'index' => ListRfidCheckpoints::route('/'),
            'create' => CreateRfidCheckpoint::route('/create'),
            'view' => ViewRfidCheckpoint::route('/{record}'),
            'edit' => EditRfidCheckpoint::route('/{record}/edit'),
        ];
    }
}
