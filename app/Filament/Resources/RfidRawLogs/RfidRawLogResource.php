<?php

namespace App\Filament\Resources\RfidRawLogs;

use App\Filament\Resources\RfidRawLogs\Pages\ListRfidRawLogs;
use App\Filament\Resources\RfidRawLogs\Pages\ViewRfidRawLog;
use App\Filament\Resources\RfidRawLogs\Schemas\RfidRawLogInfolist;
use App\Filament\Resources\RfidRawLogs\Tables\RfidRawLogsTable;
use App\Models\RfidRawLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RfidRawLogResource extends Resource
{
    protected static ?string $model = RfidRawLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'RFID Timing';

    protected static ?string $navigationLabel = 'Raw Logs';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'rfid_tag';

    public static function infolist(Schema $schema): Schema
    {
        return RfidRawLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RfidRawLogsTable::configure($table);
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
            'index' => ListRfidRawLogs::route('/'),
            'view' => ViewRfidRawLog::route('/{record}'),
        ];
    }

    // Make this resource read-only
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
