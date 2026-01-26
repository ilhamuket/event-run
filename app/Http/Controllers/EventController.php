<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function home()
    {
         $event = Event::with([
            'heroImages' => function($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'categories' => function($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'racepackItems' => function($query) {
                $query->where('is_active', true)->orderBy('order');
            }
        ])
        ->where('is_published', true)
        ->firstOrFail();

        return view('event.home', compact('event'));
    }

    /**
     * LIST PESERTA (SEBELUM RACE)
     */
    public function participants(Event $event, Request $request)
    {
        $query = $event->participants()
            ->orderBy('bib');

        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('bib', 'like', "%{$request->q}%")
                  ->orWhere('name', 'like', "%{$request->q}%");
            });
        }

        $participants = $query->paginate(25)->withQueryString();

        return view('event.participants', compact('event', 'participants'));
    }

    /**
     * RACE RESULT (SETELAH FINISH)
     */
    public function results(Event $event, Request $request)
    {
        $query = $event->participants()
            ->whereNotNull('elapsed_time') // hanya yang finish
            ->orderBy('elapsed_time');     // ranking by time

        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('bib', 'like', "%{$request->q}%")
                  ->orWhere('name', 'like', "%{$request->q}%");
            });
        }

        $results = $query->paginate(25)->withQueryString();

        return view('event.results', compact('event', 'results'));
    }
}
