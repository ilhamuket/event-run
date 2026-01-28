<?php

namespace App\Filament\Resources\EventCategories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->relationship('event', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('distance')
                    ->required(),
                TextInput::make('level')
                    ->required(),
                TextInput::make('elevation'),
                TextInput::make('terrain'),
                TextInput::make('cut_off_time'),
                FileUpload::make('course_map_image')
                     ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->imagePreviewHeight('150')
                    ->openable()
                    ->downloadable()
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('color_from')
                    ->required()
                    ->default('blue-400'),
                TextInput::make('color_to')
                    ->required()
                    ->default('blue-600'),
                TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
