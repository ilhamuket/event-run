<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('slug'),
                TextEntry::make('name'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('poster')
                    ->placeholder('-'),
                ImageEntry::make('banner_image')
                    ->disk('public')
                    ->placeholder('-'),
                TextEntry::make('race_guide')
                    ->placeholder('-'),
                TextEntry::make('start_time')
                    ->dateTime(),
                TextEntry::make('location_name')
                    ->placeholder('-'),
                TextEntry::make('latitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('longitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('instagram_url')
                    ->placeholder('-'),
                TextEntry::make('strava_route_url')
                    ->placeholder('-'),
                TextEntry::make('youtube_url')
                    ->placeholder('-'),
                TextEntry::make('contact_phone')
                    ->placeholder('-'),
                IconEntry::make('is_published')
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
