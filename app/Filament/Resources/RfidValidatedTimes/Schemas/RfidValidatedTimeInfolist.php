<?php

namespace App\Filament\Resources\RfidValidatedTimes\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RfidValidatedTimeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Participant')
                    ->schema([
                        TextEntry::make('participant.bib')
                            ->label('BIB'),
                        TextEntry::make('participant.name')
                            ->label('Name'),
                        TextEntry::make('participant.category.name')
                            ->label('Category'),
                        TextEntry::make('participant.gender')
                            ->label('Gender'),
                    ])
                    ->columns(4),

                Section::make('Checkpoint')
                    ->schema([
                        TextEntry::make('checkpoint.checkpoint_name')
                            ->label('Checkpoint'),
                        TextEntry::make('checkpoint.checkpoint_type')
                            ->label('Type')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'start' => 'success',
                                'checkpoint' => 'warning',
                                'finish' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('checkpoint.distance_km')
                            ->label('Distance')
                            ->suffix(' km')
                            ->placeholder('-'),
                    ])
                    ->columns(3),

                Section::make('Timing')
                    ->schema([
                        TextEntry::make('checkpoint_time')
                            ->label('Checkpoint Time')
                            ->dateTime(),
                        TextEntry::make('elapsed_time')
                            ->label('Elapsed Time')
                            ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('H:i:s') : '-'),
                        TextEntry::make('split_time')
                            ->label('Split Time')
                            ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('H:i:s') : '-'),
                        TextEntry::make('position_at_checkpoint')
                            ->label('Position')
                            ->placeholder('-'),
                    ])
                    ->columns(4),

                Section::make('Validation')
                    ->schema([
                        TextEntry::make('validation_status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'auto' => 'success',
                                'manual' => 'warning',
                                'corrected' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('validator.name')
                            ->label('Validated By')
                            ->placeholder('-'),
                        TextEntry::make('validation_notes')
                            ->label('Notes')
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
