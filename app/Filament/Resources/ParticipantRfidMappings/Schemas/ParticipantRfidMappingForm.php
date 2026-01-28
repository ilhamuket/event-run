<?php

namespace App\Filament\Resources\ParticipantRfidMappings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ParticipantRfidMappingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('participant_id')
                    ->relationship('participant', 'name')
                    ->required(),
                TextInput::make('rfid_tag')
                    ->required(),
                DateTimePicker::make('assigned_at'),
                TextInput::make('assigned_by')
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
