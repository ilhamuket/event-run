<?php

namespace App\Filament\Resources\RfidCheckpoints\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RfidCheckpointsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('eventCategory.event.name')
                    ->label('Event')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('eventCategory.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('checkpoint_name')
                    ->label('Checkpoint')
                    ->searchable(),

                TextColumn::make('checkpoint_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'start' => 'success',
                        'checkpoint' => 'warning',
                        'finish' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('checkpoint_order')
                    ->label('Order')
                    ->sortable(),

                TextColumn::make('distance_km')
                    ->label('Distance')
                    ->suffix(' km')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('cutoff_time')
                    ->label('Cut-off')
                    ->time('H:i')
                    ->placeholder('-'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('validated_times_count')
                    ->label('Passed')
                    ->counts('validatedTimes')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('checkpoint_order')
            ->filters([
                SelectFilter::make('event_category_id')
                    ->label('Category')
                    ->relationship('eventCategory', 'name'),

                SelectFilter::make('checkpoint_type')
                    ->label('Type')
                    ->options([
                        'start' => 'Start',
                        'checkpoint' => 'Checkpoint',
                        'finish' => 'Finish',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Active'),
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
