<?php

namespace App\Filament\Resources\ParticipantRfidMappings\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ParticipantRfidMappingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('participant.name')
                    ->label('Participant'),
                TextEntry::make('rfid_tag'),
                TextEntry::make('assigned_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('assigned_by')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('is_active')
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
