<?php

namespace App\Filament\Resources\EventRacepackItems;

use App\Filament\Resources\EventRacepackItems\Pages\CreateEventRacepackItem;
use App\Filament\Resources\EventRacepackItems\Pages\EditEventRacepackItem;
use App\Filament\Resources\EventRacepackItems\Pages\ListEventRacepackItems;
use App\Filament\Resources\EventRacepackItems\Pages\ViewEventRacepackItem;
use App\Filament\Resources\EventRacepackItems\Schemas\EventRacepackItemForm;
use App\Filament\Resources\EventRacepackItems\Schemas\EventRacepackItemInfolist;
use App\Filament\Resources\EventRacepackItems\Tables\EventRacepackItemsTable;
use App\Models\EventRacepackItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventRacepackItemResource extends Resource
{
    protected static ?string $model = EventRacepackItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'EventRacepackItem';

    public static function form(Schema $schema): Schema
    {
        return EventRacepackItemForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EventRacepackItemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventRacepackItemsTable::configure($table);
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
            'index' => ListEventRacepackItems::route('/'),
            'create' => CreateEventRacepackItem::route('/create'),
            'view' => ViewEventRacepackItem::route('/{record}'),
            'edit' => EditEventRacepackItem::route('/{record}/edit'),
        ];
    }
}
