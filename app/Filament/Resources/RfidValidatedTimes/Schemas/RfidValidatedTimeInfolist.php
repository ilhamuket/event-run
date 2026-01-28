<?php

namespace App\Filament\Resources\RfidValidatedTimes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RfidValidatedTimeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('participant.name')
                    ->label('Participant'),
                TextEntry::make('rfid_checkpoint_id')
                    ->numeric(),
                TextEntry::make('rfid_raw_log_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('checkpoint_time')
                    ->dateTime(),
                TextEntry::make('elapsed_time')
                    ->time()
                    ->placeholder('-'),
                TextEntry::make('split_time')
                    ->time()
                    ->placeholder('-'),
                TextEntry::make('position_at_checkpoint')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('validation_status')
                    ->badge(),
                TextEntry::make('validation_notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('validated_by')
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
