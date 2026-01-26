<?php

namespace App\Filament\Resources\EventRacepackItems\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EventRacepackItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('event_id')
                    ->numeric(),
                TextEntry::make('item_name'),
                TextEntry::make('item_number')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->columnSpanFull(),
                ImageEntry::make('image_path')
                ->disk('public')
                    ->placeholder('-'),
                TextEntry::make('badge_color'),
                TextEntry::make('order')
                    ->numeric(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
