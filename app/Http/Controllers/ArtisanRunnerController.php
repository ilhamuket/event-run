<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ArtisanRunnerController extends Controller
{
    private const PIN = '17200024';

    private const ALLOWED_COMMANDS = [
        'route:clear',
        'route:cache',
        'config:clear',
        'config:cache',
        'cache:clear',
        'optimize:clear',
        'optimize',
        'queue:restart',
        'queue:work --queue=rfid,default --tries=3',
        'queue:work --queue=positions,default --tries=3',
    ];

    public function index()
    {
        $events = DB::select(
            'SELECT id, name FROM events WHERE is_published = 1 ORDER BY id DESC'
        );

        return view('dev.artisan-runner', [
            'commands' => self::ALLOWED_COMMANDS,
            'events'   => $events,
        ]);
    }

    public function run(Request $request)
    {
        $request->validate(['pin' => 'required|string', 'command' => 'required|string']);

        if ($request->pin !== self::PIN) {
            return response()->json(['success' => false, 'output' => 'PIN salah.'], 403);
        }

        if (!in_array($request->command, self::ALLOWED_COMMANDS)) {
            return response()->json(['success' => false, 'output' => 'Command tidak diizinkan.'], 403);
        }

        try {
            Artisan::call($request->command);
            $output = Artisan::output() ?: 'Command selesai (tidak ada output).';
            return response()->json(['success' => true, 'output' => $output]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'output' => $e->getMessage()], 500);
        }
    }

    public function backfillStart(Request $request)
    {
        $request->validate([
            'pin'      => 'required|string',
            'event_id' => 'required|integer',
            'dry_run'  => 'required|boolean',
            'spread'   => 'nullable|integer|min:1|max:600',
        ]);

        if ($request->pin !== self::PIN) {
            return response()->json(['success' => false, 'output' => 'PIN salah.'], 403);
        }

        $eventId = (int) $request->event_id;
        $spread  = (int) ($request->spread ?? 60);
        $dryRun  = (bool) $request->dry_run;

        $event = DB::selectOne(
            'SELECT id, name FROM events WHERE id = ? AND is_published = 1 LIMIT 1',
            [$eventId]
        );
        if (!$event) {
            return response()->json(['success' => false, 'output' => "Event ID {$eventId} tidak ditemukan."], 422);
        }

        $params = [
            'event_id' => $eventId,
            '--spread'  => $spread,
        ];
        if ($dryRun) {
            $params['--dry-run'] = true;
        }

        try {
            Artisan::call('rfid:backfill-start', $params + ['--no-interaction' => true]);
            $output = Artisan::output() ?: 'Command selesai (tidak ada output).';

            return response()->json([
                'success' => true,
                'output'  => ($dryRun ? "[DRY RUN] " : "[EKSEKUSI] ") . "Event: {$event->name}\n\n" . $output,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'output' => $e->getMessage()], 500);
        }
    }

    public function normalizeFinish(Request $request)
    {
        $request->validate([
            'pin'         => 'required|string',
            'event_id'    => 'required|integer',
            'dry_run'     => 'required|boolean',
            'force'       => 'nullable|boolean',
            'category_id' => 'nullable|integer',
        ]);

        if ($request->pin !== self::PIN) {
            return response()->json(['success' => false, 'output' => 'PIN salah.'], 403);
        }

        $eventId    = (int) $request->event_id;
        $dryRun     = (bool) $request->dry_run;
        $force      = (bool) ($request->force ?? false);
        $categoryId = $request->category_id ? (int) $request->category_id : null;

        $event = DB::selectOne(
            'SELECT id, name FROM events WHERE id = ? AND is_published = 1 LIMIT 1',
            [$eventId]
        );
        if (!$event) {
            return response()->json(['success' => false, 'output' => "Event ID {$eventId} tidak ditemukan."], 422);
        }

        $params = ['event_id' => $eventId];

        if ($dryRun)     $params['--dry-run']  = true;
        if ($force)      $params['--force']     = true;
        if ($categoryId) $params['--category']  = $categoryId;

        try {
            Artisan::call('rfid:normalize-finish', $params + ['--no-interaction' => true]);
            $output = Artisan::output() ?: 'Command selesai (tidak ada output).';

            $prefix = $dryRun ? '[DRY RUN] ' : '[EKSEKUSI] ';
            $suffix = $force  ? ' [--force]'  : '';

            return response()->json([
                'success' => true,
                'output'  => "{$prefix}Normalize Finish — Event: {$event->name}{$suffix}\n\n{$output}",
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'output' => $e->getMessage()], 500);
        }
    }

    public function status(Request $request)
    {
        if ($request->pin !== self::PIN) {
            return response()->json(['success' => false, 'output' => 'PIN salah.'], 403);
        }

        // Queue counts
        $pendingRfid      = DB::table('jobs')->where('queue', 'rfid')->count();
        $pendingPositions = DB::table('jobs')->where('queue', 'positions')->count();
        $pendingDefault   = DB::table('jobs')->where('queue', 'default')->count();

        $failedRfid      = DB::table('failed_jobs')->where('queue', 'rfid')->count();
        $failedPositions = DB::table('failed_jobs')->where('queue', 'positions')->count();

        $unprocessed = DB::table('rfid_raw_logs')
            ->whereNull('rfid_checkpoint_id')
            ->where('is_valid', true)
            ->count();

        // Cek worker per queue
        exec("ps aux | grep 'queue:work' | grep -v grep", $psAll);

        $workerRfid      = collect($psAll)->filter(fn($l) => str_contains($l, 'rfid'))->count();
        $workerPositions = collect($psAll)->filter(fn($l) => str_contains($l, 'positions'))->count();

        $rfidStatus      = $workerRfid      > 0 ? "🟢 RUNNING ({$workerRfid} process)" : '🔴 MATI';
        $positionsStatus = $workerPositions > 0 ? "🟢 RUNNING ({$workerPositions} process)" : '🔴 MATI';

        $output = implode("\n", [
            "═══════════════════════════════════",
            "  WORKER STATUS",
            "═══════════════════════════════════",
            "  rfid worker      : {$rfidStatus}",
            "  positions worker : {$positionsStatus}",
            "",
            "═══════════════════════════════════",
            "  QUEUE PENDING",
            "═══════════════════════════════════",
            "  rfid             : {$pendingRfid} jobs",
            "  positions        : {$pendingPositions} jobs",
            "  default          : {$pendingDefault} jobs",
            "",
            "═══════════════════════════════════",
            "  FAILED JOBS",
            "═══════════════════════════════════",
            "  rfid             : {$failedRfid} jobs",
            "  positions        : {$failedPositions} jobs",
            "",
            "═══════════════════════════════════",
            "  RAW LOGS belum diproses: {$unprocessed} records",
            "═══════════════════════════════════",
        ]);

        return response()->json(['success' => true, 'output' => $output]);
    }

    /**
     * Start semua worker sekaligus:
     * - 3x rfid worker (untuk 1300 peserta, supaya paralel)
     * - 2x positions worker
     *
     * Masing-masing worker jalan sebagai background process terpisah.
     */
    public function startWorker(Request $request)
    {
        if ($request->pin !== self::PIN) {
            return response()->json(['success' => false, 'output' => 'PIN salah.'], 403);
        }

        $artisan = base_path('artisan');
        $log     = storage_path('logs/queue-worker.log');

        exec("ps aux | grep 'queue:work' | grep -v grep", $existing);
        $hasRfid      = collect($existing)->filter(fn($l) => str_contains($l, 'rfid'))->count();
        $hasPositions = collect($existing)->filter(fn($l) => str_contains($l, 'positions'))->count();

        $started = [];

        // ── rfid workers (3 process paralel) ────────────────────────────────
        $rfidTarget = 3;
        for ($i = $hasRfid; $i < $rfidTarget; $i++) {
            exec("php {$artisan} queue:work --queue=rfid,default --tries=3 --sleep=1  >> {$log} 2>&1 &");
            $started[] = "rfid worker #" . ($i + 1);
        }

        // ── positions workers (2 process paralel) ────────────────────────────
        $positionsTarget = 2;
        for ($i = $hasPositions; $i < $positionsTarget; $i++) {
            exec("php {$artisan} queue:work --queue=positions,default --tries=3 --sleep=1  >> {$log} 2>&1 &");
            $started[] = "positions worker #" . ($i + 1);
        }

        sleep(2);

        exec("ps aux | grep 'queue:work' | grep -v grep", $verify);
        $verifyRfid      = collect($verify)->filter(fn($l) => str_contains($l, 'rfid'))->count();
        $verifyPositions = collect($verify)->filter(fn($l) => str_contains($l, 'positions'))->count();

        if (!empty($started)) {
            $output = "✓ Worker dijalankan:\n  " . implode("\n  ", $started) . "\n\n";
        } else {
            $output = "ℹ Semua worker sudah jalan, tidak ada yang ditambah.\n\n";
        }

        $output .= implode("\n", [
            "Status sekarang:",
            "  rfid worker      : {$verifyRfid}/{$rfidTarget} process",
            "  positions worker : {$verifyPositions}/{$positionsTarget} process",
            "  Log              : {$log}",
        ]);

        $allOk = $verifyRfid >= $rfidTarget && $verifyPositions >= $positionsTarget;

        return response()->json(['success' => $allOk, 'output' => $output]);
    }

    public function stopWorker(Request $request)
    {
        if ($request->pin !== self::PIN) {
            return response()->json(['success' => false, 'output' => 'PIN salah.'], 403);
        }

        exec("ps aux | grep 'queue:work' | grep -v grep", $psOutput);
        if (empty($psOutput)) {
            return response()->json(['success' => true, 'output' => 'Worker memang tidak jalan.']);
        }

        $count = count($psOutput);
        exec("pkill -f 'queue:work'");
        sleep(1);

        exec("ps aux | grep 'queue:work' | grep -v grep", $verify);

        if (empty($verify)) {
            return response()->json(['success' => true, 'output' => "✓ {$count} worker berhasil dihentikan."]);
        }

        return response()->json(['success' => false, 'output' => 'Gagal stop beberapa worker. Coba lagi atau kill manual.']);
    }
}
