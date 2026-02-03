<?php

namespace App\Filament\Resources\RfidValidatedTimes\Schemas;

use App\Models\Participant;
use App\Models\RfidCheckpoint;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RfidValidatedTimeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Time Entry')
                    ->schema([
                        Select::make('participant_id')
                            ->label('Participant')
                            ->options(function () {
                                return Participant::with(['event', 'category'])
                                    ->whereNotNull('bib')
                                    ->get()
                                    ->mapWithKeys(function ($participant) {
                                        $label = sprintf(
                                            '[%s] %s - %s',
                                            $participant->bib,
                                            $participant->name,
                                            $participant->category->name ?? 'No Category'
                                        );
                                        return [$participant->id => $label];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('rfid_checkpoint_id', null);
                            }),

                        Select::make('rfid_checkpoint_id')
                            ->label('Checkpoint')
                            ->options(function (callable $get) {
                                $participantId = $get('participant_id');
                                if (!$participantId) {
                                    return [];
                                }

                                $participant = Participant::find($participantId);
                                if (!$participant || !$participant->event_category_id) {
                                    return [];
                                }

                                return RfidCheckpoint::where('event_category_id', $participant->event_category_id)
                                    ->where('is_active', true)
                                    ->orderBy('checkpoint_order')
                                    ->get()
                                    ->mapWithKeys(function ($checkpoint) {
                                        return [
                                            $checkpoint->id => sprintf(
                                                '%s (%s) - %s km',
                                                $checkpoint->checkpoint_name,
                                                $checkpoint->checkpoint_type,
                                                $checkpoint->distance_km ?? '?'
                                            )
                                        ];
                                    });
                            })
                            ->required()
                            ->searchable(),

                        DateTimePicker::make('checkpoint_time')
                            ->label('Checkpoint Time')
                            ->required()
                            ->default(now())
                            ->seconds(),

                        Textarea::make('validation_notes')
                            ->label('Notes')
                            ->rows(2)
                            ->placeholder('Reason for manual entry or correction')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Calculated Times (Read-only)')
                    ->schema([
                        TextInput::make('elapsed_time')
                            ->label('Elapsed Time')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('split_time')
                            ->label('Split Time')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('position_at_checkpoint')
                            ->label('Position')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('validation_status')
                            ->label('Status')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(4)
                    ->visibleOn('edit'),
            ]);
    }
}
