<?php

namespace App\Filament\Resources\RfidRawLogs\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RfidRawLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('event.name')
                    ->label('Event'),
                TextEntry::make('rfid_checkpoint_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('rfid_tag'),
                TextEntry::make('bib')
                    ->placeholder('-'),
                TextEntry::make('scanned_at')
                    ->dateTime(),
                TextEntry::make('reader_id')
                    ->placeholder('-'),
                TextEntry::make('signal_strength')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('is_valid')
                    ->boolean(),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
