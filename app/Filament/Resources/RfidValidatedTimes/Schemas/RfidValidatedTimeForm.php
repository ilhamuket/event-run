<?php

namespace App\Filament\Resources\RfidValidatedTimes\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class RfidValidatedTimeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('participant_id')
                    ->relationship('participant', 'name')
                    ->required(),
                TextInput::make('rfid_checkpoint_id')
                    ->required()
                    ->numeric(),
                TextInput::make('rfid_raw_log_id')
                    ->numeric(),
                DateTimePicker::make('checkpoint_time')
                    ->required(),
                TimePicker::make('elapsed_time'),
                TimePicker::make('split_time'),
                TextInput::make('position_at_checkpoint')
                    ->numeric(),
                Select::make('validation_status')
                    ->options(['auto' => 'Auto', 'manual' => 'Manual', 'corrected' => 'Corrected'])
                    ->default('auto')
                    ->required(),
                Textarea::make('validation_notes')
                    ->columnSpanFull(),
                TextInput::make('validated_by')
                    ->numeric(),
            ]);
    }
}
