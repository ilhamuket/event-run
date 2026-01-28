<?php

namespace App\Filament\Resources\RfidRawLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RfidRawLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->relationship('event', 'name')
                    ->required(),
                TextInput::make('rfid_checkpoint_id')
                    ->numeric(),
                TextInput::make('rfid_tag')
                    ->required(),
                TextInput::make('bib'),
                DateTimePicker::make('scanned_at')
                    ->required(),
                TextInput::make('reader_id'),
                TextInput::make('signal_strength')
                    ->numeric(),
                Toggle::make('is_valid')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
