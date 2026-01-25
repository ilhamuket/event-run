<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function create(Event $event)
    {
        return view('event.register', compact('event'));
    }

    public function store(Event $event, Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'gender'   => 'required|in:M,F',
            'category' => 'nullable|string|max:100',
            'city'     => 'nullable|string|max:100',
        ]);

       DB::transaction(function () use ($request, $event) {

            $lastBib = Participant::where('event_id', $event->id)
                ->lockForUpdate()
                ->max('bib');

            $bib = $lastBib ? $lastBib + 1 : 1001;

            Participant::create([
                'event_id' => $event->id,
                'bib'      => $bib,
                'name'     => $request->name,
                'gender'   => $request->gender,
                'category' => $request->category,
                'city'     => $request->city,
            ]);
        });

        return redirect()
            ->route('event.register.success', $event->slug)
            ->with('success', 'Pendaftaran berhasil 🎉');
    }
}
