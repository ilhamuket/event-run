<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ArtisanRunnerController extends Controller
{
    private const PIN = '17200024'; // ganti PIN di sini

    private const ALLOWED_COMMANDS = [
        'route:clear',
        'route:cache',
        'config:clear',
        'config:cache',
        'cache:clear',
        'optimize:clear',
        'optimize',
        'queue:restart',
    ];

    public function index()
    {
        return view('dev.artisan-runner', [
            'commands' => self::ALLOWED_COMMANDS,
        ]);
    }

    public function run(Request $request)
    {
        $request->validate([
            'pin'     => 'required|string',
            'command' => 'required|string',
        ]);

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
}
