<?php

namespace App\Filament\Resources\EventRacepackItems\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventRacepackItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('event_id')
                    ->required()
                    ->numeric(),
                TextInput::make('item_name')
                    ->required(),
                TextInput::make('item_number'),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('image_path')
                  ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->imagePreviewHeight('150')
                    ->openable()
                    ->downloadable()
                    ->required(),
                TextInput::make('features'),
                TextInput::make('badge_color')
                    ->required()
                    ->default('blue'),
                TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
