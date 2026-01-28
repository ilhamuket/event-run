<?php

namespace App\Filament\Resources\RfidCheckpoints\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RfidCheckpointInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('eventCategory.name')
                    ->label('Event category'),
                TextEntry::make('checkpoint_type'),
                TextEntry::make('checkpoint_name'),
                TextEntry::make('checkpoint_order')
                    ->numeric(),
                TextEntry::make('distance_km')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('location_name')
                    ->placeholder('-'),
                TextEntry::make('latitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('longitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('cutoff_time')
                    ->time()
                    ->placeholder('-'),
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
