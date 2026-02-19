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
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bib_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gender')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'M' ? 'L' : 'P')
                    ->color(fn (string $state): string => $state === 'M' ? 'info' : 'danger'),
                TextColumn::make('age')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('jersey_size')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('community')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('latestTransaction.status')
                    ->label('Status Bayar')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'PAID' => 'success',
                        'UNPAID' => 'warning',
                        'EXPIRED' => 'gray',
                        'FAILED' => 'danger',
                        'REFUND' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'PAID' => 'Lunas',
                        'UNPAID' => 'Belum Bayar',
                        'EXPIRED' => 'Kadaluarsa',
                        'FAILED' => 'Gagal',
                        'REFUND' => 'Refund',
                        default => '-',
                    }),
                TextColumn::make('latestTransaction.total_amount')
                    ->label('Total Bayar')
                    ->numeric()
                    ->prefix('Rp ')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('emergency_contact_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('emergency_contact_phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('has_comorbid')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('elapsed_time')
                    ->time()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('general_position')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category_position')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
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

                SelectFilter::make('payment_status')
                    ->label('Status Bayar')
                    ->options([
                        'PAID' => 'Lunas',
                        'UNPAID' => 'Belum Bayar',
                        'EXPIRED' => 'Kadaluarsa',
                        'FAILED' => 'Gagal',
                        'REFUND' => 'Refund',
                    ])
                    ->query(function ($query, array $data) {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->whereHas('latestTransaction', function ($q) use ($data) {
                            $q->where('status', $data['value']);
                        });
                    }),

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
