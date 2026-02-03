<?php

namespace App\Filament\Resources\ParticipantRfidMappings\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ParticipantRfidMappingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Participant Info')
                    ->schema([
                        TextEntry::make('participant.bib')
                            ->label('BIB'),
                        TextEntry::make('participant.name')
                            ->label('Name'),
                        TextEntry::make('participant.event.name')
                            ->label('Event'),
                        TextEntry::make('participant.category.name')
                            ->label('Category'),
                    ])
                    ->columns(2),

                Section::make('RFID Assignment')
                    ->schema([
                        TextEntry::make('rfid_tag')
                            ->label('RFID Tag')
                            ->copyable()
                            ->copyMessage('RFID tag copied'),
                        TextEntry::make('assigned_at')
                            ->label('Assigned At')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('assignedBy.name')
                            ->label('Assigned By')
                            ->placeholder('-'),
                        IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                        TextEntry::make('notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

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
