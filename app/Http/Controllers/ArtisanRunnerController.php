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
    ];

    public function index()
    {
        return view('dev.artisan-runner', [
            'commands' => self::ALLOWED_COMMANDS,
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

    public function status(Request $request)
    {
        if ($request->pin !== self::PIN) {
            return response()->json(['success' => false, 'output' => 'PIN salah.'], 403);
        }

        $pending = DB::table('jobs')
            ->where('queue', 'rfid')
            ->count();

        $failed = DB::table('failed_jobs')
            ->where('queue', 'rfid')
            ->count();

        $unprocessed = DB::table('rfid_raw_logs')
            ->whereNull('rfid_checkpoint_id')
            ->where('is_valid', true)
            ->count();

        // Cek worker process
        exec("ps aux | grep 'queue:work' | grep -v grep", $psOutput);
        $workerStatus = !empty($psOutput) ? '🟢 RUNNING' : '🔴 MATI';

        $output = implode("\n", [
            "Worker status             : {$workerStatus}",
            "Queue 'rfid' pending      : {$pending} jobs",
            "Queue 'rfid' failed       : {$failed} jobs",
            "Raw logs belum diproses   : {$unprocessed} records",
        ]);

        return response()->json(['success' => true, 'output' => $output]);
    }

    public function startWorker(Request $request)
    {
        if ($request->pin !== self::PIN) {
            return response()->json(['success' => false, 'output' => 'PIN salah.'], 403);
        }

        // Cek apakah worker sudah jalan
        exec("ps aux | grep 'queue:work' | grep -v grep", $psOutput);
        if (!empty($psOutput)) {
            return response()->json(['success' => true, 'output' => "Worker sudah jalan:\n" . implode("\n", $psOutput)]);
        }

        $artisan = base_path('artisan');
        $log     = storage_path('logs/queue-worker.log');

        exec("php {$artisan} queue:work --queue=rfid,default --tries=3 --sleep=3 >> {$log} 2>&1 &");

        sleep(2);

        exec("ps aux | grep 'queue:work' | grep -v grep", $verify);

        if (!empty($verify)) {
            return response()->json(['success' => true, 'output' => "✓ Worker berhasil dijalankan!\nLog: {$log}\n\n" . implode("\n", $verify)]);
        }

        return response()->json(['success' => false, 'output' => 'Gagal start worker. Cek permission server.']);
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

        exec("pkill -f 'queue:work'");
        sleep(1);

        exec("ps aux | grep 'queue:work' | grep -v grep", $verify);

        if (empty($verify)) {
            return response()->json(['success' => true, 'output' => '✓ Worker berhasil dihentikan.']);
        }

        return response()->json(['success' => false, 'output' => 'Gagal stop worker.']);
    }
}
