<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExportFinishController extends Controller
{
    /**
     * GET /events/{event}/export-finish
     * Query param opsional: ?category=slug
     */
    public function __invoke(Event $event, Request $request)
    {
        $selectedCategory = $request->category;

        // ── 1. Resolve category filter ───────────────────────────────────────
        $categoryFilter = '';
        $bindings       = [$event->id];

        if ($selectedCategory) {
            $cat = DB::selectOne(
                'SELECT id FROM event_categories WHERE event_id = ? AND slug = ? AND is_active = 1 LIMIT 1',
                [$event->id, $selectedCategory]
            );
            if ($cat) {
                $categoryFilter = 'AND p.event_category_id = ?';
                $bindings[]     = $cat->id;
            }
        }

        // ── 2. Ambil semua finisher dengan data lengkap ──────────────────────
        $rows = DB::select("
            SELECT
                p.general_position,
                p.category_position,
                p.bib,
                p.name,
                p.bib_name,
                p.email,
                p.phone,
                p.gender,
                p.age,
                p.city,
                p.community,
                ec.name            AS category_name,
                p.elapsed_time     AS chip_time,
                p.gun_elapsed_time AS gun_time,

                -- Start RFID time
                (
                    SELECT vt_s.checkpoint_time
                    FROM rfid_validated_times vt_s
                    JOIN rfid_checkpoints cp_s ON cp_s.id = vt_s.rfid_checkpoint_id
                    WHERE vt_s.participant_id = p.id
                      AND cp_s.checkpoint_type = 'start'
                      AND cp_s.is_active = 1
                    LIMIT 1
                ) AS start_time,

                -- Finish RFID time
                (
                    SELECT vt_f.checkpoint_time
                    FROM rfid_validated_times vt_f
                    JOIN rfid_checkpoints cp_f ON cp_f.id = vt_f.rfid_checkpoint_id
                    WHERE vt_f.participant_id = p.id
                      AND cp_f.checkpoint_type = 'finish'
                      AND cp_f.is_active = 1
                    LIMIT 1
                ) AS finish_time

            FROM participants p
            LEFT JOIN event_categories ec ON ec.id = p.event_category_id
            WHERE p.event_id = ?
              AND p.gun_elapsed_time IS NOT NULL
              {$categoryFilter}
            ORDER BY p.gun_elapsed_time ASC
        ", $bindings);

        // ── 3. Build spreadsheet ─────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hasil Finish');

        // Font default
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        // ── Header event info ────────────────────────────────────────────────
        $sheet->setCellValue('A1', $event->name);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->setCellValue('A2', 'Hasil Finish — Diekspor: ' . now()->format('d M Y H:i'));
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setColor(
            (new \PhpOffice\PhpSpreadsheet\Style\Color('FF6B7280'))
        );
        $sheet->mergeCells('A1:N1');
        $sheet->mergeCells('A2:N2');

        // ── Header kolom (row 4) ─────────────────────────────────────────────
        $headers = [
            'A' => 'Rank Overall',
            'B' => 'Rank Kategori',
            'C' => 'BIB',
            'D' => 'Nama',
            'E' => 'Nama BIB',
            'F' => 'Email',
            'G' => 'No. HP',
            'H' => 'Gender',
            'I' => 'Usia',
            'J' => 'Kota',
            'K' => 'Komunitas',
            'L' => 'Kategori',
            'M' => 'Gun Time',
            'N' => 'Chip Time',
            'O' => 'Start Time (RFID)',
            'P' => 'Finish Time (RFID)',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}4", $label);
        }

        // Style header row
        $headerRange = 'A4:P4';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF991B1B'], // merah gelap
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFFFFFFF'],
                ],
            ],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(30);

        // ── Data rows ────────────────────────────────────────────────────────
        $row = 5;
        foreach ($rows as $i => $r) {
            $isTop3  = ($i + 1) <= 3;
            $bgColor = $isTop3 ? 'FFFFF7ED' : 'FFFFFFFF'; // orange muda untuk top 3

            $sheet->setCellValue("A{$row}", $r->general_position ?? ($i + 1));
            $sheet->setCellValue("B{$row}", $r->category_position ?? '-');
            $sheet->setCellValue("C{$row}", $r->bib);
            $sheet->setCellValue("D{$row}", $r->name);
            $sheet->setCellValue("E{$row}", $r->bib_name ?: $r->name);
            $sheet->setCellValue("F{$row}", $r->email);
            $sheet->setCellValue("G{$row}", $r->phone);
            $sheet->setCellValue("H{$row}", $r->gender === 'M' ? 'Pria' : 'Wanita');
            $sheet->setCellValue("I{$row}", $r->age);
            $sheet->setCellValue("J{$row}", $r->city);
            $sheet->setCellValue("K{$row}", $r->community);
            $sheet->setCellValue("L{$row}", $r->category_name);
            $sheet->setCellValue("M{$row}", $r->gun_time   ?? '-');
            $sheet->setCellValue("N{$row}", $r->chip_time  ?? '-');
            $sheet->setCellValue("O{$row}", $r->start_time  ? date('H:i:s', strtotime($r->start_time))  : '-');
            $sheet->setCellValue("P{$row}", $r->finish_time ? date('H:i:s', strtotime($r->finish_time)) : '-');

            // Row styling
            $sheet->getStyle("A{$row}:P{$row}")->applyFromArray([
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => ltrim($bgColor, '#')],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FFE5E7EB'],
                    ],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Center kolom angka & waktu
            $sheet->getStyle("A{$row}:C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$row}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("M{$row}:P{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Bold gun time
            $sheet->getStyle("M{$row}")->getFont()->setBold(true);

            $row++;
        }

        // ── Summary row ──────────────────────────────────────────────────────
        $sheet->setCellValue("A{$row}", 'Total Finisher');
        $sheet->setCellValue("B{$row}", count($rows));
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:P{$row}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF3F4F6');

        // ── Column widths ────────────────────────────────────────────────────
        $widths = [
            'A' => 12, 'B' => 14, 'C' => 8,  'D' => 25, 'E' => 25,
            'F' => 28, 'G' => 16, 'H' => 8,  'I' => 6,  'J' => 15,
            'K' => 18, 'L' => 20, 'M' => 12, 'N' => 12, 'O' => 18, 'P' => 18,
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Freeze header
        $sheet->freezePane('A5');

        // ── Stream ke browser ────────────────────────────────────────────────
        $filename = 'hasil-finish-' . $event->slug . '-' . now()->format('Ymd-His') . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            function () use ($writer) { $writer->save('php://output'); },
            $filename,
            [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control'       => 'max-age=0',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]
        );
    }
}
