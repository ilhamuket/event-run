<?php

namespace App\Filament\Resources\RfidCheckpoints\Schemas;

use App\Models\EventCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RfidCheckpointForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Checkpoint Information')
                    ->schema([
                        Select::make('event_category_id')
                            ->label('Event Category')
                            ->options(function () {
                                return EventCategory::with('event')
                                    ->get()
                                    ->mapWithKeys(function ($category) {
                                        return [$category->id => $category->event->name . ' - ' . $category->name];
                                    });
                            })
                            ->required()
                            ->searchable(),

                        Select::make('checkpoint_type')
                            ->label('Type')
                            ->options([
                                'start' => 'Start',
                                'checkpoint' => 'Checkpoint (Intermediate)',
                                'finish' => 'Finish',
                            ])
                            ->required(),

                        TextInput::make('checkpoint_name')
                            ->label('Checkpoint Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Start Line, CP1, Finish'),

                        TextInput::make('checkpoint_order')
                            ->label('Order')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->helperText('0 = Start, higher numbers for later checkpoints'),

                        TextInput::make('distance_km')
                            ->label('Distance (KM)')
                            ->numeric()
                            ->step(0.01)
                            ->placeholder('e.g., 10.5'),

                        TimePicker::make('cutoff_time')
                            ->label('Cut-off Time')
                            ->helperText('Maximum allowed time to reach this checkpoint'),
                    ])
                    ->columns(2),

                Section::make('Location')
                    ->schema([
                        TextInput::make('location_name')
                            ->label('Location Name')
                            ->maxLength(255)
                            ->placeholder('e.g., Alun-alun Kota'),

                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->step(0.0000001),

                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->step(0.0000001),
                    ])
                    ->columns(3),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
            ]);
    }
}
