<?php

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Pages\ViewTransaction;
use App\Filament\Resources\Transactions\Tables\TransactionsTable;
use App\Models\Transaction;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $recordTitleAttribute = 'merchant_ref';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('merchant_ref')
                    ->label('No. Invoice'),
                TextEntry::make('tripay_reference')
                    ->label('Referensi Tripay')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PAID' => 'success',
                        'UNPAID' => 'warning',
                        'EXPIRED' => 'gray',
                        'FAILED' => 'danger',
                        'REFUND' => 'info',
                        default => 'gray',
                    }),
                TextEntry::make('payment_method')
                    ->label('Metode Bayar'),
                TextEntry::make('participant.name')
                    ->label('Nama Peserta'),
                TextEntry::make('participant.email')
                    ->label('Email'),
                TextEntry::make('participant.phone')
                    ->label('No. HP'),
                TextEntry::make('participant.bib')
                    ->label('BIB')
                    ->placeholder('-'),
                TextEntry::make('event.name')
                    ->label('Event'),
                TextEntry::make('eventCategory.name')
                    ->label('Kategori'),
                TextEntry::make('coupon.code')
                    ->label('Kode Kupon')
                    ->placeholder('-'),
                TextEntry::make('amount')
                    ->label('Harga Tiket')
                    ->numeric()
                    ->prefix('Rp '),
                TextEntry::make('discount_amount')
                    ->label('Diskon')
                    ->numeric()
                    ->prefix('Rp '),
                TextEntry::make('fee')
                    ->label('Biaya Admin')
                    ->numeric()
                    ->prefix('Rp '),
                TextEntry::make('total_amount')
                    ->label('Total Bayar')
                    ->numeric()
                    ->prefix('Rp '),
                TextEntry::make('note')
                    ->label('Catatan')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('paid_at')
                    ->label('Tanggal Bayar')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('expired_at')
                    ->label('Kadaluarsa')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return TransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransactions::route('/'),
            'view' => ViewTransaction::route('/{record}'),
        ];
    }
}
