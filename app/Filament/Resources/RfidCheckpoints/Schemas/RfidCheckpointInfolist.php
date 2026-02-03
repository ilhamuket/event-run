<?php

namespace App\Filament\Resources\RfidCheckpoints\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RfidCheckpointInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Checkpoint Information')
                    ->schema([
                        TextEntry::make('eventCategory.event.name')
                            ->label('Event'),
                        TextEntry::make('eventCategory.name')
                            ->label('Category'),
                        TextEntry::make('checkpoint_name')
                            ->label('Checkpoint Name'),
                        TextEntry::make('checkpoint_type')
                            ->label('Type')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'start' => 'success',
                                'checkpoint' => 'warning',
                                'finish' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('checkpoint_order')
                            ->label('Order'),
                        TextEntry::make('distance_km')
                            ->label('Distance')
                            ->suffix(' km')
                            ->placeholder('-'),
                        TextEntry::make('cutoff_time')
                            ->label('Cut-off Time')
                            ->time('H:i')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Location')
                    ->schema([
                        TextEntry::make('location_name')
                            ->label('Location Name')
                            ->placeholder('-'),
                        TextEntry::make('latitude')
                            ->placeholder('-'),
                        TextEntry::make('longitude')
                            ->placeholder('-'),
                    ])
                    ->columns(3),

                Section::make('Status')
                    ->schema([
                        IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(3),
            ]);
    }
}
