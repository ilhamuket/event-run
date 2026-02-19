<?php

namespace App\Filament\Resources\Participants\Pages;

use App\Filament\Resources\Participants\ParticipantResource;
use App\Models\Participant;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Response;

class ListParticipants extends ListRecords
{
    protected static string $resource = ParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Unduh Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $participants = Participant::with(['event', 'category', 'latestTransaction'])
                        ->orderBy('created_at', 'desc')
                        ->get();

                    $csvData = [];
                    $csvData[] = [
                        'BIB',
                        'Nama',
                        'Nama BIB',
                        'Kategori',
                        'Jenis Kelamin',
                        'Umur',
                        'Email',
                        'No HP',
                        'Ukuran Jersey',
                        'Kota',
                        'Komunitas',
                        'Kontak Darurat',
                        'No HP Darurat',
                        'Status Bayar',
                        'Total Bayar',
                        'No Invoice',
                        'Event',
                        'Tanggal Daftar',
                    ];

                    foreach ($participants as $p) {
                        $tx = $p->latestTransaction;
                        $csvData[] = [
                            $p->bib,
                            $p->name,
                            $p->bib_name,
                            $p->category?->name,
                            $p->gender === 'M' ? 'Laki-laki' : 'Perempuan',
                            $p->age,
                            $p->email,
                            $p->phone,
                            $p->jersey_size,
                            $p->city,
                            $p->community,
                            $p->emergency_contact_name,
                            $p->emergency_contact_phone,
                            $tx?->status ?? '-',
                            $tx?->total_amount ?? 0,
                            $tx?->merchant_ref ?? '-',
                            $p->event?->name,
                            $p->created_at?->format('Y-m-d H:i:s'),
                        ];
                    }

                    $filename = 'peserta-' . now()->format('Y-m-d-His') . '.csv';

                    $handle = fopen('php://temp', 'r+');
                    // BOM for Excel UTF-8 compatibility
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
            CreateAction::make(),
        ];
    }
}
