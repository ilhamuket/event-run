<?php

namespace App\Filament\Resources\ParticipantRfidMappings\Schemas;

use App\Models\Participant;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ParticipantRfidMappingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('RFID Assignment')
                    ->schema([
                        Select::make('participant_id')
                            ->label('Participant')
                            ->options(function () {
                                return Participant::with(['event', 'category'])
                                    ->whereDoesntHave('rfidMapping', function ($query) {
                                        $query->where('is_active', true);
                                    })
                                    ->get()
                                    ->mapWithKeys(function ($participant) {
                                        $label = sprintf(
                                            '[%s] %s - %s (%s)',
                                            $participant->bib ?? 'No BIB',
                                            $participant->name,
                                            $participant->event->name ?? 'No Event',
                                            $participant->category->name ?? 'No Category'
                                        );
                                        return [$participant->id => $label];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Only participants without active RFID mapping are shown'),

                        TextInput::make('rfid_tag')
                            ->label('RFID Tag')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('Scan or enter RFID tag')
                            ->helperText('The unique identifier from the RFID chip'),

                        DateTimePicker::make('assigned_at')
                            ->label('Assigned At')
                            ->default(now())
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Deactivate to unlink RFID without deleting history'),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
