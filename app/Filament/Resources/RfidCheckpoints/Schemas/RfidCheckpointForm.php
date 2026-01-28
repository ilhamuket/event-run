<?php

namespace App\Filament\Resources\RfidCheckpoints\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RfidCheckpointForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_category_id')
                    ->relationship('eventCategory', 'name')
                    ->required(),
                TextInput::make('checkpoint_type')
                    ->required(),
                TextInput::make('checkpoint_name')
                    ->required(),
                TextInput::make('checkpoint_order')
                    ->required()
                    ->numeric(),
                TextInput::make('distance_km')
                    ->numeric(),
                TextInput::make('location_name'),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                TimePicker::make('cutoff_time'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
