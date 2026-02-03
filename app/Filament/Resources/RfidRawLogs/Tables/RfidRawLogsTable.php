<?php

namespace App\Filament\Resources\RfidRawLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RfidRawLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('scanned_at')
                    ->label('Scanned At')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),

                TextColumn::make('event.name')
                    ->label('Event')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('checkpoint.checkpoint_name')
                    ->label('Checkpoint')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('rfid_tag')
                    ->label('RFID Tag')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('bib')
                    ->label('BIB')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('reader_id')
                    ->label('Reader')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('signal_strength')
                    ->label('Signal')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_valid')
                    ->label('Valid')
                    ->boolean(),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->notes)
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('scanned_at', 'desc')
            ->poll('5s') // Auto-refresh every 5 seconds
            ->filters([
                SelectFilter::make('event_id')
                    ->label('Event')
                    ->relationship('event', 'name'),

                SelectFilter::make('rfid_checkpoint_id')
                    ->label('Checkpoint')
                    ->relationship('checkpoint', 'checkpoint_name'),

                TernaryFilter::make('is_valid')
                    ->label('Valid Status'),

                Filter::make('scanned_at')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('scanned_at', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('scanned_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                // Read-only, no bulk actions
            ]);
    }
}
