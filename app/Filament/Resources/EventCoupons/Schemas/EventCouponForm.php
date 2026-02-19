<?php

namespace App\Filament\Resources\EventCoupons\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventCouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->relationship('event', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('code')
                    ->label('Kode Kupon')
                    ->required()
                    ->maxLength(50)
                    ->dehydrateStateUsing(fn (string $state): string => strtoupper(trim($state))),
                TextInput::make('description')
                    ->label('Deskripsi'),
                TextInput::make('discount_percent')
                    ->label('Diskon (%)')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100)
                    ->suffix('%'),
                TextInput::make('max_usage')
                    ->label('Kuota Pemakaian')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('used_count')
                    ->label('Sudah Digunakan')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit'),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
