<?php

namespace App\Filament\Resources\ParticipantRfidMappings\Tables;

use App\Models\ParticipantRfidMapping;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ParticipantRfidMappingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('participant.bib')
                    ->label('BIB')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('participant.name')
                    ->label('Participant')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('participant.event.name')
                    ->label('Event')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('participant.category.name')
                    ->label('Category')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('rfid_tag')
                    ->label('RFID Tag')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('RFID tag copied'),

                TextColumn::make('assigned_at')
                    ->label('Assigned')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('assignedBy.name')
                    ->label('Assigned By')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('assigned_at', 'desc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status'),

                SelectFilter::make('event')
                    ->label('Event')
                    ->relationship('participant.event', 'name'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('deactivate')
                    ->label('Deactivate')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ParticipantRfidMapping $record) => $record->is_active)
                    ->action(fn (ParticipantRfidMapping $record) => $record->update(['is_active' => false])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('deactivate_bulk')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
                ]),
            ]);
    }
}
