<?php

namespace App\Filament\Resources\RfidRawLogs;

use App\Filament\Resources\RfidRawLogs\Pages\CreateRfidRawLog;
use App\Filament\Resources\RfidRawLogs\Pages\EditRfidRawLog;
use App\Filament\Resources\RfidRawLogs\Pages\ListRfidRawLogs;
use App\Filament\Resources\RfidRawLogs\Pages\ViewRfidRawLog;
use App\Filament\Resources\RfidRawLogs\Schemas\RfidRawLogForm;
use App\Filament\Resources\RfidRawLogs\Schemas\RfidRawLogInfolist;
use App\Filament\Resources\RfidRawLogs\Tables\RfidRawLogsTable;
use App\Models\RfidRawLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RfidRawLogResource extends Resource
{
    protected static ?string $model = RfidRawLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'RfidRawLog';

    public static function form(Schema $schema): Schema
    {
        return RfidRawLogForm::configure($schema);
    }

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
            'create' => CreateRfidRawLog::route('/create'),
            'view' => ViewRfidRawLog::route('/{record}'),
            'edit' => EditRfidRawLog::route('/{record}/edit'),
        ];
    }
}
