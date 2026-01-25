<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('poster'),
                DateTimePicker::make('start_time')
                    ->required(),
                TextInput::make('location_name'),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                TextInput::make('instagram_url')
                    ->url(),
                TextInput::make('strava_route_url')
                    ->url(),
                TextInput::make('contact_phone')
                    ->tel(),
                Toggle::make('is_published')
                    ->required(),
            ]);
    }
}
