<?php

namespace App\Filament\Resources\EventHeroImages;

use App\Filament\Resources\EventHeroImages\Pages\CreateEventHeroImage;
use App\Filament\Resources\EventHeroImages\Pages\EditEventHeroImage;
use App\Filament\Resources\EventHeroImages\Pages\ListEventHeroImages;
use App\Filament\Resources\EventHeroImages\Pages\ViewEventHeroImage;
use App\Filament\Resources\EventHeroImages\Schemas\EventHeroImageForm;
use App\Filament\Resources\EventHeroImages\Schemas\EventHeroImageInfolist;
use App\Filament\Resources\EventHeroImages\Tables\EventHeroImagesTable;
use App\Models\EventHeroImage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventHeroImageResource extends Resource
{
    protected static ?string $model = EventHeroImage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'EventHeroImage';

    public static function form(Schema $schema): Schema
    {
        return EventHeroImageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EventHeroImageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventHeroImagesTable::configure($table);
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
            'index' => ListEventHeroImages::route('/'),
            'create' => CreateEventHeroImage::route('/create'),
            'view' => ViewEventHeroImage::route('/{record}'),
            'edit' => EditEventHeroImage::route('/{record}/edit'),
        ];
    }
}
