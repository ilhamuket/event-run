<?php

namespace App\Filament\Resources\Transactions\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('merchant_ref')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('participant.name')
                    ->label('Peserta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('participant.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('eventCategory.name')
                    ->label('Kategori')
                    ->badge()
                    ->searchable(),
                TextColumn::make('coupon.code')
                    ->label('Kupon')
                    ->badge()
                    ->color('warning')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('amount')
                    ->label('Harga')
                    ->numeric()
                    ->prefix('Rp ')
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->label('Diskon')
                    ->numeric()
                    ->prefix('Rp ')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('fee')
                    ->label('Admin')
                    ->numeric()
                    ->prefix('Rp ')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->numeric()
                    ->prefix('Rp ')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PAID' => 'success',
                        'UNPAID' => 'warning',
                        'EXPIRED' => 'gray',
                        'FAILED' => 'danger',
                        'REFUND' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'PAID' => 'Lunas',
                        'UNPAID' => 'Belum Bayar',
                        'EXPIRED' => 'Kadaluarsa',
                        'FAILED' => 'Gagal',
                        'REFUND' => 'Refund',
                        default => $state,
                    }),
                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('paid_at')
                    ->label('Dibayar')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
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
                    ->relationship('eventCategory', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Kategori'),

                SelectFilter::make('status')
                    ->options([
                        'PAID' => 'Lunas',
                        'UNPAID' => 'Belum Bayar',
                        'EXPIRED' => 'Kadaluarsa',
                        'FAILED' => 'Gagal',
                        'REFUND' => 'Refund',
                    ])
                    ->label('Status'),

                TernaryFilter::make('has_coupon')
                    ->label('Pakai Kupon')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('event_coupon_id'),
                        false: fn ($query) => $query->whereNull('event_coupon_id'),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
