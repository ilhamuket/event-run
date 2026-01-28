<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\EventCategory;

class RegistrationController extends Controller
{
    public function create(Event $event)
    {
        $categories = EventCategory::where('event_id', $event->id)
        ->active()
        ->ordered()
        ->get();


        return view('event.register', compact('event', 'categories'));
    }

    public function store(Event $event, Request $request)
    {

        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'bib_name'              => 'required|string|max:20',
            'gender'                => 'required|in:M,F',
            'event_category_id'     => 'required|exists:event_categories,id',
            'city'                  => 'nullable|string|max:100',
            'email'                 => 'nullable|email|max:255',
            'phone'                 => 'nullable|string|max:20',
            'age'                   => 'nullable|integer|min:5|max:100',
            'jersey_size'           => 'nullable|string|max:5',
            'community'             => 'nullable|string|max:100',
            'has_comorbid'          => 'nullable|boolean',
            'comorbid_details'      => 'nullable|string|max:255',
            'emergency_contact_name'=> 'nullable|string|max:255',
            'emergency_contact_phone'=> 'nullable|string|max:20',
        ]);

        DB::transaction(function () use ($event, $validated) {

            $lastBib = Participant::where('event_id', $event->id)
                ->lockForUpdate()
                ->max('bib');

            $bib = $lastBib ? $lastBib + 1 : 1001;

            Participant::create(array_merge($validated, [
                'event_id' => $event->id,
                'bib'      => $bib,
            ]));
        });

        return redirect()
            ->route('event.register.success', $event->slug)
            ->with('success', 'Pendaftaran berhasil 🎉');
    }
}
