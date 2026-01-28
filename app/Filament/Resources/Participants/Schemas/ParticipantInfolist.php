<?php

namespace App\Filament\Resources\Participants\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ParticipantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('event.name')
                    ->label('Event'),
                TextEntry::make('bib')
                    ->placeholder('-'),
                TextEntry::make('bib_name')
                    ->placeholder('-'),
                TextEntry::make('name'),
                TextEntry::make('gender')
                    ->badge(),
                TextEntry::make('age')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('address')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('country'),
                TextEntry::make('province')
                    ->placeholder('-'),
                TextEntry::make('regency')
                    ->placeholder('-'),
                TextEntry::make('category')
                    ->placeholder('-'),
                TextEntry::make('city')
                    ->placeholder('-'),
                TextEntry::make('jersey_size')
                    ->placeholder('-'),
                TextEntry::make('community')
                    ->placeholder('-'),
                TextEntry::make('emergency_contact_name')
                    ->placeholder('-'),
                TextEntry::make('emergency_contact_phone')
                    ->placeholder('-'),
                IconEntry::make('has_comorbid')
                    ->boolean(),
                TextEntry::make('comorbid_details')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('elapsed_time')
                    ->time()
                    ->placeholder('-'),
                TextEntry::make('general_position')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('category_position')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
