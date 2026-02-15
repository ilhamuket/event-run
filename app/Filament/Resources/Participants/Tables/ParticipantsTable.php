<?php

namespace App\Filament\Resources\Participants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ParticipantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event.name')
                    ->searchable(),
                TextColumn::make('bib')
                    ->searchable(),
                TextColumn::make('bib_name')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('gender')
                    ->badge(),
                TextColumn::make('age')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('country')
                    ->searchable(),
                TextColumn::make('province')
                    ->searchable(),
                TextColumn::make('regency')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('jersey_size')
                    ->searchable(),
                TextColumn::make('community')
                    ->searchable(),
                TextColumn::make('emergency_contact_name')
                    ->searchable(),
                TextColumn::make('emergency_contact_phone')
                    ->searchable(),
                IconColumn::make('has_comorbid')
                    ->boolean(),
                TextColumn::make('elapsed_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('general_position')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('category_position')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Event'),

                SelectFilter::make('event_category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Kategori'),

                SelectFilter::make('gender')
                    ->options([
                        'M' => 'Laki-laki',
                        'F' => 'Perempuan',
                    ])
                    ->label('Jenis Kelamin'),

                SelectFilter::make('jersey_size')
                    ->options([
                        'S' => 'S',
                        'M' => 'M',
                        'L' => 'L',
                        'XL' => 'XL',
                        'XXL' => 'XXL',
                    ])
                    ->label('Ukuran Jersey'),

                SelectFilter::make('city')
                    ->options(fn () => \App\Models\Participant::query()
                        ->whereNotNull('city')
                        ->distinct()
                        ->pluck('city', 'city')
                        ->toArray())
                    ->searchable()
                    ->label('Kota'),

                TernaryFilter::make('has_comorbid')
                    ->label('Komorbid')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
