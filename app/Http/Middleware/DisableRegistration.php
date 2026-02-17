<?php

namespace App\Http\Middleware;

use App\Models\Event;
use Closure;
use Illuminate\Http\Request;

class DisableRegistration
{
    public function handle(Request $request, Closure $next)
    {
        $slug = $request->route('slug') ?? $request->route('event');

        if (!$slug) {
            abort(403, 'Event tidak ditemukan.');
        }

        $event = Event::where('slug', $slug)->first();

        if (!$event) {
            abort(404, 'Event tidak ditemukan.');
        }

        if (!$event->is_registration_open) {
            abort(403, 'Pendaftaran belum atau sudah ditutup.');
        }

        if ($event->isQuotaFull()) {
            abort(403, 'Kuota peserta sudah penuh.');
        }

        return $next($request);
    }
}
