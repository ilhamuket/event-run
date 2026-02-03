<?php

namespace App\Filament\Resources\RfidValidatedTimes\Tables;

use App\Models\RfidValidatedTime;
use App\Services\RfidTimingService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RfidValidatedTimesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('checkpoint_time')
                    ->label('Time')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),

                TextColumn::make('participant.bib')
                    ->label('BIB')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('participant.name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('participant.category.name')
                    ->label('Category')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('checkpoint.checkpoint_name')
                    ->label('Checkpoint')
                    ->sortable(),

                TextColumn::make('checkpoint.checkpoint_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'start' => 'success',
                        'checkpoint' => 'warning',
                        'finish' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('elapsed_time')
                    ->label('Elapsed')
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('H:i:s') : '-'),

                TextColumn::make('split_time')
                    ->label('Split')
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('H:i:s') : '-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('position_at_checkpoint')
                    ->label('Position')
                    ->sortable(),

                TextColumn::make('validation_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'auto' => 'success',
                        'manual' => 'warning',
                        'corrected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('checkpoint_time', 'desc')
            ->poll('10s') // Auto-refresh for live monitoring
            ->filters([
                SelectFilter::make('participant.event_category_id')
                    ->label('Category')
                    ->relationship('participant.category', 'name'),

                SelectFilter::make('rfid_checkpoint_id')
                    ->label('Checkpoint')
                    ->relationship('checkpoint', 'checkpoint_name'),

                SelectFilter::make('validation_status')
                    ->label('Status')
                    ->options([
                        'auto' => 'Auto',
                        'manual' => 'Manual',
                        'corrected' => 'Corrected',
                    ]),

                Filter::make('checkpoint_time')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('checkpoint_time', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('checkpoint_time', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('correct')
                    ->label('Correct Time')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        DateTimePicker::make('new_time')
                            ->label('New Checkpoint Time')
                            ->required()
                            ->seconds()
                            ->default(fn (RfidValidatedTime $record) => $record->checkpoint_time),
                        Textarea::make('notes')
                            ->label('Reason for Correction')
                            ->required(),
                    ])
                    ->action(function (RfidValidatedTime $record, array $data) {
                        $service = app(RfidTimingService::class);
                        $service->correctTime(
                            $record->id,
                            $data['new_time'],
                            Auth::user()?->id ?? 1,
                            $data['notes']
                        );

                        Notification::make()
                            ->title('Time corrected successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
