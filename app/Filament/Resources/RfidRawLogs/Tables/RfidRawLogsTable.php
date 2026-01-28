<?php

namespace App\Filament\Resources\RfidRawLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RfidRawLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event.name')
                    ->searchable(),
                TextColumn::make('rfid_checkpoint_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rfid_tag')
                    ->searchable(),
                TextColumn::make('bib')
                    ->searchable(),
                TextColumn::make('scanned_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('reader_id')
                    ->searchable(),
                TextColumn::make('signal_strength')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_valid')
                    ->boolean(),
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
