<?php

namespace App\Filament\Resources\ParticipantRfidMappings\Schemas;

use App\Models\ParticipantRfidMapping;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
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

                Section::make('RFID Assignment (Tag Ini)')
                    ->schema([
                        TextEntry::make('rfid_tag')
                            ->label('RFID Tag')
                            ->copyable()
                            ->copyMessage('RFID tag copied')
                            ->fontFamily('mono'),
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

                Section::make('Semua Tag Aktif Participant Ini')
                    ->description('Daftar seluruh RFID tag yang aktif untuk participant yang sama')
                    ->schema([
                        RepeatableEntry::make('participant.activeRfidMappings')
                            ->label('')
                            ->schema([
                                TextEntry::make('rfid_tag')
                                    ->label('Tag')
                                    ->copyable()
                                    ->fontFamily('mono'),
                                TextEntry::make('assigned_at')
                                    ->label('Assigned')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('notes')
                                    ->label('Notes')
                                    ->placeholder('-'),
                                IconEntry::make('is_active')
                                    ->label('Active')
                                    ->boolean(),
                            ])
                            ->columns(4)
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
