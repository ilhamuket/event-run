<?php

namespace App\Filament\Resources\EventContacts;

use App\Filament\Resources\EventContacts\Pages\CreateEventContact;
use App\Filament\Resources\EventContacts\Pages\EditEventContact;
use App\Filament\Resources\EventContacts\Pages\ListEventContacts;
use App\Filament\Resources\EventContacts\Pages\ViewEventContact;
use App\Filament\Resources\EventContacts\Schemas\EventContactForm;
use App\Filament\Resources\EventContacts\Schemas\EventContactInfolist;
use App\Filament\Resources\EventContacts\Tables\EventContactsTable;
use App\Models\EventContact;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventContactResource extends Resource
{
    protected static ?string $model = EventContact::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'EventContact';

    public static function form(Schema $schema): Schema
    {
        return EventContactForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EventContactInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventContactsTable::configure($table);
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
            'index' => ListEventContacts::route('/'),
            'create' => CreateEventContact::route('/create'),
            'view' => ViewEventContact::route('/{record}'),
            'edit' => EditEventContact::route('/{record}/edit'),
        ];
    }
}
