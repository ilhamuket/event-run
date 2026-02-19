<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Response;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Unduh Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $transactions = Transaction::with(['participant', 'event', 'eventCategory', 'coupon'])
                        ->orderBy('created_at', 'desc')
                        ->get();

                    $csvData = [];
                    $csvData[] = [
                        'No Invoice',
                        'Referensi Tripay',
                        'Nama Peserta',
                        'Email',
                        'No HP',
                        'BIB',
                        'Event',
                        'Kategori',
                        'Kode Kupon',
                        'Harga',
                        'Diskon',
                        'Biaya Admin',
                        'Total',
                        'Status',
                        'Metode Bayar',
                        'Tanggal Bayar',
                        'Tanggal Dibuat',
                    ];

                    foreach ($transactions as $tx) {
                        $csvData[] = [
                            $tx->merchant_ref,
                            $tx->tripay_reference,
                            $tx->participant?->name,
                            $tx->participant?->email,
                            $tx->participant?->phone,
                            $tx->participant?->bib,
                            $tx->event?->name,
                            $tx->eventCategory?->name,
                            $tx->coupon?->code ?? '-',
                            $tx->amount,
                            $tx->discount_amount,
                            $tx->fee,
                            $tx->total_amount,
                            $tx->status,
                            $tx->payment_method,
                            $tx->paid_at?->format('Y-m-d H:i:s') ?? '-',
                            $tx->created_at?->format('Y-m-d H:i:s'),
                        ];
                    }

                    $filename = 'transaksi-' . now()->format('Y-m-d-His') . '.csv';

                    $handle = fopen('php://temp', 'r+');
                    fwrite($handle, "\xEF\xBB\xBF");
                    foreach ($csvData as $row) {
                        fputcsv($handle, $row, ';');
                    }
                    rewind($handle);
                    $content = stream_get_contents($handle);
                    fclose($handle);

                    return Response::streamDownload(function () use ($content) {
                        echo $content;
                    }, $filename, [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                    ]);
                }),
        ];
    }
}
