<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Models\RfidCheckpoint;

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
            ->whereHas('paidTransaction')
            ->with('category')
            ->orderBy('bib');

        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('bib', 'like', "%{$request->q}%")
                ->orWhere('name', 'like', "%{$request->q}%")
                ->orWhere('bib_name', 'like', "%{$request->q}%")
                ->orWhere('email', 'like', "%{$request->q}%")
                ->orWhere('community', 'like', "%{$request->q}%");
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
            ->whereHas('paidTransaction')
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

    /**
     * LIVE RACE TRACKING
     * Menampilkan peserta berdasarkan checkpoint terakhir yang dilewati.
     */
    public function live(Event $event, Request $request)
    {
        $selectedCategory = $request->category;

        // Get all active categories
        $categories = $event->categories()->active()->ordered()->get();

        // Base query: paid participants
        $participantsQuery = $event->participants()
            ->whereHas('paidTransaction')
            ->with(['category', 'validatedTimes.checkpoint']);

        // Filter by category if selected
        if ($selectedCategory) {
            $category = $categories->firstWhere('slug', $selectedCategory);
            if ($category) {
                $participantsQuery->where('event_category_id', $category->id);
            }
        }

        // Search filter
        if ($request->q) {
            $participantsQuery->where(function ($q) use ($request) {
                $q->where('bib', 'like', "%{$request->q}%")
                ->orWhere('name', 'like', "%{$request->q}%")
                ->orWhere('bib_name', 'like', "%{$request->q}%");
            });
        }

        $participants = $participantsQuery->get();

        // Get all checkpoints for relevant categories (ordered by checkpoint_order DESC)
        $categoryIds = $selectedCategory && isset($category)
            ? [$category->id]
            : $categories->pluck('id')->toArray();

        $allCheckpoints = RfidCheckpoint::whereIn('event_category_id', $categoryIds)
            ->where('is_active', true)
            ->orderBy('checkpoint_order', 'desc')
            ->get();

        // Group participants by their LATEST checkpoint
        $checkpointGroups = collect();
        $notStarted = collect();
        $participantsPlaced = collect(); // track placed participant IDs

        // Build checkpoint groups structure (ordered: finish first, then checkpoints desc, then start last)
        foreach ($allCheckpoints as $cp) {
            $key = $cp->checkpoint_type === 'checkpoint'
                ? 'cp_' . $cp->id
                : $cp->checkpoint_type;

            if (!$checkpointGroups->has($key)) {
                $checkpointGroups[$key] = [
                    'checkpoint' => $cp,
                    'participants' => collect(),
                ];
            }
        }

        // For each participant, find their latest checkpoint and place them
        foreach ($participants as $participant) {
            $validatedTimes = $participant->validatedTimes->sortByDesc(function ($vt) {
                return $vt->checkpoint?->checkpoint_order ?? 0;
            });

            if ($validatedTimes->isEmpty()) {
                $notStarted->push($participant);
                continue;
            }

            // Get the latest validated time (highest checkpoint_order)
            $latestVt = $validatedTimes->first();
            $latestCheckpoint = $latestVt->checkpoint;

            if (!$latestCheckpoint) {
                $notStarted->push($participant);
                continue;
            }

            $key = $latestCheckpoint->checkpoint_type === 'checkpoint'
                ? 'cp_' . $latestCheckpoint->id
                : $latestCheckpoint->checkpoint_type;

            if ($checkpointGroups->has($key)) {
                $checkpointGroups[$key]['participants']->push([
                    'participant' => $participant,
                    'validated_time' => $latestVt,
                ]);
            }
        }

        // Sort participants within each group
        foreach ($checkpointGroups as $key => &$group) {
            if ($key === 'finish') {
                // Finish: sort by elapsed_time (fastest first)
                $group['participants'] = $group['participants']->sortBy(function ($item) {
                    return $item['validated_time']->elapsed_time ?? $item['participant']->elapsed_time ?? '99:99:99';
                })->values();
            } else {
                // Other checkpoints: sort by checkpoint_time (earliest first = leading)
                $group['participants'] = $group['participants']->sortBy(function ($item) {
                    return $item['validated_time']->checkpoint_time;
                })->values();
            }
        }
        unset($group);

        // Sort not-started by BIB
        $notStarted = $notStarted->sortBy('bib')->values();

        // Summary stats
        $totalParticipants = $participants->count();
        $finishedCount = $checkpointGroups->has('finish')
            ? $checkpointGroups['finish']['participants']->count()
            : 0;
        $notStartedCount = $notStarted->count();
        $startedCount = $totalParticipants - $notStartedCount;
        $onCourseCount = $startedCount - $finishedCount;

        $summary = [
            'not_started' => $notStartedCount,
            'started' => $startedCount,
            'on_course' => $onCourseCount,
            'finished' => $finishedCount,
        ];

        return view('event.live-preview', compact(
            'event',
            'categories',
            'selectedCategory',
            'checkpointGroups',
            'notStarted',
            'totalParticipants',
            'summary'
        ));
    }
}
