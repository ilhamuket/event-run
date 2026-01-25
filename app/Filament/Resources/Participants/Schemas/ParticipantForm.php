<?php

namespace App\Filament\Resources\Participants\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class ParticipantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('event_id')
                    ->required()
                    ->numeric(),
                TextInput::make('bib')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Select::make('gender')
                    ->options(['M' => 'M', 'F' => 'F'])
                    ->required(),
                TextInput::make('category'),
                TextInput::make('city'),
                TimePicker::make('elapsed_time'),
                TextInput::make('general_position')
                    ->numeric(),
                TextInput::make('category_position')
                    ->numeric(),
            ]);
    }
}
