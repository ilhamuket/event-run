<?php

namespace App\Filament\Resources\Participants\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ParticipantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->relationship('event', 'name')
                    ->required(),
                TextInput::make('bib'),
                TextInput::make('bib_name'),
                TextInput::make('name')
                    ->required(),
                Select::make('gender')
                    ->options(['M' => 'M', 'F' => 'F'])
                    ->required(),
                TextInput::make('age')
                    ->numeric(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('country')
                    ->required()
                    ->default('Indonesia'),
                TextInput::make('province'),
                TextInput::make('regency'),
                TextInput::make('category'),
                TextInput::make('city'),
                TextInput::make('jersey_size'),
                TextInput::make('community'),
                TextInput::make('emergency_contact_name'),
                TextInput::make('emergency_contact_phone')
                    ->tel(),
                Toggle::make('has_comorbid')
                    ->required(),
                Textarea::make('comorbid_details')
                    ->columnSpanFull(),
                TimePicker::make('elapsed_time'),
                TextInput::make('general_position')
                    ->numeric(),
                TextInput::make('category_position')
                    ->numeric(),
            ]);
    }
}
