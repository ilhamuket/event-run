<?php

namespace App\Filament\Resources\RfidValidatedTimes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RfidValidatedTimesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('participant.name')
                    ->searchable(),
                TextColumn::make('rfid_checkpoint_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rfid_raw_log_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('checkpoint_time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('elapsed_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('split_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('position_at_checkpoint')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('validation_status')
                    ->badge(),
                TextColumn::make('validated_by')
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
                //
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
