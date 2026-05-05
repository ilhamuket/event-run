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
        $catBindings    = [$event->id];
        $catWhereExtra  = '';

        if ($selectedCategory) {
            $cat = DB::selectOne(
                'SELECT id FROM event_categories WHERE event_id = ? AND slug = ? AND is_active = 1 LIMIT 1',
                [$event->id, $selectedCategory]
            );
            if ($cat) {
                $catWhereExtra  = 'AND ec.id = ?';
                $catBindings[]  = $cat->id;
            }
        }

        // ── 2. Ambil kategori yang relevan (urut by id) ──────────────────────
        $categories = DB::select("
            SELECT ec.id, ec.name
            FROM event_categories ec
            WHERE ec.event_id = ?
              AND ec.is_active = 1
              {$catWhereExtra}
            ORDER BY ec.id ASC
        ", $catBindings);

        // ── 3. Ambil semua finisher, siap dikelompokkan ──────────────────────
        $dataBindings   = [$event->id];
        $dataWhereExtra = '';

        if ($selectedCategory && isset($cat)) {
            $dataWhereExtra = 'AND p.event_category_id = ?';
            $dataBindings[] = $cat->id;
        }

        $allRows = DB::select("
            SELECT
                p.id                AS participant_id,
                p.event_category_id,
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
                ec.name             AS category_name,
                p.elapsed_time      AS chip_time,

                (
                    SELECT vt_s.checkpoint_time
                    FROM rfid_validated_times vt_s
                    JOIN rfid_checkpoints cp_s ON cp_s.id = vt_s.rfid_checkpoint_id
                    WHERE vt_s.participant_id = p.id
                      AND cp_s.checkpoint_type = 'start'
                      AND cp_s.is_active = 1
                    LIMIT 1
                ) AS start_time,

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
              AND p.elapsed_time IS NOT NULL
              {$dataWhereExtra}
            ORDER BY
                p.event_category_id ASC,
                p.gender ASC,
                p.elapsed_time ASC
        ", $dataBindings);

        // Index rows by [category_id][gender]
        $grouped = [];
        foreach ($allRows as $r) {
            $grouped[$r->event_category_id][$r->gender][] = $r;
        }

        // ── 4. Build spreadsheet ─────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hasil Finish');
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        // ── Judul event ──────────────────────────────────────────────────────
        $sheet->setCellValue('A1', $event->name);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->mergeCells('A1:O1');

        $sheet->setCellValue('A2', 'Hasil Finish — Diekspor: ' . now()->format('d M Y H:i'));
        $sheet->getStyle('A2')->getFont()->setItalic(true)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF6B7280'));
        $sheet->mergeCells('A2:O2');

        // Warna header kolom berdasarkan gender
        $colorMale   = 'FF1E3A5F'; // biru gelap
        $colorFemale = 'FF7B1E3A'; // merah gelap / maroon
        $colorCat    = 'FF374151'; // abu-abu gelap untuk header kategori

        $colHeaders = [
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
            'M' => 'Chip Time',
            'N' => 'Start Time (RFID)',
            'O' => 'Finish Time (RFID)',
        ];

        $currentRow = 4; // mulai dari row 4 (row 3 kosong sebagai spacer)

        // ── 5. Loop per kategori → per gender ────────────────────────────────
        foreach ($categories as $cat) {
            $catRows = $grouped[$cat->id] ?? [];

            // Cek apakah ada data sama sekali di kategori ini
            $hasAny = !empty($catRows['M']) || !empty($catRows['F']);
            if (!$hasAny) continue;

            // ── Section header: nama kategori ────────────────────────────────
            $sheet->setCellValue("A{$currentRow}", strtoupper($cat->name));
            $sheet->mergeCells("A{$currentRow}:O{$currentRow}");
            $sheet->getStyle("A{$currentRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . ltrim($colorCat, '#FF')]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle("A{$currentRow}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF374151']],
            ]);
            $sheet->getRowDimension($currentRow)->setRowHeight(22);
            $currentRow++;

            // ── Loop per gender dalam kategori ini ───────────────────────────
            foreach (['M', 'F'] as $gender) {
                $gRows = $catRows[$gender] ?? [];
                if (empty($gRows)) continue;

                $gLabel    = $gender === 'M' ? 'PRIA' : 'WANITA';
                $gColor    = $gender === 'M' ? $colorMale : $colorFemale;
                $gColorBg  = $gender === 'M' ? 'FFE8F0F8' : 'FFF8E8F0'; // stripe ringan untuk data rows

                // ── Sub-header: gender ────────────────────────────────────────
                $sheet->setCellValue("A{$currentRow}", "{$cat->name} — {$gLabel}");
                $sheet->mergeCells("A{$currentRow}:O{$currentRow}");
                $sheet->getStyle("A{$currentRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $gColor]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension($currentRow)->setRowHeight(20);
                $currentRow++;

                // ── Header kolom ──────────────────────────────────────────────
                foreach ($colHeaders as $col => $label) {
                    $sheet->setCellValue("{$col}{$currentRow}", $label);
                }
                $sheet->getStyle("A{$currentRow}:O{$currentRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $gColor]],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFFFFFF']],
                    ],
                ]);
                $sheet->getRowDimension($currentRow)->setRowHeight(28);
                $currentRow++;

                // ── Data rows ─────────────────────────────────────────────────
                foreach ($gRows as $i => $r) {
                    $isTop3  = ($i + 1) <= 3;
                    $bgColor = $isTop3 ? 'FFFFF7ED' : ($i % 2 === 0 ? 'FFFFFFFF' : ltrim($gColorBg, 'FF'));

                    $sheet->setCellValue("A{$currentRow}", $r->general_position  ?? '-');
                    $sheet->setCellValue("B{$currentRow}", $r->category_position ?? '-');
                    $sheet->setCellValue("C{$currentRow}", $r->bib);
                    $sheet->setCellValue("D{$currentRow}", $r->name);
                    $sheet->setCellValue("E{$currentRow}", $r->bib_name ?: $r->name);
                    $sheet->setCellValue("F{$currentRow}", $r->email);
                    $sheet->setCellValue("G{$currentRow}", $r->phone);
                    $sheet->setCellValue("H{$currentRow}", $r->gender === 'M' ? 'Pria' : 'Wanita');
                    $sheet->setCellValue("I{$currentRow}", $r->age);
                    $sheet->setCellValue("J{$currentRow}", $r->city);
                    $sheet->setCellValue("K{$currentRow}", $r->community);
                    $sheet->setCellValue("L{$currentRow}", $r->category_name);
                    $sheet->setCellValue("M{$currentRow}", $r->chip_time ?? '-');
                    $sheet->setCellValue("N{$currentRow}", $r->start_time  ? substr($r->start_time,  11, 8) : '-');
                    $sheet->setCellValue("O{$currentRow}", $r->finish_time ? substr($r->finish_time, 11, 8) : '-');

                    $sheet->getStyle("A{$currentRow}:O{$currentRow}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']],
                        ],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);

                    // Center: rank, bib, gender, usia, waktu
                    $sheet->getStyle("A{$currentRow}:C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("H{$currentRow}:I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("M{$currentRow}:O{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Bold chip time
                    $sheet->getStyle("M{$currentRow}")->getFont()->setBold(true);

                    $currentRow++;
                }

                // ── Summary subtotal per gender ───────────────────────────────
                $sheet->setCellValue("A{$currentRow}", "Total {$gLabel}");
                $sheet->setCellValue("B{$currentRow}", count($gRows));
                $sheet->mergeCells("C{$currentRow}:O{$currentRow}");
                $sheet->getStyle("A{$currentRow}:O{$currentRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF3F4F6']],
                    'borders' => [
                        'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FFD1D5DB']],
                    ],
                ]);
                $currentRow++;

                // Spacer antar gender
                $currentRow++;
            }

            // Spacer antar kategori
            $currentRow++;
        }

        // ── 6. Column widths ─────────────────────────────────────────────────
        $widths = [
            'A' => 12, 'B' => 14, 'C' => 8,  'D' => 25, 'E' => 25,
            'F' => 28, 'G' => 16, 'H' => 8,  'I' => 6,  'J' => 15,
            'K' => 18, 'L' => 20, 'M' => 12, 'N' => 18, 'O' => 18,
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Freeze baris judul
        $sheet->freezePane('A4');

        // ── 7. Stream ke browser ─────────────────────────────────────────────
        $filename = 'hasil-finish-' . $event->slug . '-' . now()->format('Ymd-His') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);

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
