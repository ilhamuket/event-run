<?php

namespace App\Filament\Resources\RfidRawLogs\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RfidRawLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Scan Details')
                    ->schema([
                        TextEntry::make('event.name')
                            ->label('Event'),
                        TextEntry::make('checkpoint.checkpoint_name')
                            ->label('Checkpoint')
                            ->placeholder('-'),
                        TextEntry::make('rfid_tag')
                            ->label('RFID Tag')
                            ->copyable(),
                        TextEntry::make('bib')
                            ->label('BIB')
                            ->placeholder('-'),
                        TextEntry::make('scanned_at')
                            ->label('Scanned At')
                            ->dateTime(),
                    ])
                    ->columns(2),

                Section::make('Reader Info')
                    ->schema([
                        TextEntry::make('reader_id')
                            ->label('Reader ID')
                            ->placeholder('-'),
                        TextEntry::make('signal_strength')
                            ->label('Signal Strength')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Validation')
                    ->schema([
                        IconEntry::make('is_valid')
                            ->label('Valid')
                            ->boolean(),
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Timestamps')
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }
}
